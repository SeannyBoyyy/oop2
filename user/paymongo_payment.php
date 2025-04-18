<?php
require '../foodpartner/vendor/autoload.php'; 
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

include '../config.php';
session_start();

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get secret key from .env
$paymongo_secret_key = $_ENV['PAYMONGO_SECRET_KEY'];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$showtime_id = $_GET['showtime_id'];
$seats = json_decode($_GET['seats'], true);

if (empty($showtime_id) || empty($seats)) {
    die('Error: Invalid showtime or seats.');
}

// Fetch user email
$query = "SELECT user_email FROM tbl_user WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$partner_email = $user['user_email'];

// Calculate total price
$seat_price_query = "SELECT price FROM tbl_showtimes WHERE showtime_id = ?";
$stmt = $con->prepare($seat_price_query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
$price_row = $result->fetch_assoc();
$seat_price = $price_row['price'];

$total_price = $seat_price * count($seats);
$total_price_cents = $total_price * 100;

$client = new Client();

try {
    $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
        'auth' => [$paymongo_secret_key, ''],
        'json' => [
            'data' => [
                'attributes' => [
                    'billing' => [
                        'email' => $partner_email,
                    ],
                    'description' => 'Movie Ticket Payment',
                    'line_items' => [[
                        'amount' => $total_price_cents, 
                        'currency' => 'PHP',
                        'name' => 'Reserved Seats',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => ['gcash', 'grab_pay', 'card'],
                    'success_url' => 'http://localhost/oop2/user/payment_success.php?user_id=' . urlencode($user_id) . '&showtime_id=' . urlencode($showtime_id) . '&seats=' . urlencode(json_encode($seats)),
                    'cancel_url' => 'http://localhost/oop2/user/select_showtime.php',
                ],
            ],
        ]
    ]);

    $body = json_decode($response->getBody(), true);

    if (isset($body['data']['attributes']['checkout_url'])) {
        // Insert transaction into database after successful payment processing
        $seats_string = implode(", ", array_map(function($seat) {
            return $seat['row_label'] . $seat['seat_number'];
        }, $seats)); // Converts to something like "B2, B3, B4"
        
        $insert_transaction = "INSERT INTO tbl_transactions (user_id, showtime_id, seats, total_price, payment_status, transaction_date)
                               VALUES (?, ?, ?, ?, 'paid', NOW())";
        $stmt = $con->prepare($insert_transaction);
        $stmt->bind_param("iiss", $user_id, $showtime_id, $seats_string, $total_price);
        $stmt->execute();        

        header("Location: " . $body['data']['attributes']['checkout_url']);
        exit();
    } else {
        echo "Error: Checkout URL not found in response.";
    }

} catch (RequestException $e) {
    if ($e->hasResponse()) {
        echo "API Error: " . $e->getResponse()->getBody()->getContents();
    } else {
        echo "Request Error: " . $e->getMessage();
    }
}
?>

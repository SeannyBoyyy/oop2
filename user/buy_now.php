<?php
require '../foodpartner/vendor/autoload.php'; 
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

include '../config.php';
session_start();

// PayMongo Secret Key
$paymongo_secret_key = 'sk_test_rQsjmYK8sbTPT6dcWZk3tBxw'; // Replace with your PayMongo secret key

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (!isset($_GET['seats'])) {
    echo "Need Seats Reservations";
    header("Location: user_tickets.php");
    exit();
}

// Ensure product_id and showtime_id are passed
if (!isset($_GET['product_id']) || !isset($_GET['showtime_id'])) {
    echo "Product ID and Showtime ID are required.";
    header("Location: user_tickets.php");
    exit();
}

$seats = $_GET['seats'];
$product_id = $_GET['product_id'];
$showtime_id = $_GET['showtime_id'];
$user_id = $_SESSION['user_id'];

// Fetch product details from tbl_foodproducts
$productQuery = "SELECT * FROM tbl_foodproducts WHERE product_id = ?";
$stmtProduct = $con->prepare($productQuery);
$stmtProduct->bind_param("i", $product_id);
$stmtProduct->execute();
$productResult = $stmtProduct->get_result();
$product = $productResult->fetch_assoc();

if (!$product) {
    echo "Invalid product.";
    exit();
}

// Fetch showtime, cinema, and movie details
$showtimeQuery = "
    SELECT s.screen_number, c.name AS cinema_name, m.title AS movie_name
    FROM tbl_showtimes s
    JOIN tbl_cinema c ON s.cinema_id = c.cinema_id
    JOIN tbl_movies m ON s.movie_id = m.movie_id
    WHERE s.showtime_id = ?
";
$stmtShowtime = $con->prepare($showtimeQuery);
$stmtShowtime->bind_param("i", $showtime_id);
$stmtShowtime->execute();
$showtimeResult = $stmtShowtime->get_result();
$showtime = $showtimeResult->fetch_assoc();

if (!$showtime) {
    echo "Invalid showtime.";
    exit();
}

$screen_number = $showtime['screen_number'];
$cinema_name = $showtime['cinema_name'];
$movie_name = $showtime['movie_name'];

// Check if form is submitted to process the order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure quantity is an integer
    $quantity = intval($_POST['quantity']);  // Cast to integer to avoid the data type error

    // Calculate the total price
    $total_price = $product['price'];
    $total_price_cents = $total_price * 100; // Convert to cents

    // Fetch user email for billing
    $query = "SELECT user_email FROM tbl_user WHERE user_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_email = $user['user_email'];

    // Set up PayMongo client and create checkout session
    $client = new Client();

    try {
        $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
            'auth' => [$paymongo_secret_key, ''],
            'json' => [
                'data' => [
                    'attributes' => [
                        'billing' => [
                            'email' => $user_email,
                        ],
                        'description' => "Food Purchase for $movie_name (Screen $screen_number, Cinema: $cinema_name)",
                        'line_items' => [
                            [
                                'amount' => $total_price_cents, 
                                'currency' => 'PHP',
                                'name' => $product['product_name'],
                                'quantity' => $quantity,  // Make sure this is an integer
                            ]
                        ],
                        'payment_method_types' => ['gcash', 'grab_pay', 'card'],
                        'success_url' => 'http://localhost/oop2/user/foodpayment_success.php?user_id=' . urlencode($user_id) . '&product_id=' . urlencode($product_id) 
                                        . '&quantity=' . urlencode($quantity) . '&showtime_id=' . urlencode($showtime_id) . '&seats=' . urlencode($seats),
                        'cancel_url' => 'http://localhost/oop2/user/order_cancel.php',
                    ],
                ],
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        if (isset($body['data']['attributes']['checkout_url'])) {
            // Redirect user to PayMongo checkout page
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
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <a href="userDashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <h2>Order Food: <?= htmlspecialchars($product['product_name']) ?></h2>
    
    <div class="row">
        <div class="col-md-6">
            <img src="../foodpartner/uploads/<?= $product['image_url'] ?>" class="img-fluid" alt="Food Image">
        </div>
        <div class="col-md-6">
            <h4>Description:</h4>
            <p><?= htmlspecialchars($product['description']) ?></p>
            <p><strong>Price: ₱<?= number_format($product['price'], 2) ?></strong></p>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="100" value="1" required>
                </div>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>

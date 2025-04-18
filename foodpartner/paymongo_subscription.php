<?php
require 'vendor/autoload.php'; 

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get secret key from .env
$paymongo_secret_key = $_ENV['PAYMONGO_SECRET_KEY'];

$client = new Client();

if (!isset($_GET['partner_email']) || empty($_GET['partner_email'])) {
    die('Error: Partner email is not set.');
}

$partner_email = $_GET['partner_email']; 

try {
    $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
        'auth' => [$paymongo_secret_key, ''],
        'json' => [
            'data' => [
                'attributes' => [
                    'billing' => [
                        'email' => $partner_email,
                    ],
                    'description' => 'Subscription Payment (Food Business)',
                    'line_items' => [[
                        'amount' => 500000,  // Change this value (in centavos) e.g., ₱200.00 = 20000
                        'currency' => 'PHP',
                        'name' => '1 Subscription Plan',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => ['gcash', 'grab_pay', 'card'],
                    'success_url' => 'http://localhost/oop2/foodpartner/subscription_success.php?partner_email=' . urlencode($partner_email),
                    'cancel_url' => 'http://localhost/oop2/foodpartner/foodpartnerlogin.php',
                ],
            ],
        ]
    ]);

    $body = json_decode($response->getBody(), true);
    
    // ✅ Extract the checkout  URL
    if (isset($body['data']['attributes']['checkout_url'])) {
        $checkout_url = $body['data']['attributes']['checkout_url'];

        // ✅ Redirect to PayMongo checkout page
        header("Location: $checkout_url");
        exit();
    } else {
        echo "Error: Checkout URL not found in response.";
    }

} catch (RequestException $e) {
    if ($e->hasResponse()) {
        $errorBody = $e->getResponse()->getBody()->getContents();
        echo "API Error: " . $errorBody;
    } else {
        echo "Request Error: " . $e->getMessage();
    }
}
?>

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
    $total_price = $product['price'] * $quantity; 
    
    // Check if the total amount meets PayMongo's minimum requirement
    if ($total_price < 20) {  // Minimum 20 PHP
        // Create a JavaScript that will trigger a modal
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
            var errorModal = new bootstrap.Modal(document.getElementById("minimumOrderModal"));
            errorModal.show();
            });
        </script>';
        
        // Add the modal HTML to the page
        echo '<div class="modal fade" id="minimumOrderModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-body p-0">
                        <div class="mt-3 text-center">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3.5rem;"></i>
                            <h4 class="mt-3 mb-0 fw-bold">Minimum Order Required</h4>
                        </div>
                        
                        <div class="p-4 text-center">
                            <p class="fs-5 mb-4">The minimum order amount is PHP 20.00. Please increase your quantity.</p>
                            <button type="button" class="btn btn-warning px-5 py-2 rounded-pill" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-left-circle-fill me-2"></i>Go Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    } else {
        $total_price_cents = intval($total_price * 100); // Convert to cents

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
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> | Cinema Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        :root {
            --primary: #ffc107;
            --secondary: #ff9800;
            --dark: #212529;
            --light: #f8f9fa;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .page-header {
            background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://i.imgur.com/JnNGAmQ.png');
            background-size: 300px;
            opacity: 0.05;
            z-index: 0;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .order-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 0;
            overflow: hidden;
            margin-bottom: 3rem;
        }
        
        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 450px;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-image:hover {
            transform: scale(1.05);
        }
        
        .product-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--primary);
            color: var(--dark);
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 2;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        
        .product-info {
            padding: 2.5rem;
            position: relative;
        }
        
        .movie-info {
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .movie-info-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .movie-info-item i {
            width: 24px;
            margin-right: 8px;
            color: var(--primary);
        }
        
        .product-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--dark);
        }
        
        .product-description {
            color: #6c757d;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .price-tag {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .order-form {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 180px;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
            border: none;
            color: var(--dark);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quantity-btn:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .quantity-input {
            flex-grow: 1;
            border: none;
            text-align: center;
            font-weight: 600;
            color: var(--dark);
            background: transparent;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        .btn-back {
            background-color: transparent;
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .btn-order {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .btn-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3);
        }
        
        .btn-order::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
        }
        
        .btn-order:hover::after {
            transform: translateX(100%);
            transition: transform 0.6s ease-in-out;
        }
        
        .total-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #dee2e6;
        }
        
        .total-label {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        .secure-payment {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .secure-payment i {
            margin-right: 8px;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <?php include '../include/userNav.php'; ?>
    


    <div class="container" style="margin-top: 120px;">
        <a href="order_food.php?showtime_id=<?= $showtime_id ?>&seats=<?= $seats ?>" class="btn btn-warning text-black mb-3">
            <i class="bi bi-arrow-left me-2"></i>Back to Menu
        </a>
        <div class="order-container">
            
            <div class="row g-0">
                <div class="col-lg-6">
                    
                    <div class="product-image-container">
                        <div class="product-badge">
                            <?= isset($product['category']) ? htmlspecialchars($product['category']) : 'Menu Item' ?>
                        </div>
                        <img src="../foodpartner/uploads/<?= $product['image_url'] ?>" class="product-image" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="product-info">
                        <div class="movie-info">
                            <div class="movie-info-item">
                                <i class="bi bi-film"></i>
                                <span><strong>Movie:</strong> <?= htmlspecialchars($movie_name) ?></span>
                            </div>
                            <div class="movie-info-item">
                                <i class="bi bi-building"></i>
                                <span><strong>Cinema:</strong> <?= htmlspecialchars($cinema_name) ?></span>
                            </div>
                            <div class="movie-info-item">
                                <i class="bi bi-display"></i>
                                <span><strong>Screen:</strong> <?= htmlspecialchars($screen_number) ?></span>
                            </div>
                        </div>
                        
                        <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="price-tag">₱<?= number_format($product['price'], 2) ?></div>
                        
                        <form method="POST" class="order-form">
                            <div class="mb-4">
                                <label for="quantity" class="form-label">Quantity</label>
                                <div class="quantity-control">
                                    <button type="button" class="quantity-btn" id="decrease">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" class="quantity-input" min="1" max="100" value="1" required>
                                    <button type="button" class="quantity-btn" id="increase">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="total-section">
                                <span class="total-label">Total Amount:</span>
                                <span class="total-price" id="totalPrice">₱<?= number_format($product['price'], 2) ?></span>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-order">
                                    <i class="bi bi-credit-card me-2"></i>Proceed to Payment
                                </button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const decreaseBtn = document.getElementById('decrease');
            const increaseBtn = document.getElementById('increase');
            const quantityInput = document.getElementById('quantity');
            const totalPrice = document.getElementById('totalPrice');
            const basePrice = <?= $product['price'] ?>;
            
            // Update total when quantity changes
            function updateTotal() {
                const quantity = parseInt(quantityInput.value);
                const total = basePrice * quantity;
                totalPrice.textContent = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            
            // Decrease quantity
            decreaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                    updateTotal();
                }
            });
            
            // Increase quantity
            increaseBtn.addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue < 100) {
                    quantityInput.value = currentValue + 1;
                    updateTotal();
                }
            });
            
            // Handle manual input
            quantityInput.addEventListener('input', function() {
                updateTotal();
            });
            
            // Prevent form submission when pressing enter in quantity field
            quantityInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>

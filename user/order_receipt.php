<?php
include '../config.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch the order details
    $orderQuery = "SELECT o.*, p.product_name, p.price, s.name, m.title AS movie_name, o.seats
                   FROM tbl_orders o
                   JOIN tbl_foodproducts p ON o.product_id = p.product_id
                   JOIN tbl_showtimes t ON o.showtime_id = t.showtime_id
                   JOIN tbl_cinema s ON t.cinema_id = s.cinema_id
                   JOIN tbl_movies m ON t.movie_id = m.movie_id
                   WHERE o.order_id = ?";
    $stmtOrder = $con->prepare($orderQuery);
    $stmtOrder->bind_param("i", $order_id);
    $stmtOrder->execute();
    $orderResult = $stmtOrder->get_result();

    if ($orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();

        // If seats are stored as a plain string (comma-separated), convert it into an array
        $seats = explode(',', $order['seats']); // Split by commas to create an array

        // Display order details with Bootstrap
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Order Receipt</title>
            <!-- Include Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                .receipt-header {
                    background-color: #f8f9fa;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 30px;
                }
                .receipt-footer {
                    background-color: #f8f9fa;
                    padding: 15px;
                    border-radius: 10px;
                    text-align: center;
                    margin-top: 20px;
                }
                .receipt-details {
                    margin-top: 20px;
                    padding: 20px;
                    background-color: #ffffff;
                    border: 1px solid #ddd;
                    border-radius: 10px;
                }
                .order-title {
                    font-size: 24px;
                    font-weight: bold;
                }
                .order-item {
                    margin-bottom: 15px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Receipt Header -->
                <div class="receipt-header text-center">
                    <h2>Order Receipt</h2>
                    <p class="lead">Thank you for your purchase!</p>
                </div>

                <!-- Order Details -->
                <div class="receipt-details">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
                            <p><strong>Movie:</strong> <?php echo $order['movie_name']; ?></p>
                            <p><strong>Cinema:</strong> <?php echo $order['name']; ?></p>
                            <p><strong>Screen Number:</strong> <?php echo $order['screen_number']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Product:</strong> <?php echo $order['product_name']; ?></p>
                            <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                            <p><strong>Total Price:</strong> PHP <?php echo number_format($order['total_price'], 2); ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                        </div>
                    </div>

                    <!-- Display the selected seats -->
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Selected Seats:</strong></p>
                            <ul>
                                <?php 
                                if (!empty($seats)) {
                                    foreach ($seats as $seat) {
                                        echo htmlspecialchars($seat);
                                    }
                                } else {
                                    echo "No seats selected";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="receipt-footer">
                    <p>&copy; <?php echo date('Y'); ?> Cinema Order System</p>
                    <a href="user_tickets.php" class="btn btn-primary">Back to Home</a>
                </div>
            </div>

            <!-- Include Bootstrap JS -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found.</div></div>";
    }
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Error: Order ID missing.</div></div>";
}
?>

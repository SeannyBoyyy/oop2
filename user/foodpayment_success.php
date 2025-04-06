<?php
include '../config.php';
session_start();

// Assuming you are already inside the payment success handler
if (isset($_GET['product_id']) && isset($_GET['showtime_id']) && isset($_GET['quantity']) && isset($_GET['seats'])) {
    $seats = $_GET['seats']; // Get the selected seats (e.g., an array or JSON)
    $product_id = $_GET['product_id'];
    $showtime_id = $_GET['showtime_id'];
    $quantity = $_GET['quantity'];
    $user_id = $_SESSION['user_id'];  // Assuming user is logged in
    
    // Fetch the product details
    $productQuery = "SELECT * FROM tbl_foodproducts WHERE product_id = ?";
    $stmtProduct = $con->prepare($productQuery);
    $stmtProduct->bind_param("i", $product_id);
    $stmtProduct->execute();
    $productResult = $stmtProduct->get_result();
    $product = $productResult->fetch_assoc();

    // Fetch showtime, cinema, and movie details
    $showtimeQuery = "
    SELECT s.screen_number, c.name AS cinema_name, m.title AS movie_name, s.cinema_id
    FROM tbl_showtimes s
    JOIN tbl_cinema c ON s.cinema_id = c.cinema_id
    JOIN tbl_movies m ON s.movie_id = m.movie_id
    WHERE s.showtime_id = ?
    ";
    $stmtShowtime = $con->prepare($showtimeQuery);
    $stmtShowtime->bind_param("i", $showtime_id);
    $stmtShowtime->execute();
    $showtimeResult = $stmtShowtime->get_result();

    // Check if showtime details are fetched
    if ($showtimeResult->num_rows > 0) {
        $showtime = $showtimeResult->fetch_assoc();
    } else {
        echo "No showtime found for the given showtime_id.";
        exit();
    }

    // Prepare order data
    $screen_number = $showtime['screen_number'];
    $cinema_name = $showtime['cinema_name'];
    $movie_name = $showtime['movie_name'];
    $cinema_id = $showtime['cinema_id'];  // Ensure this value is set
    $total_price = $product['price'] * $quantity;

    // Check if cinema_id is valid
    if (is_null($cinema_id)) {
        echo "Error: Cinema ID is null.";
        exit();
    }

    // Convert selected seats into a JSON format (or you can use serialized data)
    $seats_json = json_encode($seats); // This will store the selected seats as a JSON string

    // Insert order into tbl_orders
    $orderQuery = "
        INSERT INTO tbl_orders (user_id, product_id, quantity, total_price, cinema_id, screen_number, showtime_id, movie_name, seats, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ";
    $stmtOrder = $con->prepare($orderQuery);
    $stmtOrder->bind_param("iiidiiiss", $user_id, $product_id, $quantity, $total_price, $cinema_id, $screen_number, $showtime_id, $movie_name, $seats_json);
    $stmtOrder->execute();

    // Get the last inserted order ID
    $order_id = $stmtOrder->insert_id;

    // Get the partner's details (food partner)
    $partnerQuery = "SELECT * FROM tbl_foodpartner WHERE cinema_id = ?";
    $stmtPartner = $con->prepare($partnerQuery);
    $stmtPartner->bind_param("i", $showtime['cinema_id']);
    $stmtPartner->execute();
    $partnerResult = $stmtPartner->get_result();
    $partner = $partnerResult->fetch_assoc();

    // Send email to the food partner or update their dashboard with the order info
    $to = $partner['partner_email'];
    $subject = "New Food Order for Showtime " . $movie_name;
    $message = "New food order received:\n\n";
    $message .= "Movie: " . $movie_name . "\n";
    $message .= "Showtime: " . $showtime['show_time'] . "\n";
    $message .= "Cinema: " . $cinema_name . " (Screen: " . $screen_number . ")\n";
    $message .= "Ordered by User ID: " . $user_id . "\n";
    $message .= "Quantity: " . $quantity . "\n";
    $message .= "Total Price: PHP " . number_format($total_price, 2) . "\n";
    $message .= "Seats: " . $seats_json . "\n"; // Display selected seats in the email

    // Sending email (Ensure mail configuration is set)
    mail($to, $subject, $message);
    
    // Optionally, redirect user to a confirmation page
    header("Location: order_receipt.php?order_id=" . $order_id);
    exit();
} else {
    echo "Error: Missing required parameters.";
    exit();
}
?>

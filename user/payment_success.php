<?php
session_start();
include '../config.php';

$user_id = $_GET['user_id'];
$showtime_id = $_GET['showtime_id'];
$seats = json_decode($_GET['seats'], true);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (empty($user_id) || empty($showtime_id) || empty($seats)) {
    die('Error: Missing payment details.');
}

// Fetch user details
$query = "SELECT user_firstname, user_lastname, user_email FROM tbl_user WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_firstname = $user['user_firstname'];
$user_lastname = $user['user_lastname'];
$user_email = $user['user_email'];

// Fetch movie & showtime details
$query = "SELECT m.title, s.show_date, s.show_time, s.price 
          FROM tbl_showtimes s
          JOIN tbl_movies m ON s.movie_id = m.movie_id
          WHERE s.showtime_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
$showtime = $result->fetch_assoc();
$movie_title = $showtime['title'];
$show_date = $showtime['show_date'];
$show_time = date("h:i A", strtotime($showtime['show_time']));
$seat_price = $showtime['price'];

// Calculate total price
$total_price = $seat_price * count($seats);

// Reserve seats before confirming the payment
$seat_list = [];
foreach ($seats as $seat) {
    $row_label = $seat['row_label'];
    $seat_number = $seat['seat_number'];
    $seat_list[] = $row_label . $seat_number;

    // Reserve the seat first
    $query = "UPDATE tbl_seats SET status = 'reserved', user_id = ? 
              WHERE showtime_id = ? AND row_label = ? AND seat_number = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("iisi", $user_id, $showtime_id, $row_label, $seat_number);
    $stmt->execute();
}

// Insert transaction into `tbl_transactions`
$seat_numbers = implode(", ", $seat_list);
$insert_transaction = "INSERT INTO tbl_transactions (user_id, showtime_id, seats, total_price, payment_status, transaction_date)
                       VALUES (?, ?, ?, ?, 'paid', NOW())";
$stmt = $con->prepare($insert_transaction);
$stmt->bind_param("iisd", $user_id, $showtime_id, $seat_numbers, $total_price);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .receipt-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
        }
        .print-button {
            display: block;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h3>🎟 Movie Ticket Receipt</h3>
        <p>Thank you for your purchase, <strong><?= htmlspecialchars($user_firstname) ?> <?= htmlspecialchars($user_lastname) ?></strong>!</p>
    </div>

    <table class="table">
        <tr>
            <th>Movie:</th>
            <td><?= htmlspecialchars($movie_title) ?></td>
        </tr>
        <tr>
            <th>Showtime:</th>
            <td><?= htmlspecialchars($show_date) ?> at <?= htmlspecialchars($show_time) ?></td>
        </tr>
        <tr>
            <th>Seats:</th>
            <td><?= htmlspecialchars($seat_numbers) ?></td>
        </tr>
        <tr>
            <th>Price per Seat:</th>
            <td>₱<?= number_format($seat_price, 2) ?></td>
        </tr>
        <tr>
            <th>Total Price:</th>
            <td><strong>₱<?= number_format($total_price, 2) ?></strong></td>
        </tr>
        <tr>
            <th>Email:</th>
            <td><?= htmlspecialchars($user_email) ?></td>
        </tr>
        <tr>
            <th>Transaction Date:</th>
            <td><?= date("Y-m-d H:i:s") ?></td>
        </tr>
    </table>

    <div class="receipt-footer">
        <p><strong>🎬 Enjoy your movie!</strong></p>
        <button class="btn btn-primary print-button" onclick="window.print()">🖨 Print Receipt</button>
    </div>
</div>

</body>
</html>

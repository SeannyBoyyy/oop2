<?php
session_start();
include '../config.php'; // or your correct path to DB config

// Check if partner is logged in
if (!isset($_SESSION['partner_id'])) {
    header("Location: FoodPartnerLogin.php");
    exit();
}

$partner_id = $_SESSION['partner_id'];

// Fetch all food orders
$query = "
    SELECT o.*, f.product_name, c.name AS cinema_name,
           u.user_firstname, u.user_lastname
    FROM tbl_orders o
    JOIN tbl_foodproducts f ON o.product_id = f.product_id
    JOIN tbl_cinema c ON o.cinema_id = c.cinema_id
    JOIN tbl_user u ON o.user_id = u.user_id
    WHERE f.partner_id = ?
";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Food Orders - Partner Side</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f1f1;
        }
        .order-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">Customer Orders</h3>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Movie</th>
                <th>Cinema</th>
                <th>Screen</th>
                <th>Seats</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= htmlspecialchars($row['movie_name']) ?></td>
                <td><?= htmlspecialchars($row['cinema_name']) ?></td>
                <td><?= $row['screen_number'] ?></td>
                <td><?= htmlspecialchars($row['seats']) ?></td>
                <td><?= htmlspecialchars($row['user_firstname'] . ' ' . $row['user_lastname']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>₱<?= number_format($row['total_price'], 2) ?></td>
                <td><span class="badge bg-warning text-dark"><?= $row['status'] ?></span></td>
                <td><?= date("M d, Y h:i A", strtotime($row['order_date'])) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

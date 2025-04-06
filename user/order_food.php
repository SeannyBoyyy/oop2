<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (!isset($_GET['showtime_id'])) {
    echo "Showtime ID is required.";
    header("Location: user_tickets .php");
    exit();
}

if (!isset($_GET['seats'])) {
    echo "Need Seats Reservations";
    header("Location: user_tickets.php");
    exit();
}

$showtime_id = $_GET['showtime_id'];
$seats = $_GET['seats'];

// Get cinema_id from showtime
$cinemaQuery = "SELECT cinema_id FROM tbl_showtimes WHERE showtime_id = ?";
$stmtCinema = $con->prepare($cinemaQuery);
$stmtCinema->bind_param("i", $showtime_id);
$stmtCinema->execute();
$stmtCinema->bind_result($cinema_id);
$stmtCinema->fetch();
$stmtCinema->close();

// Fetch distinct partners offering food in that cinema
$partnerQuery = "
    SELECT DISTINCT p.partner_id, p.partner_firstname, p.partner_lastname, p.business_name, p.image_url
    FROM tbl_foodpartner p
    JOIN tbl_foodproducts f ON p.partner_id = f.partner_id
    WHERE f.cinema_id = ?
";
$stmtPartner = $con->prepare($partnerQuery);
$stmtPartner->bind_param("i", $cinema_id);
$stmtPartner->execute();
$partnersResult = $stmtPartner->get_result();

// Fetch food products available in that cinema
$productQuery = "
    SELECT f.*, p.business_name 
    FROM tbl_foodproducts f
    JOIN tbl_foodpartner p ON f.partner_id = p.partner_id
    WHERE f.cinema_id = ? AND f.status = 'available'
";
$stmtProduct = $con->prepare($productQuery);
$stmtProduct->bind_param("i", $cinema_id);
$stmtProduct->execute();
$productResult = $stmtProduct->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Food</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <a href="userDashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
    <h2>🍔 Food Partners</h2>

    <div class="row">
        <?php while ($partner = $partnersResult->fetch_assoc()) { ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <?php if (!empty($partner['image_url'])) { ?>
                        <img src="../foodpartner/uploads/foodpartner_profiles/<?= $partner['image_url'] ?>" class="card-img-top" alt="Partner Image">
                    <?php } ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= $partner['business_name'] ?></h5>
                        <p class="card-text">By: <?= $partner['partner_firstname'] . ' ' . $partner['partner_lastname'] ?></p>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <hr>
    <h3>🍟 Available Foods</h3>
    <div class="row">
        <?php while ($food = $productResult->fetch_assoc()) { ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="../foodpartner/uploads/<?= $food['image_url'] ?>" class="card-img-top" alt="Food Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= $food['product_name'] ?></h5>
                        <p class="card-text"><?= $food['description'] ?></p>
                        <p><strong>₱<?= number_format($food['price'], 2) ?></strong></p>
                        <p class="text-muted">From: <?= $food['business_name'] ?></p>
                        <!-- Optional: Order button logic -->
                        <a href="buy_now.php?product_id=<?= $food['product_id'] ?>&showtime_id=<?= $showtime_id ?>&seats=<?= $seats ?>" class="btn btn-primary">Buy Now</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>

<?php
session_start();
include '../config.php'; // Database connection

// Check if the food partner is logged in
if (!isset($_SESSION['partner_id'])) {
    header("Location: foodPartnerLogin.php");
    exit();
}

$partner_id = $_SESSION['partner_id'];

// Fetch subscription details
$query = "SELECT business_name, partner_email, subscription_status, subscription_expiry 
          FROM tbl_foodpartner 
          WHERE partner_id = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Subscription details not found.");
}

$partner = $result->fetch_assoc();
$business_name = $partner['business_name'];
$partner_email = $partner['partner_email'];
$subscription_status = $partner['subscription_status'];
$subscription_expiry = $partner['subscription_expiry'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <h2 class="text-primary">📜 My Subscription</h2>
                <p class="mt-3"><strong>Business Name:</strong> <?= htmlspecialchars($business_name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($partner_email) ?></p>

                <hr>

                <p><strong>Subscription Status:</strong> 
                    <?php if ($subscription_status === 'active') : ?>
                        <span class="badge bg-success">Active ✅</span>
                    <?php else : ?>
                        <span class="badge bg-danger">Expired ❌</span>
                    <?php endif; ?>
                </p>

                <p><strong>Expires On:</strong> <?= $subscription_expiry ? htmlspecialchars($subscription_expiry) : 'Not Set' ?></p>

                <hr>

                <?php if ($subscription_status === 'expired') : ?>
                    <a href="renewSubscription.php" class="btn btn-warning">Renew Subscription</a>
                <?php else : ?>
                    <p class="text-success">Your subscription is active. Enjoy our services!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

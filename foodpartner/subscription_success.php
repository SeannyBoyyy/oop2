<?php
session_start();
include '../config.php'; // Database connection

if (!isset($_GET['partner_email'])) {
    die("Invalid request.");
}

$partner_email = $_GET['partner_email'];
$subscription_status = "active";
$subscription_expiry = date('Y-m-d H:i:s', strtotime('+1 year')); // Set expiry to 1 year from now

// ✅ Update the subscription status and expiry date in the database
$sql = "UPDATE tbl_foodpartner 
        SET status = ?, verification_status = 'verified', subscription_status = ?, subscription_expiry = ?, created_at = NOW() 
        WHERE partner_email = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("ssss", $subscription_status, $subscription_status, $subscription_expiry, $partner_email);
$stmt->execute();
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <h2 class="text-success">🎉 Subscription Successful!</h2>
                <p class="mt-3">Thank you for subscribing. Your food business account is now **activated**.</p>
                
                <hr>

                <h4>📜 Subscription Receipt</h4>
                <p><strong>Business Email:</strong> <?php echo htmlspecialchars($partner_email); ?></p>
                <p><strong>Subscription Status:</strong> Active ✅</p>
                <p><strong>Expires On:</strong> <?php echo $subscription_expiry; ?></p>
                
                <hr>

                <a href="foodPartnerDashboard.php" class="btn btn-primary mt-3">Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>

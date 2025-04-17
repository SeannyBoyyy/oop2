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
    <title>Subscription Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
</head>
<body>
<div class="wrapper">
        <nav id="sidebar" class="cinema-sidebar">
            <div class="position-sticky">
                <div class="sidebar-header text-center">
                    <i class="bi bi-shop display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong><?php echo htmlspecialchars($business_name); ?></strong></h3>
                </div>
                <ul class="list-unstyled components">
                    <li style="font-size: 1.1rem;">
                        <a href="foodPartnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_food.php"><i class="bi bi-bag"></i> Manage Products</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="view_orders.php"><i class="bi bi-cart-check"></i> Manage Orders</a>
                    </li>
                    <li class="active" style="font-size: 1.1rem;">
                        <a href="subscription_status.php"><i class="bi bi-star"></i> Subscription Status</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_foodpartner_profile.php"><i class="bi bi-person"></i> Profile</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="FoodPartnerlogout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn">
                        <i class="bi bi-list text-dark"></i>
                    </button>
                    <div class="ms-auto">
                        <div class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-dark" href="#" id="partnerDropdown" role="button" data-bs-toggle="dropdown">
                                       Welcome, <?php echo htmlspecialchars($business_name); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="FoodPartnerlogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Subscription Status</h2>

                <div class="card shadow" style="transition: none; transform: none;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="mb-4"><i class="bi bi-info-circle me-2"></i>Subscription Information</h4>
                                <p><strong>Business Name:</strong> <?= htmlspecialchars($business_name) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($partner_email) ?></p>
                                <p><strong>Subscription Status:</strong> 
                                    <?php if ($subscription_status === 'active') : ?>
                                        <span class="badge bg-success">Active <i class="bi bi-check-circle"></i></span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Expired <i class="bi bi-x-circle"></i></span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Expires On:</strong> <?= $subscription_expiry ? date('F j, Y', strtotime($subscription_expiry)) : 'Not Set' ?></p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <?php if ($subscription_status === 'expired' || $subscription_status === 'inactive') : ?>
                                <a href="renewSubscription.php" class="btn btn-warning btn-lg">
                                    <i class="bi bi-arrow-repeat me-2"></i>Renew Subscription
                                </a>
                            <?php else : ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    Your subscription is active. Enjoy our services!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include '../config.php';

// Check if food partner is logged in
if (!isset($_SESSION['partner_id']) || $_SESSION['user_type'] !== 'partner') {
    header("Location: foodPartnerLogin.php");
    exit();
}

// Get partner information
$partner_id = $_SESSION['partner_id'];
$sql = "SELECT partner_firstname, partner_lastname, business_name FROM tbl_foodpartner WHERE partner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $partner_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname, $business_name);
mysqli_stmt_fetch($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Partner Dashboard</title>
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
                    <li class="active" style="font-size: 1.1rem;">
                        <a href="foodPartnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_food.php"><i class="bi bi-bag"></i> Manage Products</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="view_orders.php"><i class="bi bi-cart-check"></i> Manage Orders</a>
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
                <h2 class="text-start mb-5 fw-bold fs-1">Food Partner Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <a href="manage_food.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-bag display-1 mb-3"></i>
                                <h5 class="card-title">Manage Products</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="view_orders.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-cart-check display-1 mb-3"></i>
                                <h5 class="card-title">Manage Orders</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="manage_foodpartner_profile.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-person display-1 mb-3"></i>
                                <h5 class="card-title">Profile</h5>
                            </div>
                        </a>
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
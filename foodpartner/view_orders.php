<?php
session_start();
include '../config.php'; // or your correct path to DB config

// Check if partner is logged in
if (!isset($_SESSION['partner_id'])) {
    header("Location: FoodPartnerLogin.php");
    exit();
}

$partner_id = $_SESSION['partner_id'];

// Get business name for header
$query_business = "SELECT business_name FROM tbl_foodpartner WHERE partner_id = ?";
$stmt_business = $con->prepare($query_business);
$stmt_business->bind_param("i", $partner_id);
$stmt_business->execute();
$result_business = $stmt_business->get_result();
$row_business = $result_business->fetch_assoc();
$business_name = $row_business['business_name'];
$stmt_business->close();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders | Food Partner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                    <li style="font-size: 1.2em;">
                        <a href="foodPartnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="manage_food.php"><i class="bi bi-bag"></i> Manage Products</a>
                    </li>
                    <li class="active" style="font-size: 1.2em;">
                        <a href="view_orders.php"><i class="bi bi-cart-check"></i> Manage Orders</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="subscription_status.php"><i class="bi bi-star"></i> Subscription Status</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="manage_foodpartner_profile.php"><i class="bi bi-person"></i> Profile</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a class="text-danger" href="FoodPartnerlogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        
        <div id="content">
            <!-- Top Navbar -->
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
                                    <li>
                                        <a class="dropdown-item text-danger" href="FoodPartnerlogout.php">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>

           
            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Manage Orders</h2>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Cinema</th>
                                <th>Customer Name</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['cinema_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_firstname'] . ' ' . $row['user_lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>₱<?php echo number_format($row['total_price'], 2); ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'bg-warning text-dark';
                                            if (strtolower($row['status']) == 'completed') {
                                                $status_class = 'bg-success';
                                            } elseif (strtolower($row['status']) == 'canceled') {
                                                $status_class = 'bg-danger';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                        <td><?php echo date("M d, Y h:i A", strtotime($row['order_date'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
</body>
</html>
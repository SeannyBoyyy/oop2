<?php
session_start();
include '../config.php'; // adjust the path as needed

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: UserLogin.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); // ✅ Close the statement here

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$query = "
    SELECT o.*, f.product_name, c.name AS cinema_name
    FROM tbl_orders o
    JOIN tbl_foodproducts f ON o.product_id = f.product_id
    JOIN tbl_cinema c ON o.cinema_id = c.cinema_id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Food Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
        }
        .order-container {
            margin-top: 50px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <i class="bi bi-person-circle display-1 mb-2"></i>
            <h3 class="fw-bold"><strong>User Dashboard</strong></h3>
        </div>
        <ul class="list-unstyled components">
            <li style="font-size: 1.2em;">
                <a href="userDashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            </li>
            <li class="active" style="font-size: 1.2em;">
                <a href="user_tickets.php"><i class="bi bi-ticket-perforated"></i> My Tickets</a>
            </li>
            <li style="font-size: 1.2em;">
                <a href="user_orders.php"><i class="bi bi-cart"></i> My Orders</a>
            </li>
            <li style="font-size: 1.2em;">
                <a href="userLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn">
                    <i class="bi bi-list text-dark"></i>
                </button>
                <div class="ms-auto">
                    <div class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                Welcome, <?php echo htmlspecialchars($firstname); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="userLogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Orders Table -->
        <div class="container order-container">
            <h3 class="mb-4">My Food Orders</h3>

            <?php if ($result->num_rows > 0): ?>
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Movie</th>
                            <th>Cinema</th>
                            <th>Screen</th>
                            <th>Seats</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Date Ordered</th>
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
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>₱<?= number_format($row['total_price'], 2) ?></td>
                            <td>
                                <span class="badge <?= $row['status'] == 'Pending' ? 'bg-warning' : 'bg-success' ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date("M d, Y h:i A", strtotime($row['order_date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">You haven't placed any food orders yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

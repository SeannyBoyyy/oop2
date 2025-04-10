<?php 
session_start();
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); // 

if (!isset($_GET['partner_id']) || !isset($_GET['cinema_id'])) {
    die("Invalid request. Partner or Cinema not found.");
}

$partner_id = intval($_GET['partner_id']);
$cinema_id = intval($_GET['cinema_id']);

// Fetch Food Partner Profile
$sqlPartner = "SELECT business_name, partner_firstname, partner_lastname, partner_email, partner_address, image_url 
               FROM tbl_foodpartner WHERE partner_id = ? AND cinema_id = ?";
$stmtPartner = $con->prepare($sqlPartner);
$stmtPartner->bind_param("ii", $partner_id, $cinema_id);
$stmtPartner->execute();
$resultPartner = $stmtPartner->get_result();
$partner = $resultPartner->fetch_assoc();

if (!$partner) {
    die("Food partner not found.");
}

// Fetch food products
$sqlProducts = "SELECT * FROM tbl_foodproducts WHERE partner_id = ? AND cinema_id = ? AND status = 'available'";
$stmtProducts = $con->prepare($sqlProducts);
$stmtProducts->bind_param("ii", $partner_id, $cinema_id);
$stmtProducts->execute();
$resultProducts = $stmtProducts->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($partner['business_name']); ?> - Food Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
    <style>
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-warning" href="user_homepage.php">
                Cinema App
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'userDashboard.php' ? 'active text-warning' : '' ?>" href="userDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'user_tickets.php' ? 'active text-warning' : '' ?>" href="user_tickets.php">My Tickets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'user_orders.php' ? 'active text-warning' : '' ?>" href="user_orders.php">My Orders</a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                             Welcome, <strong><?php echo htmlspecialchars($firstname); ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item text-danger" href="userLogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <a href="cinema_schedule.php?cinema_id=<?= $cinema_id; ?>" class="btn btn-secondary mb-3">Back to Cinema</a>
        
        <!-- Food Partner Profile -->
        <div class="card p-4 mb-4">
            <div class="row">
                <div class="col-md-2 text-center">
                    <img src="../foodpartner/uploads/foodpartner_profiles/<?= $partner['image_url'] ? htmlspecialchars($partner['image_url']) : 'default.png'; ?>" class="profile-img">
                </div>
                <div class="col-md-10">
                    <h2><?= htmlspecialchars($partner['business_name']); ?></h2>
                    <p><strong>Owner:</strong> <?= htmlspecialchars($partner['partner_firstname'] . " " . $partner['partner_lastname']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($partner['partner_email']); ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($partner['partner_address']); ?></p>
                </div>
            </div>
        </div>

        <h3 class="mb-4">Food Products</h3>

        <div class="row">
            <?php if ($resultProducts->num_rows > 0) { ?>
                <?php while ($product = $resultProducts->fetch_assoc()) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="../foodpartner/uploads/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top product-img">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['product_name']); ?></h5>
                                <p><?= htmlspecialchars($product['description']); ?></p>
                                <p><strong>Category:</strong> <?= htmlspecialchars($product['category']); ?></p>
                                <p><strong>Price:</strong> ₱<?= number_format($product['price'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="text-center text-muted">No available products.</p>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

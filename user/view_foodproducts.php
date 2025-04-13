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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
    <?php include '../include/userNav.php'; ?> 

    <div class=" my-4 py-5" style="margin-top: -50px;">

        
        <!-- Food Partner Profile -->
        <div class="hero-section text-light" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('../foodpartner/uploads/foodpartner_profiles/<?= $partner['image_url'] ? htmlspecialchars($partner['image_url']) : 'default.png'; ?>'); background-size: cover; background-position: center; padding: 60px 0 40px; margin-bottom: 30px;">
            <div class="container">
            <div class="mb-4 text-start">
                <button onclick="history.back()" class="btn btn-warning px-4 py-2 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back to Cinema
                </button>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 text-center mb-4 py-5">
                <img src="../foodpartner/uploads/foodpartner_profiles/<?= $partner['image_url'] ? htmlspecialchars($partner['image_url']) : 'default.png'; ?>" 
                     alt="<?= htmlspecialchars($partner['business_name']); ?>" 
                     class="img-fluid rounded shadow-lg" style="max-height: 300px; object-fit: cover; width: auto;">
                </div>
                <div class="col-lg-6 col-md-12 mb-4 mb-lg-0 text-center text-lg-start">
                <span class="badge bg-danger px-3 py-2 mb-2">FOOD PARTNER</span>
                <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($partner['business_name']); ?></h1>
                <p class="lead mb-4 opacity-75">Delicious food options for your movie experience</p>
                
                <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center justify-content-lg-start">
                    <div class="badge bg-light text-dark p-2 px-3 fs-6 mb-2 text-wrap" style="max-width: 100%;">
                        <i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($partner['partner_firstname'] . " " . $partner['partner_lastname']); ?>
                    </div>
                    <div class="badge bg-light text-dark p-2 px-3 fs-6 mb-2 text-wrap" style="max-width: 100%;">
                        <i class="bi bi-envelope-fill me-1"></i> <?= htmlspecialchars($partner['partner_email']); ?>
                    </div>
                    <div class="badge bg-light text-dark p-2 px-3 fs-6 mb-2 text-wrap" style="max-width: 100%;">
                        <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($partner['partner_address']); ?>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>

        <!-- Food Products Section -->
        <div class="container mt-5 mb-5">
            <div class="row mb-4">
            <div class="col">
                <div class="text-center mb-5">
                    <h2 class="fw-bold display-5 mb-3">Our Menu Selection</h2>
                    <div class="position-relative d-inline-block">
                        <span class="fs-5 text-muted">Elevate Your Movie Experience with Our Delicious Options</span>
                        <div class="position-absolute w-50 start-50 translate-middle-x" style="bottom: -10px;"></div>
                    </div>
                </div>

            </div>
            </div>

            <?php if ($resultProducts->num_rows > 0) { ?>
            <div class="row g-4">
                <?php while ($product = $resultProducts->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden transition-hover">
                    <div class="position-relative">
                        <img src="../foodpartner/uploads/<?= htmlspecialchars($product['image_url']); ?>" 
                         class="card-img-top product-img" 
                         alt="<?= htmlspecialchars($product['product_name']); ?>">
                        <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-warning text-black"><?= htmlspecialchars($product['category']); ?></span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold mb-3"><?= htmlspecialchars($product['product_name']); ?></h5>
                        <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($product['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="fs-5 fw-bold ">₱<?= number_format($product['price'], 2); ?></span>
                        </div>
                    </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="row mt-5">
                <div class="col text-center py-5">
                <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                <p class="fs-4 text-muted mt-3">No available products at the moment</p>
                <p class="text-muted">Please check back later for updates to our menu</p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <style>
    .navbar-nav .nav-link {
        color: white !important;
    }
    
    .navbar-nav .nav-link.active {
        color: #ffd700 !important;
    }
    
    .navbar-nav .nav-link:hover {
        color: #ffd700 !important;
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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

$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9));
            padding: 60px 0;
            color: white;
            margin-bottom: 30px;
        }
        
        .partner-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .partner-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .partner-img {
            height: 180px;
            object-fit: cover;
        }
        
        .food-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        
        .food-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .food-img {
            height: 200px;
            object-fit: cover;
        }
        
        .badge-corner {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .btn-custom {
            background-color: #ffc107;
            color: #000;
            border: none;
            border-radius: 50px;
            padding: 8px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            background-color: #e0a800;
            transform: scale(1.05);
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            margin-bottom: 30px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include '../include/userNav.php'; ?>
    
    <div class="hero-section" style="margin-top: 70px;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">

                    <h1 class="display-4 fw-bold mb-3">Enhance Your Movie Experience</h1>
                    <p class="lead mb-4">Choose from a variety of delicious food options to enjoy with your movie</p>

                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Food Partners Section -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="section-title fw-bold">
                    <i class="bi bi-shop me-2 text-warning"></i>Our Food Partners
                </h2>
                <p class="text-muted">Exclusive dining options for your movie experience</p>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <?php 
            if ($partnersResult->num_rows > 0) {
                while ($partner = $partnersResult->fetch_assoc()) { 
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card partner-card h-100">
                        <?php if (!empty($partner['image_url'])) { ?>
                            <div class="position-relative">
                                <img src="../foodpartner/uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']) ?>" 
                                     class="card-img-top partner-img" 
                                     alt="<?= htmlspecialchars($partner['business_name']) ?>">
                                <div class="badge-corner">
                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Partner</span>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="card-img-top partner-img d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-shop display-1 text-muted"></i>
                            </div>
                        <?php } ?>
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-cup-hot-fill display-6 text-warning"></i>
                            </div>
                            <h4 class="card-title fw-bold"><?= htmlspecialchars($partner['business_name']) ?></h4>
                            <p class="card-text text-muted">
                                <i class="bi bi-person-circle me-2"></i>
                                <?= htmlspecialchars($partner['partner_firstname'] . ' ' . $partner['partner_lastname']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
            ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-shop text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0 lead">No food partners available for this cinema</p>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <!-- Available Foods Section -->
        <div class="row mb-4 mt-5">
            <div class="col-12">
                <h2 class="section-title fw-bold">
                    <i class="bi bi-basket2-fill me-2 text-warning"></i>Available Menu Items
                </h2>
                <p class="text-muted">Select delicious treats to enjoy with your movie</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php 
            if ($productResult->num_rows > 0) {
                while ($food = $productResult->fetch_assoc()) { 
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card food-card h-100">
                        <div class="position-relative">
                            <img src="../foodpartner/uploads/<?= htmlspecialchars($food['image_url']) ?>" 
                                 class="card-img-top food-img" 
                                 alt="<?= htmlspecialchars($food['product_name']) ?>">
                            <div class="badge-corner">
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    <?= isset($food['category']) ? htmlspecialchars($food['category']) : 'Food' ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <h5 class="card-title fw-bold mb-3"><?= htmlspecialchars($food['product_name']) ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($food['description']) ?></p>
                            <div class="mt-3">
                                <p class="fs-5 fw-bold mb-2">₱<?= number_format($food['price'], 2) ?></p>
                                <p class="mb-3 small text-muted">
                                    <i class="bi bi-shop me-1"></i>From: <?= htmlspecialchars($food['business_name']) ?>
                                </p>
                                <a href="buy_now.php?product_id=<?= $food['product_id'] ?>&showtime_id=<?= $showtime_id ?>&seats=<?= $seats ?>" 
                                   class="btn btn-custom w-100">
                                    <i class="bi bi-bag-check-fill me-2"></i>Buy Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
            ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-basket text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0 lead">No food items available at this time</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
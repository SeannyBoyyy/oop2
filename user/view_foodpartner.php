<?php
session_start();
require '../config.php'; // Include database connection

// Fetch all active food partners
$sqlPartners = "SELECT * FROM tbl_foodpartner WHERE status = 'active' AND subscription_status = 'active'";
$resultPartners = $con->query($sqlPartners);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Partners</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .partner-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Cinema App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="user_homepage.php">Home</a>
                        <a class="nav-link active" href="userDashboard.php">Dashboard</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="userLogout.php">Logout</a>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h2 class="text-center mb-4">Food Partners</h2>

        <?php while ($partner = $resultPartners->fetch_assoc()) { ?>
            <div class="partner-card">
                <h4><?= htmlspecialchars($partner['business_name']); ?></h4>
                <p><strong>Address:</strong> <?= htmlspecialchars($partner['partner_address']); ?></p>

                <div class="row">
                    <?php
                    $partner_id = $partner['partner_id'];
                    $sqlProducts = "SELECT * FROM tbl_foodproducts WHERE partner_id = $partner_id AND status = 'available'";
                    $resultProducts = $con->query($sqlProducts);

                    if ($resultProducts->num_rows > 0) {
                        while ($product = $resultProducts->fetch_assoc()) { ?>
                            <div class="col-md-4 mb-3">
                                <div class="card product-card">
                                    <img src="../foodpartner/uploads/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($product['product_name']); ?></h6>
                                        <p class="text-muted"><?= htmlspecialchars($product['category']); ?></p>
                                        <p><?= htmlspecialchars($product['description']); ?></p>
                                        <p class="fw-bold">₱<?= number_format($product['price'], 2); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    } else {
                        echo "<p class='text-muted'>No available products.</p>";
                    }
                    ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

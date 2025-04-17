<?php
session_start();
require '../config.php';

if (!isset($_SESSION['partner_id'])) {
    header("Location: FoodPartnerLogin.php");
    exit();
}

$partner_id = $_SESSION['partner_id']; 

// Fetch current profile data
$sql = "SELECT * FROM tbl_foodpartner WHERE partner_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['partner_firstname']);
    $lastname = trim($_POST['partner_lastname']);
    $email = trim($_POST['partner_email']);
    $address = trim($_POST['partner_address']);
    $business_name = trim($_POST['business_name']);
    $image_url = $partner['image_url']; // Keep the existing image if not changed

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/foodpartner_profiles/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error_message = "Only JPG, JPEG, and PNG files are allowed.";
        } else if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        } else {
            $error_message = "Error uploading image.";
        }
    }

    if (empty($error_message)) {
        // Update food partner profile
        $sqlUpdate = "UPDATE tbl_foodpartner SET 
            partner_firstname = ?, 
            partner_lastname = ?, 
            partner_email = ?, 
            partner_address = ?, 
            business_name = ?, 
            image_url = ? 
            WHERE partner_id = ?";
        
        $stmtUpdate = $con->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssssssi", $firstname, $lastname, $email, $address, $business_name, $image_url, $partner_id);
        
        if ($stmtUpdate->execute()) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Profile update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Partner Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <style>
        .profile-img {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            display: block;
        }
    </style>
</head>
<body>
<div class="wrapper">
        <nav id="sidebar" class="cinema-sidebar">
            <div class="position-sticky">
                <div class="sidebar-header text-center">
                    <i class="bi bi-shop display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong><?php echo htmlspecialchars($partner['business_name']); ?></strong></h3>
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
                                       Welcome, <?php echo htmlspecialchars($partner['business_name']); ?>
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
                <h2 class="text-start mb-5 fw-bold fs-1">Manage Food Partner Profile</h2>

                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" class="form-control" name="business_name" value="<?php echo htmlspecialchars($partner['business_name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="partner_firstname" value="<?php echo htmlspecialchars($partner['partner_firstname']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="partner_lastname" value="<?php echo htmlspecialchars($partner['partner_lastname']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="partner_email" value="<?php echo htmlspecialchars($partner['partner_email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="partner_address" required><?php echo htmlspecialchars($partner['partner_address']); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Profile Image</label>
                        <?php if ($partner['image_url']) { ?>
                            <img src="uploads/foodpartner_profiles/<?php echo htmlspecialchars($partner['image_url']); ?>" class="profile-img mb-2">
                        <?php } ?>
                        <input type="file" class="form-control" name="profile_image" accept="image/*">
                    </div>

                    <button type="submit" class="btn" style="background-color: #ffd700">Update Profile</button>
                </form>
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
<?php
session_start();
require '../config.php';

$partner_id = $_SESSION['partner_id']; // Ensure session stores the logged-in partner ID

if (!$partner_id) {
    die("Unauthorized access.");
}

// Fetch current profile data
$sql = "SELECT * FROM tbl_foodpartner WHERE partner_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$result = $stmt->get_result();
$partner = $result->fetch_assoc();

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
        $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Only JPG, JPEG, and PNG files are allowed.");
        }
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        } else {
            die("Error uploading image.");
        }
    }

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
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: manage_foodpartner_profile.php");
        exit();
    } else {
        die("Profile update failed.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2>Manage Food Partner Profile</h2>
        <?php if (isset($_SESSION['message'])) { ?>
            <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php } ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Business Name</label>
                <input type="text" class="form-control" name="business_name" value="<?= htmlspecialchars($partner['business_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" name="partner_firstname" value="<?= htmlspecialchars($partner['partner_firstname']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" name="partner_lastname" value="<?= htmlspecialchars($partner['partner_lastname']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="partner_email" value="<?= htmlspecialchars($partner['partner_email']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="partner_address" required><?= htmlspecialchars($partner['partner_address']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Profile Image</label>
                <input type="file" class="form-control" name="profile_image">
                <?php if ($partner['image_url']) { ?>
                    <img src="uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']); ?>" class="mt-2" width="150">
                <?php } ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

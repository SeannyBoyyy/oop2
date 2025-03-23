<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
$success_message = "";
$error_message = "";

// Fetch cinema details
$sql = "SELECT cinema_id, name, location, total_screens, status, cinema_image FROM tbl_cinema WHERE owner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cinema_id, $name, $location, $total_screens, $status, $cinema_image);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Handle form submission for updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_location = trim($_POST['location']);
    $new_total_screens = intval($_POST['total_screens']);
    $new_status = $_POST['status'];

    // File upload handling
    if (!empty($_FILES["cinema_image"]["name"])) {
        $target_dir = "../cinema/uploads/profile/";
        $file_name = basename($_FILES["cinema_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; 
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed_types) && $_FILES["cinema_image"]["size"] <= 5000000) { // 5MB limit
            if (move_uploaded_file($_FILES["cinema_image"]["tmp_name"], $target_file)) {
                $cinema_image = $target_file; // Update image path
            } else {
                $error_message = "Error uploading image.";
            }
        } else {
            $error_message = "Invalid file format or file too large.";
        }
    }

    if (!empty($new_name) && !empty($new_location) && $new_total_screens > 0) {
        $update_sql = "UPDATE tbl_cinema SET name = ?, location = ?, total_screens = ?, status = ?, cinema_image = ? WHERE cinema_id = ? AND owner_id = ?";
        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssisssi", $new_name, $new_location, $new_total_screens, $new_status, $cinema_image, $cinema_id, $owner_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "Profile updated successfully!";
            $name = $new_name;
            $location = $new_location;
            $total_screens = $new_total_screens;
            $status = $new_status;
        } else {
            $error_message = "Error updating profile.";
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $error_message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cinema Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="cinemaOwnerDashboard.php">Cinema Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="manageCinemaProfile.php">Manage Cinema</a>
                    </li>
                </ul>
                <a href="cinemaOwnerLogout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Manage Cinema Profile</h3>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Cinema Name</label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <textarea class="form-control" name="location" required><?php echo htmlspecialchars($location); ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Total Screens</label>
                <input type="number" class="form-control" name="total_screens" value="<?php echo $total_screens; ?>" required min="1">
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">
                    <option value="open" <?php if ($status == 'open') echo "selected"; ?>>Open</option>
                    <option value="closed" <?php if ($status == 'closed') echo "selected"; ?>>Closed</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cinema Profile Image</label>
                <?php if (!empty($cinema_image)): ?>
                    <img src="<?php echo htmlspecialchars($cinema_image); ?>" class="profile-img mb-2">
                <?php endif; ?>
                <input type="file" class="form-control" name="cinema_image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

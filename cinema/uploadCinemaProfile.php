<?php
session_start();
include '../config.php';

// Check if owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$owner_id = $_SESSION['owner_id'];
$success_message = "";
$error_message = "";

// Check if the owner has already uploaded a cinema profile
$check_sql = "SELECT cinema_id FROM tbl_cinema WHERE owner_id = ?";
$check_stmt = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $owner_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    header("Location: manageCinemaProfile.php"); // Redirect to edit page if profile exists
    exit();
}
mysqli_stmt_close($check_stmt);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $total_screens = intval($_POST['total_screens']);
    $status = $_POST['status'];
    $cinema_image = "";

    // File upload handling
    if (!empty($_FILES["cinema_image"]["name"])) {
        $target_dir = "../cinema/uploads/profile/";
        $file_name = basename($_FILES["cinema_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $file_name; 
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (in_array($imageFileType, $allowed_types) && $_FILES["cinema_image"]["size"] <= 5000000) { // 5MB limit
            if (move_uploaded_file($_FILES["cinema_image"]["tmp_name"], $target_file)) {
                $cinema_image = $target_file;
            } else {
                $error_message = "Error uploading image.";
            }
        } else {
            $error_message = "Invalid file format or file too large.";
        }
    }

    if (!empty($name) && !empty($location) && $total_screens > 0 && !empty($cinema_image)) {
        $insert_sql = "INSERT INTO tbl_cinema (owner_id, name, location, total_screens, status, cinema_image, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "ississ", $owner_id, $name, $location, $total_screens, $status, $cinema_image);

        if (mysqli_stmt_execute($insert_stmt)) {
            // ✅ Fetch the newly inserted cinema_id
            $cinema_id = mysqli_insert_id($con);
            
            // ✅ Store cinema_id in session
            $_SESSION['cinema_id'] = $cinema_id;

            header("Location: manageCinemaProfile.php"); // Redirect after successful upload
            exit();
        } else {
            $error_message = "Error saving profile.";
        }
        mysqli_stmt_close($insert_stmt);
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
    <title>Upload Cinema Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Cinema Dashboard</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Upload Your Cinema Profile</h3>
        <p class="text-muted">This is a one-time setup. You can edit later.</p>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Cinema Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <textarea class="form-control" name="location" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Total Screens</label>
                <input type="number" class="form-control" name="total_screens" required min="1">
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cinema Profile Image</label>
                <input type="file" class="form-control" name="cinema_image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload Profile</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

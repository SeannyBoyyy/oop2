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

$cinema_id = $_SESSION['cinema_id'];
$cinema_name = '';
$query = "SELECT name FROM tbl_cinema WHERE cinema_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $cinema_id);
mysqli_stmt_execute($stmt);
$result_cinema = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result_cinema)) {
    $cinema_name = $row['name'];
}

// Fetch cinema details
$sql = "SELECT c.cinema_id, c.name, c.location, c.total_screens, c.status, c.cinema_image, o.owner_firstname, o.owner_lastname 
        FROM tbl_cinema c
        JOIN tbl_cinema_owner o ON c.owner_id = o.owner_id
        WHERE c.owner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cinema_id, $name, $location, $total_screens, $status, $cinema_image, $owner_firstname, $owner_lastname);
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
                    <i class="bi bi-person-circle display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong><?php echo htmlspecialchars($name); ?></strong></h3>
                </div>
                <ul class="list-unstyled components">
                    <li style="font-size: 1.1rem;">
                        <a href="cinemaOwnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_movies.php"><i class="bi bi-film"></i> Manage Movies</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_showtimes.php"><i class="bi bi-ticket"></i> Manage Showtimes</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="select_showtime.php"><i class="bi bi-clock"></i> Showtimes</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_cinema.php"><i class="bi bi-building"></i> Manage Cinema</a>
                    </li>
                    <li class="active" style="font-size: 1.1rem;">
                        <a href="manageCinemaProfile.php"><i class="bi bi-gear"></i> Settings</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="cinemaOwnerLogout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
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
                                <a class="nav-link dropdown-toggle text-dark" href="#" id="ownerDropdown" role="button" data-bs-toggle="dropdown">
                                       Welcome, <?php echo htmlspecialchars($cinema_name); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="cinemaOwnerLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Manage Cinema Profile</h2>

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
                    <button type="submit" class="btn " style="background-color: #ffd700">Update Profile</button>
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

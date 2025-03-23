<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

// Get cinema owner information
$owner_id = $_SESSION['owner_id'];
$sql = "SELECT owner_firstname, owner_lastname, cinema_name FROM tbl_cinema_owner WHERE owner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname, $cinema_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Owner Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><?php echo htmlspecialchars($cinema_name); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="cinemaOwnerDashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_movies.php">Manage Movies</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_showtimes.php">Manage Showtime</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="ownerDropdown" role="button" data-bs-toggle="dropdown">
                            Welcome, <?php echo htmlspecialchars($firstname); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger" href="cinemaOwnerLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>Welcome, <?php echo htmlspecialchars($firstname) . " " . htmlspecialchars($lastname); ?>!</h3>
        <p>This is your dashboard where you can manage movies, bookings, and cinema settings.</p>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-film fs-1 text-primary"></i>
                        <h5 class="mt-2">Manage Movies</h5>
                        <p>View and update your cinema's movie listings.</p>
                        <a href="manage_movies.php" class="btn btn-primary btn-sm">Go to Movies</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-ticket fs-1 text-success"></i>
                        <h5 class="mt-2">Manage Showtime</h5>
                        <p>View and manage Movies Showtime.</p>
                        <a href="manage_showtimes.php" class="btn btn-success btn-sm">Go to Showtime</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <i class="bi bi-gear fs-1 text-warning"></i>
                        <h5 class="mt-2">Settings</h5>
                        <p>Update your cinema details and preferences.</p>
                        <a href="manageCinemaProfile.php" class="btn btn-warning btn-sm">Go to Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

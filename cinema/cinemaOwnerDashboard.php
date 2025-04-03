<?php
session_start();
include '../config.php';

if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

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
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar" class="cinema-sidebar">
            <div class="position-sticky">
                <div class="sidebar-header text-center">
                    <i class="bi bi-person-circle display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong><?php echo htmlspecialchars($cinema_name); ?></strong></h3>
                </div>
                <ul class="list-unstyled components">
                    <li class="active" style="font-size: 1.1rem;">
                        <a href="cinemaOwnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li  style="font-size: 1.1rem;">
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
                    <li style="font-size: 1.1rem;">
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
            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Cinema Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <a href="manage_movies.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-film display-1 mb-3"></i>
                                <h5 class="card-title">Manage Movies</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="manage_showtimes.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-ticket display-1 mb-3"></i>
                                <h5 class="card-title">Manage Showtimes</h5>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="manageCinemaProfile.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-gear display-1 mb-3"></i>
                                <h5 class="card-title">Settings</h5>
                            </div>
                        </a>
                    </div>
                </div>
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

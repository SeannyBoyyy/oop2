<?php
session_start();
include '../config.php';


if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: adminLogin.php");
    exit();
}

$result_cinemas = mysqli_query($con, "SELECT * FROM tbl_cinema");
$total_cinemas = mysqli_num_rows($result_cinemas);


$result_food = mysqli_query($con, "SELECT * FROM tbl_foodpartner");
$total_food = mysqli_num_rows($result_food);


$result_users = mysqli_query($con, "SELECT * FROM tbl_user");
$total_users = mysqli_num_rows($result_users);

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT admin_firstname, admin_lastname FROM tbl_admin WHERE admin_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        
        <nav id="sidebar" class="cinema-sidebar">
            <div class="position-sticky">
                <div class="sidebar-header text-center">
                    <i class="bi bi-person-circle display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong>Cinema Admin</strong></h3>
                </div>
                <ul class="list-unstyled components">
                    <li class="active" style="font-size: 1.2em;">
                        <a href="adminDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminCinema.php"><i class="bi bi-camera-reels"></i> Cinema</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminCinemaOwners.php"><i class="bi bi-camera-reels"></i> Cinema Owners</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminFoodPartner.php"><i class="bi bi-egg-fried"></i> Food Partner</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminUsers.php"><i class="bi bi-people"></i> Users</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
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
                                <a class="nav-link dropdown-toggle text-dark" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    Welcome, <?php echo htmlspecialchars($firstname); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="adminLogout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout</a>
                                    </li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>

           
            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Admin Dashboard</h2>
                <div class="row">
                    <div class="col-md-4">
                        <a href="adminCinema.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-camera-reels display-1 mb-3"></i>
                                <h5 class="card-title">Total Cinemas</h5>
                                <p class="card-text display-4"><?php echo $total_cinemas; ?></p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="adminFoodPartner.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-egg-fried display-1 mb-3"></i>
                                <h5 class="card-title">Total Food Partners</h5>
                                <p class="card-text display-4"><?php echo $total_food; ?></p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="adminUsers.php" class="card text-center text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-people display-1 mb-3"></i>
                                <h5 class="card-title">Total Users</h5>
                                <p class="card-text display-4"><?php echo $total_users; ?></p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('active');
        });
    });
</script>
</html>
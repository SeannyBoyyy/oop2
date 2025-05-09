<?php
session_start();
include '../config.php';


if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: adminLogin.php");
    exit();
}


$admin_id = $_SESSION['admin_id'];
$sql = "SELECT admin_firstname, admin_lastname FROM tbl_admin WHERE admin_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); 


if (isset($_GET['action']) && isset($_GET['id'])) {
    $partner_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'verify') {
        $update_sql = "UPDATE tbl_foodpartner SET verification_status = 'verified' WHERE partner_id = ?";
    } elseif ($action == 'unverify') {
        $update_sql = "UPDATE tbl_foodpartner SET verification_status = 'unverified' WHERE partner_id = ?";
    }

    if (isset($update_sql)) {
        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $partner_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    }

    header("Location: adminFoodPartner.php");
    exit();
}


$result_foodpartners = mysqli_query($con, "SELECT * FROM tbl_foodpartner");
if (!$result_foodpartners) {
    die("Query failed: " . mysqli_error($con));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Food Partner</title>
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
                    <li  style="font-size: 1.2em;">
                        <a href="adminDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminCinema.php"><i class="bi bi-camera-reels"></i> Cinema</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminCinemaOwners.php"><i class="bi bi-camera-reels"></i> Cinema Owners</a>
                    </li>
                    <li class="active" style="font-size: 1.2em;">
                        <a href="adminFoodPartner.php"><i class="bi bi-egg-fried"></i> Food Partner</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a href="adminUsers.php"><i class="bi bi-people"></i> Users</a>
                    </li>
                    <li style="font-size: 1.2em;">
                        <a class="text-danger" href="adminLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        
        <div id="content">
            <!-- Top Navbar -->
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
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Business Name</th>
                                <th>DTI Permit</th>
                                <th>Mayor Permit</th>
                                <th>Sanitary Permit</th>
                                <th>Verification Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_foodpartners)) { ?>
                                <tr>
                                    <td><?php echo $row['partner_id']; ?></td>
                                    <td><?php echo $row['partner_firstname']; ?></td>
                                    <td><?php echo $row['partner_lastname']; ?></td>
                                    <td><?php echo $row['partner_email']; ?></td>
                                    <td><?php echo $row['business_name']; ?></td>
                                    <td>
                                        <?php if (file_exists($row['dti_permit'])) { ?>
                                            <a href="<?php echo $row['dti_permit']; ?>" target="_blank">View</a>
                                        <?php } else { ?>
                                            <span class="text-danger">Not Found</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (file_exists($row['mayor_permit'])) { ?>
                                            <a href="<?php echo $row['mayor_permit']; ?>" target="_blank">View</a>
                                        <?php } else { ?>
                                            <span class="text-danger">Not Found</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if (file_exists($row['sanitary_permit'])) { ?>
                                            <a href="<?php echo $row['sanitary_permit']; ?>" target="_blank">View</a>
                                        <?php } else { ?>
                                            <span class="text-danger">Not Found</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $row['verification_status']; ?></td>
                                    <td>
                                        <?php if ($row['verification_status'] == 'unverified') { ?>
                                            <a href="adminFoodPartner.php?action=verify&id=<?php echo $row['partner_id']; ?>" class="btn btn-success btn-sm">Verify</a>
                                        <?php } else { ?>
                                            <a href="adminFoodPartner.php?action=unverify&id=<?php echo $row['partner_id']; ?>" class="btn btn-warning btn-sm">Unverify</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
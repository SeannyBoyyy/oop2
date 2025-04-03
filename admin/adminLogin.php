<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);
    
    if (empty($admin_email) || empty($admin_password)) {
        die("All fields are required!");
    }

    $sql = "SELECT admin_id, admin_firstname, admin_password FROM tbl_admin WHERE admin_email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $admin_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $admin_id, $admin_firstname, $db_password);
        mysqli_stmt_fetch($stmt);

        if (password_verify($admin_password, $db_password)) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['admin_name'] = $admin_firstname;
            $_SESSION['user_type'] = 'admin';
            
            header("Location: adminDashboard.php");
            exit();
        } else {
            echo "<div class='alert alert-danger text-center'>Incorrect password!</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>No admin account found!</div>";
    }

    mysqli_stmt_close($stmt);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../css/adminLogin.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-shield fa-3x mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Admin Login</h4>
                    <p class="text-muted"  style="letter-spacing: 1px;">Please enter your email and password to login.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="adminLogin.php" method="POST">
                            <div class="mb-4">
                                <label for="admin_email" class="form-label " style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"  style="color: black;"></i></span>
                                    <input type="email" class="form-control" name="admin_email" required>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label for="admin_password" class="form-label"  style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"  style="color: black;"></i></span>
                                    <input type="password" class="form-control" name="admin_password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Log In</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="adminSignup.php" style="color: blue;">Don't have an account? Sign up here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
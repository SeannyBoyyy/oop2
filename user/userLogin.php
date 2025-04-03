<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = trim($_POST['user_email']);
    $user_password = trim($_POST['user_password']);
    
    if (empty($user_email) || empty($user_password)) {
        die("All fields are required!");
    }

    $sql = "SELECT user_id, user_firstname, user_password, status FROM tbl_user WHERE user_email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id, $user_firstname, $db_password, $status);
        mysqli_stmt_fetch($stmt);

        if ($status === 'suspended') {
            echo "<div class='alert alert-danger text-center'>Your account is suspended. Please contact support.</div>";
        } else if (password_verify($user_password, $db_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_firstname;
            $_SESSION['user_type'] = 'user';
            
            header("Location: user_homepage.php");
            exit();
        } else {
            echo "<div class='alert alert-danger text-center'>Incorrect password!</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>No user account found!</div>";
    }

    mysqli_stmt_close($stmt);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminLogin.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">User Login</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please enter your email and password to login.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="userLogin.php" method="POST">
                            <div class="mb-4">
                                <label for="user_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                    <input type="email" class="form-control" name="user_email" required>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label for="user_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                    <input type="password" class="form-control" name="user_password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Log In</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="userSignup.php" style="color: blue;">Don't have an account? Sign up here</a>
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
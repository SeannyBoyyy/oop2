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
            
            header("Location: userDashboard.php");
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
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>User Login</h4>
                    </div>
                    <div class="card-body">
                        <form action="userLogin.php" method="POST">
                            <div class="mb-3">
                                <label for="user_email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="user_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="user_password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="user_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="userSignup.php">Don't have an account? Sign up here</a>
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
<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_firstname = trim($_POST['user_firstname']);
    $user_lastname = trim($_POST['user_lastname']);
    $user_email = trim($_POST['user_email']);
    $user_password = password_hash(trim($_POST['user_password']), PASSWORD_DEFAULT);
    $user_contact_number = trim($_POST['user_contact_number']);
    $user_address = trim($_POST['user_address']);
    
    if (empty($user_firstname) || empty($user_lastname) || empty($user_email) || empty($_POST['user_password'])) {
        die("All fields are required!");
    }

    $check_email = "SELECT user_email FROM tbl_user WHERE user_email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $user_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        echo "<div class='alert alert-danger text-center' role='alert'>
            Email already exists!
              </div>";
        exit();
    }
    
    $sql = "INSERT INTO tbl_user (user_firstname, user_lastname, user_email, user_password, user_contact_number, user_address) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $user_firstname, $user_lastname, $user_email, $user_password, $user_contact_number, $user_address);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success text-center' role='alert'>
            User account created successfully! Redirecting to login...
              </div>";
        header("Location: userLogin.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center' role='alert'>
            Error: " . mysqli_error($con) . "
              </div>";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Sign-Up | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>User Sign-Up</h4>
                    </div>
                    <div class="card-body">
                        <form action="userSignup.php" method="POST">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="user_firstname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="user_lastname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="user_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="user_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contact Number</label>
                                <input type="text" name="user_contact_number" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <textarea name="user_address" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="userLogin.php">Already have an account? Login here</a>
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
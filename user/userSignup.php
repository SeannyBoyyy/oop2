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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminSignup.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">User Sign-Up</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please fill in the form below to create a user account.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="userSignup.php" method="POST">
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_firstname" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Last Name -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_lastname" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                        <input type="email" name="user_email" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Password -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                        <input type="password" name="user_password" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Contact Number -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Contact Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_contact_number" class="form-control">
                                    </div>
                                </div>
                                <!-- Address -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-geo-alt-fill" style="color: black;"></i></span>
                                        <textarea name="user_address" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="userLogin.php" style="color: blue;">Already have an account? Login here</a>
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
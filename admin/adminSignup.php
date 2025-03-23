<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_firstname = trim($_POST['admin_firstname']);
    $admin_lastname = trim($_POST['admin_lastname']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = password_hash(trim($_POST['admin_password']), PASSWORD_DEFAULT);

    
    if (empty($admin_firstname) || empty($admin_lastname) || empty($admin_email) || empty($_POST['admin_password'])) {
        die("All fields are required!");
    }

   
    $check_email = "SELECT admin_email FROM tbl_admin WHERE admin_email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $admin_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        echo "<div class='alert alert-danger text-center' role='alert'>
            Email already exists!
              </div>";
        exit();
    }
    
    
    $sql = "INSERT INTO tbl_admin (admin_firstname, admin_lastname, admin_email, admin_password) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $admin_firstname, $admin_lastname, $admin_email, $admin_password);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success text-center' role='alert'>
            Admin account created successfully! Redirecting to login...
              </div>";
           header("Location: adminLogin.php");
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
    <title>Admin Sign-Up | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminSignup.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <i class="fas fa-user-shield fa-3x mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Admin Sign-Up</h4>
                    <p class="text-muted " style="letter-spacing: 1px;">Please fill in the form below to create an admin account.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    
                    <div class="card-body">
                        <form action="adminSignup.php" method="POST">
                            <div class="mb-4">
                                <label class="form-label" style="letter-spacing: 2px;">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="admin_firstname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="admin_lastname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="admin_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="admin_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black " style="letter-spacing: 2px;padding: 10px 20px;">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="adminLogin.php" style="color: blue;">Already have an account? Login here</a>
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
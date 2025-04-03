<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_email = trim($_POST['owner_email']);
    $owner_password = trim($_POST['owner_password']);

    // Check if email exists
    $sql = "SELECT owner_id, owner_firstname, owner_lastname, owner_email, owner_password, verification_status, status 
            FROM tbl_cinema_owner 
            WHERE owner_email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $owner_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Verify password
        if (password_verify($owner_password, $row['owner_password'])) {
            if ($row['verification_status'] !== 'verified') {
                echo "<div class='alert alert-warning text-center'>Your account is not yet verified.</div>";
            } elseif ($row['status'] !== 'active') {
                echo "<div class='alert alert-danger text-center'>Your account is inactive. Please contact support.</div>";
            } else {
                // Start session and store user details
                $_SESSION['owner_id'] = $row['owner_id'];
                $_SESSION['owner_firstname'] = $row['owner_firstname'];
                $_SESSION['owner_lastname'] = $row['owner_lastname'];
                $_SESSION['owner_email'] = $row['owner_email'];

                // Check if the cinema profile exists
                $check_profile_sql = "SELECT cinema_id FROM tbl_cinema WHERE owner_id = ?";
                $check_profile_stmt = mysqli_prepare($con, $check_profile_sql);
                mysqli_stmt_bind_param($check_profile_stmt, "i", $row['owner_id']);
                mysqli_stmt_execute($check_profile_stmt);
                $cinema_result = mysqli_stmt_get_result($check_profile_stmt);

                if ($cinema_row = mysqli_fetch_assoc($cinema_result)) {
                    $_SESSION['cinema_id'] = $cinema_row['cinema_id']; // Store cinema_id in session
                    header("Location: cinemaOwnerDashboard.php"); // Redirect to dashboard
                } else {
                    header("Location: uploadCinemaProfile.php"); // Redirect to profile setup
                }
                exit();
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Invalid email or password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Account not found.</div>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Owner Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminLogin.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-tie fa-3x mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Cinema Owner Login</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please enter your email and password to login.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="cinemaOwnerLogin.php" method="POST">
                            <div class="mb-4">
                                <label for="owner_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill"  style="color: black;"></i></span>
                                    <input type="email" class="form-control" name="owner_email" required>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label for="owner_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                    <input type="password" class="form-control" name="owner_password" required>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary  text-black" style="letter-spacing: 2px; padding: 10px 20px;">Log In</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="cinemaOwnerSignup.php" style="color: blue;">Don't have an account? Sign up here</a>
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

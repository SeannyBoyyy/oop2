<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_firstname = trim($_POST['owner_firstname']);
    $owner_lastname = trim($_POST['owner_lastname']);
    $owner_email = trim($_POST['owner_email']);
    $owner_password = password_hash(trim($_POST['owner_password']), PASSWORD_DEFAULT);
    $owner_address = trim($_POST['owner_address']);
    $cinema_name = trim($_POST['cinema_name']);
    
    // File upload directory
    $upload_dir = "../cinema/uploads/permits/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Function to handle file uploads
    function uploadFile($file, $prefix) {
        global $upload_dir;
        $target_file = $upload_dir . $prefix . "_" . time() . "_" . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validate file type and size
        if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
            die("Only JPG, JPEG, and PNG files are allowed.");
        }
        if ($file["size"] > 5000000) {
            die("File is too large. Maximum size is 5MB.");
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            die("Error uploading file.");
        }
    }

    // Handle file uploads for permits
    $dti_permit = uploadFile($_FILES["dti_permit"], "dti");
    $mayor_permit = uploadFile($_FILES["mayor_permit"], "mayor");
    $sanitary_permit = uploadFile($_FILES["sanitary_permit"], "sanitary");

    // Check if email already exists
    $check_email = "SELECT owner_email FROM tbl_cinema_owner WHERE owner_email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $owner_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "<div class='alert alert-danger text-center'>Email already exists!</div>";
        exit();
    }
    
    // Insert into database
    $sql = "INSERT INTO tbl_cinema_owner (owner_firstname, owner_lastname, owner_email, 
            owner_password, owner_address, cinema_name, dti_permit, mayor_permit, sanitary_permit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $owner_firstname, $owner_lastname, $owner_email, 
                          $owner_password, $owner_address, $cinema_name, $dti_permit, 
                          $mayor_permit, $sanitary_permit);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success text-center'>Cinema Owner account created successfully! Please wait for verification.</div>";
        header("Location: cinemaOwnerLogin.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . mysqli_error($con) . "</div>";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Owner Sign-Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Cinema Owner Sign-Up</h4>
                    </div>
                    <div class="card-body">
                        <form action="cinemaOwnerSignup.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="owner_firstname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="owner_lastname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="owner_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="owner_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Business Address</label>
                                <textarea name="owner_address" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Cinema Name</label>
                                <input type="text" name="cinema_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>DTI Permit</label>
                                <input type="file" name="dti_permit" class="form-control" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label>Mayor's Permit</label>
                                <input type="file" name="mayor_permit" class="form-control" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label>Sanitary Permit</label>
                                <input type="file" name="sanitary_permit" class="form-control" accept="image/*" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="cinemaOwnerLogin.php">Already have an account? Login here</a>
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

<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partner_firstname = trim($_POST['partner_firstname']);
    $partner_lastname = trim($_POST['partner_lastname']);
    $partner_email = trim($_POST['partner_email']);
    $partner_password = password_hash(trim($_POST['partner_password']), PASSWORD_DEFAULT);
    $partner_address = trim($_POST['partner_address']);
    $business_name = trim($_POST['business_name']);
    
    // File upload handling
    $upload_dir = "../foodpartner/uploads/permits/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Function to handle file upload
    function uploadFile($file, $prefix) {
        global $upload_dir;
        $target_file = $upload_dir . $prefix . "_" . time() . "_" . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is a actual image or fake image
        $check = getimagesize($file["tmp_name"]);
        if($check === false) {
            die("File is not an image.");
        }
        
        // Check file size (5MB max)
        if ($file["size"] > 5000000) {
            die("File is too large. Maximum size is 5MB.");
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            die("Only JPG, JPEG, PNG files are allowed.");
        }
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            die("Error uploading file.");
        }
    }

    // Handle permit uploads
    $dti_permit = uploadFile($_FILES["dti_permit"], "dti");
    $mayor_permit = uploadFile($_FILES["mayor_permit"], "mayor");
    $sanitary_permit = uploadFile($_FILES["sanitary_permit"], "sanitary");

    // Check email existence
    $check_email = "SELECT partner_email FROM tbl_foodpartner WHERE partner_email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $partner_email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        echo "<div class='alert alert-danger text-center'>Email already exists!</div>";
        exit();
    }
    
    $sql = "INSERT INTO tbl_foodpartner (partner_firstname, partner_lastname, partner_email, 
            partner_password, partner_address, business_name, dti_permit, mayor_permit, sanitary_permit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss", $partner_firstname, $partner_lastname, $partner_email, 
                          $partner_password, $partner_address, $business_name, $dti_permit, 
                          $mayor_permit, $sanitary_permit);
    
    if (mysqli_stmt_execute($stmt)) {
    // Redirect to subscription payment
    // After successful registration, store the partner email in the session
    $_SESSION['partner_email'] = $partner_email; // ✅ Set the session variable
        header("Location: paymongo_subscription.php?partner_email=" . urlencode($partner_email));
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
    <title>Food Partner Sign-Up | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Food Partner Sign-Up</h4>
                    </div>
                    <div class="card-body">
                        <form action="foodPartnerSignup.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>First Name</label>
                                <input type="text" name="partner_firstname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Last Name</label>
                                <input type="text" name="partner_lastname" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="partner_email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="partner_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Business Address</label>
                                <textarea name="partner_address" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Business Name</label>
                                <input type="text" name="business_name" class="form-control" required>
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
                                <a href="foodPartnerLogin.php">Already have a partner account? Login here</a>
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
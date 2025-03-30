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
    $cinema_id = trim($_POST['cinema_id']); // Get selected cinema ID
    
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
            partner_password, partner_address, business_name, dti_permit, mayor_permit, 
            sanitary_permit, cinema_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssssi", $partner_firstname, $partner_lastname, $partner_email, 
                          $partner_password, $partner_address, $business_name, $dti_permit, 
                          $mayor_permit, $sanitary_permit, $cinema_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['partner_email'] = $partner_email;
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
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/foodPartnerSignup.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <i class="fas fa-store-alt fa-3x mb-3" style="color: #17a2b8; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Food Partner Sign-Up</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Join us and expand your food business with cinema partnerships.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="foodPartnerSignup.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="partner_firstname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="partner_lastname" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="partner_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="partner_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Business Address</label>
                                <textarea name="partner_address" class="form-control" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Business Name</label>
                                <input type="text" name="business_name" class="form-control" required>
                            </div>
                            <!-- Cinema Selection Dropdown -->
                            <div class="mb-3">
                                <label class="form-label" style="letter-spacing: 2px;">Select Cinema</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-film"></i></span>
                                    <select name="cinema_id" class="form-control" required>
                                        <option value="" disabled selected>Select Cinema</option>
                                        <?php
                                        $cinema_query = "SELECT cinema_id, name FROM tbl_cinema";
                                        $cinema_result = mysqli_query($con, $cinema_query);
                                        while ($cinema = mysqli_fetch_assoc($cinema_result)) {
                                            echo "<option value='{$cinema['cinema_id']}'>{$cinema['name']}</option>";
                                        }
                                        
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">DTI Permits</label>
                                <input type="file" name="dti_permit" class="form-control" required>
                                <label class="form-label">Mayor Permits</label>
                                <input type="file" name="mayor_permit" class="form-control mt-2" required>
                                <label class="form-label">Sanitary Permits</label>
                                <input type="file" name="sanitary_permit" class="form-control mt-2" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-info">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="foodPartnerLogin.php">Already have an account? Login here</a>
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
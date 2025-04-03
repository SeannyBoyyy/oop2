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
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminSignup.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <i class="fas fa-user-tie fa-3x mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Cinema Owner Sign-Up</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please fill in the form below to create a cinema owner account.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="cinemaOwnerSignup.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill"  style="color: black;"></i></span>
                                        <input type="text" name="owner_firstname" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Last Name -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill"  style="color: black;"></i></span>
                                        <input type="text" name="owner_lastname" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;" >Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"  style="color: black;"></i></span>
                                        <input type="email" name="owner_email" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Password -->
                                <div class="col-md-6 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"  style="color: black;"></i></span>
                                        <input type="password" name="owner_password" class="form-control" required>
                                    </div>
                                </div>
                                <!-- Business Address -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Business Address</label>
                                    <textarea name="owner_address" class="form-control" required></textarea>
                                </div>
                                <!-- Cinema Name -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Cinema Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building-fill"  style="color: black;"></i></span>
                                        <input type="text" name="cinema_name" class="form-control" required>
                                    </div>
                                </div>
                                <!-- DTI Permit -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">DTI Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"  style="color: black;"></i></span>
                                        <input type="file" name="dti_permit" class="form-control" accept="image/*" required>
                                    </div>
                                </div>
                                <!-- Mayor's Permit -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Mayor's Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"  style="color: black;"></i></span>
                                        <input type="file" name="mayor_permit" class="form-control" accept="image/*" required>
                                    </div>
                                </div>
                                <!-- Sanitary Permit -->
                                <div class="col-md-4 mb-4">
                                    <label class="form-label" style="letter-spacing: 2px;">Sanitary Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"  style="color: black;"></i></span>
                                        <input type="file" name="sanitary_permit" class="form-control" accept="image/*" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="cinemaOwnerLogin.php" style="color: blue;">Already have an account? Login here</a>
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


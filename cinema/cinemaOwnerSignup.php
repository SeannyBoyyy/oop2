<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $owner_firstname = trim($_POST['owner_firstname']);
    $owner_lastname = trim($_POST['owner_lastname']);
    $owner_email = trim($_POST['owner_email']);
    $owner_password = trim($_POST['owner_password']);
    $owner_address = trim($_POST['owner_address']);
    $cinema_name = trim($_POST['cinema_name']);
    
    // Validate required fields
    if (empty($owner_firstname) || empty($owner_lastname) || empty($owner_email) || 
        empty($owner_password) || empty($owner_address) || empty($cinema_name)) {
        $signupError = "All fields are required!";
    } 
    // Password validation
    else if (strlen($owner_password) < 8 || 
             !preg_match('/[A-Z]/', $owner_password) || 
             !preg_match('/[a-z]/', $owner_password) || 
             !preg_match('/[0-9]/', $owner_password) || 
             !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\\|,.<>\/?]/', $owner_password)) {
        $signupError = "Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters.";
    }
    else {
        // Hash the password
        $hashed_password = password_hash($owner_password, PASSWORD_DEFAULT);
        
        // File upload directory
        $upload_dir = "../cinema/uploads/permits/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Function to handle file uploads
        function uploadFile($file, $prefix) {
            global $upload_dir, $signupError;
            $target_file = $upload_dir . $prefix . "_" . time() . "_" . basename($file["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Validate file type and size
            if (!in_array($imageFileType, ["jpg", "jpeg", "png"])) {
                $signupError = "Only JPG, JPEG, and PNG files are allowed.";
                return false;
            }
            if ($file["size"] > 5000000) {
                $signupError = "File is too large. Maximum size is 5MB.";
                return false;
            }

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                return $target_file;
            } else {
                $signupError = "Error uploading file.";
                return false;
            }
        }

        // Check if all required files are uploaded
        if (!isset($_FILES["dti_permit"]) || $_FILES["dti_permit"]["error"] != 0 ||
            !isset($_FILES["mayor_permit"]) || $_FILES["mayor_permit"]["error"] != 0 ||
            !isset($_FILES["sanitary_permit"]) || $_FILES["sanitary_permit"]["error"] != 0) {
            $signupError = "All permits are required.";
        } else {
            // Handle file uploads for permits
            $dti_permit = uploadFile($_FILES["dti_permit"], "dti");
            $mayor_permit = uploadFile($_FILES["mayor_permit"], "mayor");
            $sanitary_permit = uploadFile($_FILES["sanitary_permit"], "sanitary");

            if ($dti_permit && $mayor_permit && $sanitary_permit) {
                // Check if email already exists
                $check_email = "SELECT owner_email FROM tbl_cinema_owner WHERE owner_email = ?";
                $stmt = mysqli_prepare($con, $check_email);
                mysqli_stmt_bind_param($stmt, "s", $owner_email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $signupError = "Email already exists!";
                } else {
                    // Insert into database
                    $sql = "INSERT INTO tbl_cinema_owner (owner_firstname, owner_lastname, owner_email, 
                            owner_password, owner_address, cinema_name, dti_permit, mayor_permit, sanitary_permit) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssssss", $owner_firstname, $owner_lastname, $owner_email, 
                                          $hashed_password, $owner_address, $cinema_name, $dti_permit, 
                                          $mayor_permit, $sanitary_permit);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $signupSuccess = true;
                    } else {
                        $signupError = "Error: " . mysqli_error($con);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
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
                        <form action="cinemaOwnerSignup.php" method="POST" enctype="multipart/form-data" novalidate>
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-4">
                                    <label for="owner_firstname" class="form-label" style="letter-spacing: 2px;">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="owner_firstname" id="owner_firstname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your first name.</div>
                                    </div>
                                </div>
                                <!-- Last Name -->
                                <div class="col-md-6 mb-4">
                                    <label for="owner_lastname" class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="owner_lastname" id="owner_lastname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your last name.</div>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="col-md-6 mb-4">
                                    <label for="owner_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                        <input type="email" name="owner_email" id="owner_email" class="form-control" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="owner_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                        <input type="password" name="owner_password" id="owner_password" class="form-control" 
                                               required minlength="8" 
                                               pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\\|,.<>\/?])(?=.*[0-9]).{8,}$">
                                        <span class="input-group-text">
                                            <i class="bi bi-eye-fill password-toggle" style="cursor: pointer; color: black;"></i>
                                        </span>
                                        <div class="invalid-feedback">Password must be at least 8 characters with uppercase, lowercase, and special characters.</div>
                                    </div>
                                    <div class="password-requirements small text-muted mt-1">
                                        <div class="requirement" id="length-req"><i class="bi bi-circle"></i> Minimum 8 characters</div>
                                        <div class="requirement" id="uppercase-req"><i class="bi bi-circle"></i> At least one uppercase letter</div>
                                        <div class="requirement" id="lowercase-req"><i class="bi bi-circle"></i> At least one lowercase letter</div>
                                        <div class="requirement" id="special-req"><i class="bi bi-circle"></i> At least one special character</div>
                                        <div class="requirement" id="number-req"><i class="bi bi-circle"></i> At least one number</div>
                                    </div>
                                </div>
                                <!-- Business Address -->
                                <div class="col-md-12 mb-4">
                                    <label for="owner_address" class="form-label" style="letter-spacing: 2px;">Business Address</label>
                                    <textarea name="owner_address" id="owner_address" class="form-control" required></textarea>
                                    <div class="invalid-feedback">Please enter your business address.</div>
                                </div>
                                <!-- Cinema Name -->
                                <div class="col-md-12 mb-4">
                                    <label for="cinema_name" class="form-label" style="letter-spacing: 2px;">Cinema Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-building-fill" style="color: black;"></i></span>
                                        <input type="text" name="cinema_name" id="cinema_name" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your cinema name.</div>
                                    </div>
                                </div>
                                <!-- DTI Permit -->
                                <div class="col-md-4 mb-4">
                                    <label for="dti_permit" class="form-label" style="letter-spacing: 2px;">DTI Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill" style="color: black;"></i></span>
                                        <input type="file" name="dti_permit" id="dti_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your DTI permit.</div>
                                    </div>
                                </div>
                                <!-- Mayor's Permit -->
                                <div class="col-md-4 mb-4">
                                    <label for="mayor_permit" class="form-label" style="letter-spacing: 2px;">Mayor's Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill" style="color: black;"></i></span>
                                        <input type="file" name="mayor_permit" id="mayor_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your Mayor's permit.</div>
                                    </div>
                                </div>
                                <!-- Sanitary Permit -->
                                <div class="col-md-4 mb-4">
                                    <label for="sanitary_permit" class="form-label" style="letter-spacing: 2px;">Sanitary Permit</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill" style="color: black;"></i></span>
                                        <input type="file" name="sanitary_permit" id="sanitary_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your Sanitary permit.</div>
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

    <!-- Signup Error Modal -->
    <div class="modal fade" id="signupErrorModal" tabindex="-1" aria-labelledby="signupErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div class="mt-3 text-center">
                        <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 3.5rem;"></i>
                        <h4 class="mt-3 mb-0 fw-bold">Registration Failed</h4>
                    </div>
                    
                    <div class="p-4 text-center">
                        <p id="signupErrorMessage" class="fs-5 mb-4">There was an error with your registration. Please try again.</p>
                        <button type="button" class="btn btn-danger px-5 py-2 rounded-pill" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signup Success Modal -->
    <div class="modal fade" id="signupSuccessModal" tabindex="-1" aria-labelledby="signupSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div class="mt-3 text-center">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                        <h4 class="mt-3 mb-0 fw-bold">Registration Successful</h4>
                    </div>
                    
                    <div class="p-4 text-center">
                        <p class="fs-5 mb-4">Your cinema owner account has been created successfully! Please wait for admin verification.</p>
                        <a href="cinemaOwnerLogin.php" class="btn btn-success px-5 py-2 rounded-pill">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Proceed to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateInput(input, force = false) {
        const errorMessage = input.nextElementSibling;
        if (!errorMessage || !errorMessage.classList.contains('invalid-feedback')) return;
        
        const interacted = input.getAttribute('data-interacted') === 'true' || force;
        
        if (interacted) {
            if (input.validity.valid) {
                input.classList.remove('is-invalid');
            } else {
                input.classList.add('is-invalid');
            }
        }
    }

    window.addEventListener('load', function() {
        document.querySelectorAll('input, textarea').forEach(input => {
            input.setAttribute('data-interacted', 'false');
            
            input.addEventListener('input', function() {
                this.setAttribute('data-interacted', 'true');
                validateInput(this);
            });
            
            input.addEventListener('keyup', function() {
                this.setAttribute('data-interacted', 'true');
                validateInput(this);
            });
            
            input.addEventListener('blur', function() {
                this.setAttribute('data-interacted', 'true');
                validateInput(this);
            });
        });
        
        document.querySelector('form').addEventListener('submit', (event) => {
            let formValid = true;
            
            document.querySelectorAll('input, textarea').forEach(input => {
                validateInput(input, true);
                
                if (!input.validity.valid) {
                    formValid = false;
                }
            });

            if (!formValid) {
                event.preventDefault();
            }
        });

        const togglePassword = document.querySelector('.password-toggle');
        const passwordInput = document.querySelector('input[name="owner_password"]');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('bi-eye-fill');
                this.classList.toggle('bi-eye-slash-fill');
            });
        }

        // Password validation with visual feedback
        if (passwordInput) {
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const lowercaseReq = document.getElementById('lowercase-req');
            const specialReq = document.getElementById('special-req');
            const numberReq = document.getElementById('number-req');

            passwordInput.addEventListener('input', function() {
                // Check length
                if (this.value.length >= 8) {
                    lengthReq.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Minimum 8 characters';
                } else {
                    lengthReq.innerHTML = '<i class="bi bi-circle"></i> Minimum 8 characters';
                }
                
                // Check uppercase
                if (/[A-Z]/.test(this.value)) {
                    uppercaseReq.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> At least one uppercase letter';
                } else {
                    uppercaseReq.innerHTML = '<i class="bi bi-circle"></i> At least one uppercase letter';
                }
                
                // Check lowercase
                if (/[a-z]/.test(this.value)) {
                    lowercaseReq.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> At least one lowercase letter';
                } else {
                    lowercaseReq.innerHTML = '<i class="bi bi-circle"></i> At least one lowercase letter';
                }
                
                // Check special character
                if (/[!@#$%^&*()_+\-=\[\]{};':\\|,.<>\/?]/.test(this.value)) {
                    specialReq.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> At least one special character';
                } else {
                    specialReq.innerHTML = '<i class="bi bi-circle"></i> At least one special character';
                }
                
                // Check number
                if (/[0-9]/.test(this.value)) {
                    numberReq.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> At least one number';
                } else {
                    numberReq.innerHTML = '<i class="bi bi-circle"></i> At least one number';
                }
            });
        }

        <?php if(isset($signupError)): ?>
            const signupErrorMessage = document.getElementById('signupErrorMessage');
            signupErrorMessage.textContent = "<?php echo $signupError; ?>";
            
            const signupErrorModal = new bootstrap.Modal(document.getElementById('signupErrorModal'));
            signupErrorModal.show();
        <?php endif; ?>
        <?php if(isset($signupSuccess) && $signupSuccess): ?>
            const signupSuccessModal = new bootstrap.Modal(document.getElementById('signupSuccessModal'));
            signupSuccessModal.show();
            
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        <?php endif; ?>
    });
    </script>
</body>
</html>
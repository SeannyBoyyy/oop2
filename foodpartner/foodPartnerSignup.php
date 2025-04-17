<?php
session_start();
include '../config.php';

$signupError = '';
$signupSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partner_firstname = trim($_POST['partner_firstname']);
    $partner_lastname = trim($_POST['partner_lastname']);
    $partner_email = trim($_POST['partner_email']);
    $partner_password = trim($_POST['partner_password']);
    $partner_address = trim($_POST['partner_address']);
    $business_name = trim($_POST['business_name']);
    $cinema_id = isset($_POST['cinema_id']) ? trim($_POST['cinema_id']) : '';
    
    // Validation
    if (empty($partner_firstname) || empty($partner_lastname) || empty($partner_email) || 
        empty($partner_password) || empty($partner_address) || empty($business_name) || empty($cinema_id)) {
        $signupError = "All fields are required!";
    } 
    // Password validation
    else if (strlen($partner_password) < 8 || 
             !preg_match('/[A-Z]/', $partner_password) || 
             !preg_match('/[a-z]/', $partner_password) || 
             !preg_match('/[0-9]/', $partner_password) || 
             !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\\|,.<>\/?]/', $partner_password)) {
        $signupError = "Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters.";
    }
    else {
        // File upload handling
        $upload_dir = "../foodpartner/uploads/permits/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Function to handle file upload
        function uploadFile($file, $prefix) {
            global $upload_dir, $signupError;
            $target_file = $upload_dir . $prefix . "_" . time() . "_" . basename($file["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is a actual image or fake image
            $check = getimagesize($file["tmp_name"]);
            if($check === false) {
                $signupError = "File is not an image.";
                return false;
            }
            
            // Check file size (5MB max)
            if ($file["size"] > 5000000) {
                $signupError = "File is too large. Maximum size is 5MB.";
                return false;
            }
            
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $signupError = "Only JPG, JPEG, PNG files are allowed.";
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
            // Handle permit uploads
            $dti_permit = uploadFile($_FILES["dti_permit"], "dti");
            $mayor_permit = uploadFile($_FILES["mayor_permit"], "mayor");
            $sanitary_permit = uploadFile($_FILES["sanitary_permit"], "sanitary");

            if ($dti_permit && $mayor_permit && $sanitary_permit) {
                // Check email existence
                $check_email = "SELECT partner_email FROM tbl_foodpartner WHERE partner_email = ?";
                $stmt = mysqli_prepare($con, $check_email);
                mysqli_stmt_bind_param($stmt, "s", $partner_email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $signupError = "Email already exists!";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($partner_password, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO tbl_foodpartner (partner_firstname, partner_lastname, partner_email, 
                            partner_password, partner_address, business_name, dti_permit, mayor_permit, 
                            sanitary_permit, cinema_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssssssi", $partner_firstname, $partner_lastname, $partner_email, 
                                        $hashed_password, $partner_address, $business_name, $dti_permit, 
                                        $mayor_permit, $sanitary_permit, $cinema_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $signupSuccess = true;
                        $_SESSION['partner_email'] = $partner_email;
                        $redirect_url = "paymongo_subscription.php?partner_email=" . urlencode($partner_email);
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
    <title>Food Partner Sign-Up | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/foodPartnerSignup.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <i class="fas fa-utensils mb-3" style="color:rgb(238, 215, 13); font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Food Partner Sign-Up</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Join us and expand your food business with cinema partnerships.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="foodPartnerSignup.php" method="POST" enctype="multipart/form-data" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="partner_firstname" class="form-label" style="letter-spacing: 2px;">First Name</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="partner_firstname" id="partner_firstname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your first name.</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="partner_lastname" class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="partner_lastname" id="partner_lastname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your last name.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="partner_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="partner_email" id="partner_email" class="form-control" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="partner_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="partner_password" id="partner_password" class="form-control" 
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
                            </div>
                            
                            <div class="mb-4">
                                <label for="business_name" class="form-label" style="letter-spacing: 2px;">Business Name</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-store"></i></span>
                                    <input type="text" name="business_name" id="business_name" class="form-control" required>
                                    <div class="invalid-feedback">Please enter your business name.</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="partner_address" class="form-label" style="letter-spacing: 2px;">Business Address</label>
                                <div class="input-group has-validation">
                                    <textarea name="partner_address" id="partner_address" class="form-control" required></textarea>
                                    <div class="invalid-feedback">Please enter your business address.</div>
                                </div>
                            </div>
                            
                            <!-- Cinema Selection Dropdown -->
                            <div class="mb-4">
                                <label for="cinema_id" class="form-label" style="letter-spacing: 2px;">Select Cinema</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-film"></i></span>
                                    <select name="cinema_id" id="cinema_id" class="form-control" required>
                                        <option value="" disabled selected>Select Cinema</option>
                                        <?php
                                        $cinema_query = "SELECT cinema_id, name FROM tbl_cinema";
                                        $cinema_result = mysqli_query($con, $cinema_query);
                                        while ($cinema = mysqli_fetch_assoc($cinema_result)) {
                                            echo "<option value='{$cinema['cinema_id']}'>{$cinema['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a cinema.</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label for="dti_permit" class="form-label" style="letter-spacing: 2px;">DTI Permit</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"></i></span>
                                        <input type="file" name="dti_permit" id="dti_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your DTI permit.</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="mayor_permit" class="form-label" style="letter-spacing: 2px;">Mayor's Permit</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"></i></span>
                                        <input type="file" name="mayor_permit" id="mayor_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your Mayor's permit.</div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label for="sanitary_permit" class="form-label" style="letter-spacing: 2px;">Sanitary Permit</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-file-earmark-text-fill"></i></span>
                                        <input type="file" name="sanitary_permit" id="sanitary_permit" class="form-control" accept="image/*" required>
                                        <div class="invalid-feedback">Please upload your Sanitary permit.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-warning text-white" style="letter-spacing: 2px; padding: 10px 20px;">Sign Up</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="foodPartnerLogin.php" style="color: blue;">Already have an account? Login here</a>
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
                        <p class="fs-5 mb-4">Your food partner account has been created successfully!</p>
                        <a href="<?php echo isset($redirect_url) ? $redirect_url : 'paymongo_subscription.php'; ?>" class="btn btn-success px-5 py-2 rounded-pill">
                            <i class="bi bi-credit-card-fill me-2"></i>Continue to Subscription
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateInput(input, force = false) {
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
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.setAttribute('data-interacted', 'false');
            
            input.addEventListener('input', function() {
                this.setAttribute('data-interacted', 'true');
                validateInput(this);
            });
            
            input.addEventListener('change', function() {
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
            
            document.querySelectorAll('input, select, textarea').forEach(input => {
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
        const passwordInput = document.querySelector('input[name="partner_password"]');
        
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

        <?php if(!empty($signupError)): ?>
            const signupErrorMessage = document.getElementById('signupErrorMessage');
            signupErrorMessage.textContent = "<?php echo $signupError; ?>";
            
            const signupErrorModal = new bootstrap.Modal(document.getElementById('signupErrorModal'));
            signupErrorModal.show();
        <?php endif; ?>
        <?php if($signupSuccess): ?>
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
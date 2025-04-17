<?php
session_start();
include '../config.php';

$signupError = '';
$signupSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_firstname = trim($_POST['user_firstname']);
    $user_lastname = trim($_POST['user_lastname']);
    $user_email = trim($_POST['user_email']);
    $user_password = trim($_POST['user_password']);
    $user_contact_number = trim($_POST['user_contact_number']);
    $user_address = trim($_POST['user_address']);
    
    // Validation
    if (empty($user_firstname) || empty($user_lastname) || empty($user_email) || 
        empty($user_password) || empty($user_contact_number) || empty($user_address)) {
        $signupError = "All required fields must be filled out!";
    } 
    // Password validation
    else if (strlen($user_password) < 8 || 
             !preg_match('/[A-Z]/', $user_password) || 
             !preg_match('/[a-z]/', $user_password) || 
             !preg_match('/[0-9]/', $user_password) || 
             !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\\|,.<>\/?]/', $user_password)) {
        $signupError = "Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters.";
    }
    else {
        // Check if email already exists
        $check_email = "SELECT user_email FROM tbl_user WHERE user_email = ?";
        $stmt = mysqli_prepare($con, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $signupError = "Email already exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO tbl_user (user_firstname, user_lastname, user_email, user_password, user_contact_number, user_address) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $user_firstname, $user_lastname, $user_email, $hashed_password, $user_contact_number, $user_address);
            
            if (mysqli_stmt_execute($stmt)) {
                $signupSuccess = true;
            } else {
                $signupError = "Error: " . mysqli_error($con);
            }
        }
        mysqli_stmt_close($stmt);
    }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
                        <form action="userSignup.php" method="POST" novalidate>
                            <div class="row">
                                <!-- First Name -->
                                <div class="col-md-6 mb-4">
                                    <label for="user_firstname" class="form-label" style="letter-spacing: 2px;">First Name</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_firstname" id="user_firstname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your first name.</div>
                                    </div>
                                </div>
                                <!-- Last Name -->
                                <div class="col-md-6 mb-4">
                                    <label for="user_lastname" class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_lastname" id="user_lastname" class="form-control" required>
                                        <div class="invalid-feedback">Please enter your last name.</div>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="col-md-6 mb-4">
                                    <label for="user_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                        <input type="email" name="user_email" id="user_email" class="form-control" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>
                                <!-- Password -->
                                <div class="col-md-6 mb-4">
                                    <label for="user_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                        <input type="password" name="user_password" id="user_password" class="form-control" 
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
                                <!-- Contact Number -->
                                <div class="col-md-12 mb-4">
                                    <label for="user_contact_number" class="form-label" style="letter-spacing: 2px;">Contact Number</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-telephone-fill" style="color: black;"></i></span>
                                        <input type="text" name="user_contact_number" id="user_contact_number" class="form-control" pattern="[0-9]{10,11}" required>
                                        <div class="invalid-feedback">Please enter a valid contact number (10-11 digits).</div>
                                    </div>
                                </div>
                                <!-- Address -->
                                <div class="col-md-12 mb-4">
                                    <label for="user_address" class="form-label" style="letter-spacing: 2px;">Address</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text"><i class="bi bi-geo-alt-fill" style="color: black;"></i></span>
                                        <textarea name="user_address" id="user_address" class="form-control" rows="3" required></textarea>
                                        <div class="invalid-feedback">Please enter your address.</div>
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
                        <p class="fs-5 mb-4">Your user account has been created successfully!</p>
                        <a href="userLogin.php" class="btn btn-success px-5 py-2 rounded-pill">
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
            
            document.querySelectorAll('input, textarea').forEach(input => {
                validateInput(input, true);
                
                if (!input.validity.valid && input.hasAttribute('required')) {
                    formValid = false;
                }
            });

            if (!formValid) {
                event.preventDefault();
            }
        });

        const togglePassword = document.querySelector('.password-toggle');
        const passwordInput = document.querySelector('input[name="user_password"]');
        
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
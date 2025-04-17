<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_firstname = trim($_POST['admin_firstname']);
    $admin_lastname = trim($_POST['admin_lastname']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);
    
    if (empty($admin_firstname) || empty($admin_lastname) || empty($admin_email) || empty($admin_password)) {
        $signupError = "All fields are required!";
    } else if (strlen($admin_password) < 8 || 
               !preg_match('/[A-Z]/', $admin_password) || 
               !preg_match('/[a-z]/', $admin_password) || 
               !preg_match('/[0-9]/', $admin_password) || 
               !preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\\|,.<>\/?]/', $admin_password)) {
        $signupError = "Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters.";
    } else {
        // Check if email already exists
        $check_email = "SELECT admin_email FROM tbl_admin WHERE admin_email = ?";
        $stmt = mysqli_prepare($con, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $admin_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            $signupError = "Email already exists!";
        } else {
            // Hash the password
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $sql = "INSERT INTO tbl_admin (admin_firstname, admin_lastname, admin_email, admin_password) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $admin_firstname, $admin_lastname, $admin_email, $hashed_password);
            
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
    <title>Admin Sign-Up | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminSignup.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <i class="fas fa-user-shield fa-3x mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">Admin Sign-Up</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please fill in the form below to create an admin account.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="adminSignup.php" method="POST" novalidate>
                            <div class="mb-4">
                                <label for="admin_firstname" class="form-label" style="letter-spacing: 2px;">First Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                    <input type="text" name="admin_firstname" id="admin_firstname" class="form-control" required>
                                    <div class="invalid-feedback">Please enter your first name.</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="admin_lastname" class="form-label" style="letter-spacing: 2px;">Last Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-fill" style="color: black;"></i></span>
                                    <input type="text" name="admin_lastname" id="admin_lastname" class="form-control" required>
                                    <div class="invalid-feedback">Please enter your last name.</div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="admin_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                    <input type="email" name="admin_email" id="admin_email" class="form-control" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label for="admin_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                    <input type="password" name="admin_password" id="admin_password" class="form-control" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\\|,.<>\/?])(?=.*[0-9]).{8,}$">
                                    <span class="input-group-text">
                                        <i class="bi bi-eye-fill password-toggle" style="cursor: pointer; color: black;"></i>
                                    </span>
                                    <div class="invalid-feedback">Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.</div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Sign Up</button>
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
                        <p class="fs-5 mb-4">Your admin account has been created successfully!</p>
                        <a href="adminLogin.php" class="btn btn-success px-5 py-2 rounded-pill">
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
        document.querySelectorAll('input').forEach(input => {
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
            
            document.querySelectorAll('input').forEach(input => {
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
        const passwordInput = document.querySelector('input[name="admin_password"]');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('bi-eye-fill');
                this.classList.toggle('bi-eye-slash-fill');
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
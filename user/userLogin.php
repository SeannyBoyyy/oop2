<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = trim($_POST['user_email']);
    $user_password = trim($_POST['user_password']);
    
    if (empty($user_email) || empty($user_password)) {
        $loginError = "All fields are required!";
    } else {
        $sql = "SELECT user_id, user_firstname, user_password, status FROM tbl_user WHERE user_email = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $user_id, $user_firstname, $db_password, $status);
            mysqli_stmt_fetch($stmt);

            if ($status === 'suspended') {
                $loginError = "Your account is suspended. Please contact support.";
            } else if (password_verify($user_password, $db_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_firstname;
                $_SESSION['user_type'] = 'user';
                
                $loginSuccess = true;
                $successName = $user_firstname;
            } else {
                $loginError = "Incorrect password!";
            }
        } else {
            $loginError = "No user account found!";
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
    <title>User Login | Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminLogin.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle mb-3" style="color: #ffc107; font-size: 5rem;"></i>
                    <h4 class="fs-1 fw-bold">User Login</h4>
                    <p class="text-muted" style="letter-spacing: 1px;">Please enter your email and password to login.</p>
                </div>
                <div class="card shadow" style="padding: 20px;">
                    <div class="card-body">
                        <form action="userLogin.php" method="POST" novalidate>
                            <div class="mb-4">
                                <label for="user_email" class="form-label" style="letter-spacing: 2px;">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope-fill" style="color: black;"></i></span>
                                    <input type="email" class="form-control" id="user_email" name="user_email" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>
                            <div class="mb-5">
                                <label for="user_password" class="form-label" style="letter-spacing: 2px;">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill" style="color: black;"></i></span>
                                    <input type="password" class="form-control" id="user_password" name="user_password" required>
                                    <span class="input-group-text">
                                        <i class="bi bi-eye-fill password-toggle" style="cursor: pointer; color: black;"></i>
                                    </span>
                                    <div class="invalid-feedback">Please enter your password.</div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-black" style="letter-spacing: 2px; padding: 10px 20px;">Log In</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="userSignup.php" style="color: blue;">Don't have an account? Sign up here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Error Modal -->
    <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div class="mt-3 text-center">
                        <i class="bi bi-exclamation-circle-fill text-danger" style="font-size: 3.5rem;"></i>
                        <h4 class="mt-3 mb-0 fw-bold">Login Failed</h4>
                    </div>
                    
                    <div class="p-4 text-center">
                        <p id="loginErrorMessage" class="fs-5 mb-4">Invalid email or password. Please try again.</p>
                        <button type="button" class="btn btn-danger px-5 py-2 rounded-pill" data-bs-dismiss="modal">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Success Modal -->
    <div class="modal fade" id="loginSuccessModal" tabindex="-1" aria-labelledby="loginSuccessModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-0">
                    <div class="mt-3 text-center">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                        <h4 class="mt-3 mb-0 fw-bold">Login Successful</h4>
                    </div>
                    
                    <div class="p-4 text-center">
                        <p class="fs-5 mb-4">Welcome back, <span id="userNameSpan"></span>! You've successfully logged in.</p>
                        <a href="user_homepage.php" class="btn btn-success px-5 py-2 rounded-pill">
                            <i class="bi bi-arrow-right-circle-fill me-2"></i>Continue to Homepage
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
        const passwordInput = document.querySelector('input[name="user_password"]');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                this.classList.toggle('bi-eye-fill');
                this.classList.toggle('bi-eye-slash-fill');
            });
        }

        <?php if(isset($loginError)): ?>
            const loginErrorMessage = document.getElementById('loginErrorMessage');
            loginErrorMessage.textContent = "<?php echo $loginError; ?>";
            
            const loginErrorModal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
            loginErrorModal.show();
        <?php endif; ?>
        <?php if(isset($loginSuccess) && $loginSuccess): ?>
            const userNameSpan = document.getElementById('userNameSpan');
            userNameSpan.textContent = "<?php echo htmlspecialchars($successName); ?>";
            
            const loginSuccessModal = new bootstrap.Modal(document.getElementById('loginSuccessModal'));
            loginSuccessModal.show();
            
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        <?php endif; ?>
    });
    </script>
</body>
</html>
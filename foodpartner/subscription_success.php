<?php
session_start();
include '../config.php'; // Database connection

if (!isset($_GET['partner_email'])) {
    die("Invalid request.");
}

$partner_email = $_GET['partner_email'];
$subscription_status = "active";
$subscription_expiry = date('Y-m-d H:i:s', strtotime('+1 year')); // Set expiry to 1 year from now

// Get business name if available
$query = "SELECT business_name FROM tbl_foodpartner WHERE partner_email = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $partner_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$business_name = $row ? $row['business_name'] : 'Your Food Business';
$stmt->close();

// Format the expiry date
$formatted_expiry = date('F j, Y', strtotime($subscription_expiry));

// ✅ Update the subscription status and expiry date in the database
$sql = "UPDATE tbl_foodpartner 
        SET status = ?, verification_status = 'verified', subscription_status = ?, subscription_expiry = ?, created_at = NOW() 
        WHERE partner_email = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("ssss", $subscription_status, $subscription_status, $subscription_expiry, $partner_email);
$stmt->execute();
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Success - <?php echo htmlspecialchars($business_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f1c40f;
            --secondary: #f39c12;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #2ecc71;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark);
            padding: 0;
            margin: 0;
        }
        
        .success-banner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: black;
            padding: 2rem 0;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .success-banner::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background-image: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            opacity: 0.3;
            z-index: 1;
        }
        
        .success-banner h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
        }
        
        .success-banner p {
            font-weight: 300;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .success-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .ticket-container {
            max-width: 850px;
            margin: 0 auto 4rem;
            position: relative;
        }
        
        .ticket {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            position: relative;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            padding: 1.5rem;
            position: relative;
            text-align: center;
        }
        
        .ticket-id {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .ticket-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .ticket-subtitle {
            font-weight: 300;
            opacity: 0.9;
        }
        
        .ticket-body {
            padding: 2rem;
        }
        
        .info-row {
            margin-bottom: 1rem;
        }
        
        .info-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .divider {
            height: 1px;
            background-image: linear-gradient(to right, transparent, rgba(0, 0, 0, 0.1), transparent);
            margin: 2rem 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success);
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .qr-section {
            background-color: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-top: 2rem;
        }
        
        .qr-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .actions {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .btn-custom {
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--dark);
        }
        
        .btn-custom:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3);
        }
        
        .ticket-footer {
            background-color: var(--light);
            padding: 1.5rem;
            text-align: center;
        }
        
        .info-section {
            border-radius: 12px;
            background-color: var(--light);
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .info-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 0.5rem;
            vertical-align: middle;
        }
        
        .tear-line {
            position: relative;
            height: 30px;
            margin: 0 2rem;
        }
        
        .tear-line::before {
            content: "";
            position: absolute;
            top: 50%;
            left: -10px;
            right: -10px;
            border-top: 2px dashed rgba(0, 0, 0, 0.1);
        }
        
        .tear-circle-left, .tear-circle-right {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background-color: #f8f9fa;
            border-radius: 50%;
        }
        
        .tear-circle-left {
            left: -20px;
        }
        
        .tear-circle-right {
            right: -20px;
        }
        
        .benefit-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(241, 196, 15, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: var(--primary);
            top: -10px;
            z-index: 99999;
            animation: confetti-fall 5s linear forwards;
        }
    </style>
</head>
<body>
    <!-- Success Banner -->
    <div class="success-banner">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1>Subscription Successful!</h1>
        <p>Your food business account has been activated and is ready to use.</p>
    </div>

    <div class="ticket-container">
        <div class="ticket">
            <!-- Ticket Header -->
            <div class="ticket-header">
                <div class="ticket-id">FOOD PARTNER</div>
                <h2 class="ticket-title">Subscription Confirmation</h2>
                <p class="ticket-subtitle">Thank you for joining our food partner program</p>
            </div>
            
            <!-- Tear Line -->
            <div class="tear-line">
                <div class="tear-circle-left"></div>
                <div class="tear-circle-right"></div>
            </div>
            
            <!-- Ticket Body -->
            <div class="ticket-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label"><i class="bi bi-shop me-2"></i> Business Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($business_name); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label"><i class="bi bi-envelope me-2"></i> Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($partner_email); ?></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label"><i class="bi bi-calendar-check me-2"></i> Expires On</span>
                            <span class="info-value"><?php echo $formatted_expiry; ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label"><i class="bi bi-check-circle me-2"></i> Status</span>
                            <span class="info-value">
                                <span class="status-badge">
                                    <i class="bi bi-circle-fill me-2" style="font-size: 0.6rem;"></i>Active
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="divider"></div>
                

                
                <div class="info-section mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle info-icon"></i>
                        <h5 class="mb-0">Important Information</h5>
                    </div>
                    <p>Your subscription is valid for one year from today.</p>
                    
                </div>
            </div>
            
            <!-- Ticket Footer -->
            <div class="ticket-footer">

                
                <a href="foodPartnerLogin.php" class="btn btn-custom">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
    // Generate confetti effect for celebration
    function createConfetti() {
        const colors = ['#f1c40f', '#e74c3c', '#2ecc71', '#3498db', '#9b59b6'];
        const confettiCount = 100;
        
        for (let i = 0; i < confettiCount; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            
            // Random position, color, and delay
            const left = Math.random() * 100;
            const backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            const delay = Math.random() * 3;
            const duration = 3 + Math.random() * 2;
            
            confetti.style.left = `${left}vw`;
            confetti.style.backgroundColor = backgroundColor;
            confetti.style.animationDelay = `${delay}s`;
            confetti.style.animationDuration = `${duration}s`;
            
            document.body.appendChild(confetti);
            
            // Remove confetti after animation ends
            setTimeout(() => {
                confetti.remove();
            }, (delay + duration) * 1000);
        }
    }
    
    // Run confetti animation
    window.addEventListener('load', createConfetti);
    </script>
</body>
</html>
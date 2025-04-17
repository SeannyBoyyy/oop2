<?php
session_start();
include '../config.php';

$user_id = $_GET['user_id'];
$showtime_id = $_GET['showtime_id'];
$seats = json_decode($_GET['seats'], true);


if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (empty($user_id) || empty($showtime_id) || empty($seats)) {
    die('Error: Missing payment details.');
}


$query = "SELECT user_firstname, user_lastname, user_email FROM tbl_user WHERE user_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_firstname = $user['user_firstname'];
$user_lastname = $user['user_lastname'];
$user_email = $user['user_email'];


$query = "SELECT m.title, m.poster_url, s.show_date, s.show_time, s.price, c.name as cinema_name, s.screen_number 
          FROM tbl_showtimes s
          JOIN tbl_movies m ON s.movie_id = m.movie_id
          JOIN tbl_cinema c ON s.cinema_id = c.cinema_id
          WHERE s.showtime_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
$showtime = $result->fetch_assoc();
$movie_title = $showtime['title'];
$poster_url = $showtime['poster_url'];
$cinema_name = $showtime['cinema_name'];
$screen_number = $showtime['screen_number'];
$show_date = date("l, F j, Y", strtotime($showtime['show_date'])); // Format: Monday, January 1, 2023
$show_time = date("h:i A", strtotime($showtime['show_time']));
$seat_price = $showtime['price'];

// Calculate total price
$total_price = $seat_price * count($seats);

// Reserve seats before confirming the payment
$seat_list = [];
foreach ($seats as $seat) {
    $row_label = $seat['row_label'];
    $seat_number = $seat['seat_number'];
    $seat_list[] = $row_label . $seat_number;

    // Reserve the seat first
    $query = "UPDATE tbl_seats SET status = 'reserved', user_id = ? 
              WHERE showtime_id = ? AND row_label = ? AND seat_number = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("iisi", $user_id, $showtime_id, $row_label, $seat_number);
    $stmt->execute();
}

// Insert transaction into `tbl_transactions`
$seat_numbers = implode(", ", $seat_list);
$transaction_date = date("Y-m-d H:i:s");
$insert_transaction = "INSERT INTO tbl_transactions (user_id, showtime_id, seats, total_price, payment_status, transaction_date)
                       VALUES (?, ?, ?, ?, 'paid', NOW())";
$stmt = $con->prepare($insert_transaction);
$stmt->bind_param("iisd", $user_id, $showtime_id, $seat_numbers, $total_price);
$stmt->execute();
$transaction_id = $con->insert_id;

// Generate QR code data
$qr_data = "TXN:{$transaction_id}|MOVIE:{$movie_title}|DATE:{$showtime['show_date']}|TIME:{$showtime['show_time']}|SEATS:{$seat_numbers}|USER:{$user_id}";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - <?= htmlspecialchars($movie_title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
            color: white;
            padding: 1.5rem;
            position: relative;
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
        
        .movie-details {
            display: flex;
            margin-bottom: 2rem;
        }
        
        .movie-poster {
            width: 150px;
            height: 225px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-right: 2rem;
        }
        
        .movie-info {
            flex: 1;
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
        
        .seat-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--light);
            border-radius: 30px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .pricing-table {
            width: 100%;
            margin-top: 1.5rem;
        }
        
        .pricing-table td, .pricing-table th {
            padding: 0.75rem;
        }
        
        .pricing-table tr:last-child {
            font-weight: 700;
            font-size: 1.2rem;
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
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
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
        
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white;
            }
            .ticket-container {
                max-width: 100%;
                margin: 0;
            }
            .ticket {
                box-shadow: none;
                margin-bottom: 0;
            }
            .success-banner {
                padding: 1rem 0;
            }
        }
        
        /* Animation for confetti effect */
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
    <div class="success-banner no-print">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1>Payment Successful!</h1>
        <p>Your movie tickets have been booked successfully. We've sent a confirmation to your email.</p>
    </div>

    <div class="container ticket-container">
        <!-- Movie Ticket -->
        <div class="ticket">
            <!-- Ticket Header -->
            <div class="ticket-header">
                <div class="ticket-id text-black">TRANSACTION #<?= $transaction_id ?></div>
                <h2 class="ticket-title text-black">Movie Ticket</h2>
                <p class="ticket-subtitle text-black">Thank you for your purchase!</p>
            </div>
            
            <!-- Tear Line -->
            <div class="tear-line">
                <div class="tear-circle-left"></div>
                <div class="tear-circle-right"></div>
            </div>
            
            <!-- Ticket Body -->
            <div class="ticket-body">
                <div class="movie-details">
                    <img src="../cinema/<?= htmlspecialchars($poster_url) ?>" alt="<?= htmlspecialchars($movie_title) ?>" class="movie-poster">
                    
                    <div class="movie-info">
                        <h3><?= htmlspecialchars($movie_title) ?></h3>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <span class="info-label"><i class="bi bi-building"></i> Cinema</span>
                                    <span class="info-value"><?= htmlspecialchars($cinema_name) ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label"><i class="bi bi-display"></i> Screen</span>
                                    <span class="info-value"><?= htmlspecialchars($screen_number) ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-row">
                                    <span class="info-label"><i class="bi bi-calendar-event"></i> Date</span>
                                    <span class="info-value"><?= htmlspecialchars($show_date) ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label"><i class="bi bi-clock"></i> Time</span>
                                    <span class="info-value"><?= htmlspecialchars($show_time) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <!-- Seat Details -->
                <div class="row">
                    <div class="col-12">
                        <h4><i class="bi bi-chair"></i> Your Seats</h4>
                        <div class="mt-3">
                            <?php foreach($seat_list as $seat): ?>
                                <span class="seat-badge"><?= htmlspecialchars($seat) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <!-- Pricing Details -->
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="bi bi-credit-card"></i> Payment Details</h4>
                        <table class="pricing-table">
                            <tr>
                                <td>Price per seat</td>
                                <td class="text-end">₱<?= number_format($seat_price, 2) ?></td>
                            </tr>
                            <tr>
                                <td>Number of seats</td>
                                <td class="text-end"><?= count($seats) ?></td>
                            </tr>
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-end">₱<?= number_format($total_price, 2) ?></td>
                            </tr>
                            <tr>
                                <td>TOTAL</td>
                                <td class="text-end">₱<?= number_format($total_price, 2) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- QR Code Section -->
                        <div class="qr-section">
                            <div class="qr-title"><i class="bi bi-qr-code"></i> Scan to verify your ticket</div>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($qr_data) ?>" 
                                alt="Ticket QR Code" class="img-fluid" style="max-width: 150px;">
                            <div class="mt-2 text-muted">Transaction #<?= $transaction_id ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Info -->
                <div class="info-section mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-person"></i> Customer</span>
                                <span class="info-value"><?= htmlspecialchars($user_firstname . ' ' . $user_lastname) ?></span>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-envelope"></i> Email</span>
                                <span class="info-value"><?= htmlspecialchars($user_email) ?></span>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-2">
                            <div class="info-row">
                                <span class="info-label"><i class="bi bi-calendar-check"></i> Transaction Date</span>
                                <span class="info-value"><?= htmlspecialchars($transaction_date) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ticket Footer -->
            <div class="ticket-footer">
                <div class="mb-3">
                    <i class="bi bi-film info-icon"></i>
                    <span class="fw-bold">Enjoy your movie! Please arrive 15 minutes before showtime.</span>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="actions no-print">
            <button class="btn btn-primary btn-custom" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Ticket
            </button>
            
            <a href="user_tickets.php" class="btn btn-secondary btn-custom">
                <i class="bi bi-ticket-perforated"></i> View All Tickets
            </a>
            
            <a href="order_food.php?showtime_id=<?= $showtime_id ?>" class="btn btn-success btn-custom">
                <i class="bi bi-cup-hot"></i> Order Food & Drinks
            </a>
        </div>
        
        <!-- Help Info -->

    </div>

    <script>
    // Generate confetti effect for celebration
    function createConfetti() {
        const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6'];
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

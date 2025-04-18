<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch the order details
    $orderQuery = "SELECT o.*, p.product_name, p.price, p.image_url, s.name, m.title AS movie_name, m.poster_url, o.seats, t.screen_number
                   FROM tbl_orders o
                   JOIN tbl_foodproducts p ON o.product_id = p.product_id
                   JOIN tbl_showtimes t ON o.showtime_id = t.showtime_id
                   JOIN tbl_cinema s ON t.cinema_id = s.cinema_id
                   JOIN tbl_movies m ON t.movie_id = m.movie_id
                   WHERE o.order_id = ?";
    $stmtOrder = $con->prepare($orderQuery);
    $stmtOrder->bind_param("i", $order_id);
    $stmtOrder->execute();
    $orderResult = $stmtOrder->get_result();

    if ($orderResult->num_rows > 0) {
        $order = $orderResult->fetch_assoc();

        // If seats are stored as a plain string (comma-separated), convert it into an array
        $seats = explode(',', $order['seats']); // Split by commas to create an array

        // Display order details with Bootstrap
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - <?php echo htmlspecialchars($order['movie_name']); ?></title>
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
        
        .receipt-container {
            max-width: 850px;
            margin: 0 auto 4rem;
            position: relative;
        }
        
        .receipt {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            position: relative;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            padding: 1.5rem;
            position: relative;
            text-align: center;
        }
        
        .order-id {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
        }
        
        .receipt-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .receipt-subtitle {
            font-weight: 300;
            opacity: 0.9;
            color: var(--dark);
        }
        
        .receipt-body {
            padding: 2rem;
        }
        
        .movie-details {
            display: flex;
            margin-bottom: 2rem;
        }
        
        .movie-poster {
            width: 110px;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-right: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--light);
        }
        
        .movie-info {
            flex: 1;
        }
        
        .movie-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .info-row {
            margin-bottom: 0.6rem;
            font-size: 0.95rem;
        }
        
        .info-icon {
            color: var(--primary);
            margin-right: 0.5rem;
            width: 18px;
            text-align: center;
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
        
        .product-card {
            background-color: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .product-image {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            flex-shrink: 0;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.3rem;
            color: var(--dark);
        }
        
        .product-details {
            font-size: 0.9rem;
            color: rgba(0, 0, 0, 0.6);
        }
        
        .product-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.3rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-paid {
            background-color: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }
        
        .status-pending {
            background-color: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
        }
        
        .status-processing {
            background-color: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }
        
        .pricing-table {
            width: 100%;
            margin-top: 1rem;
        }
        
        .pricing-table td {
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .pricing-table tr:last-child td {
            border-bottom: none;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .pricing-table tr:last-child td:first-child {
            color: var(--dark);
        }
        
        .pricing-table tr:last-child td:last-child {
            color: var(--primary);
        }
        
        .qr-section {
            text-align: center;
            background-color: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .qr-code {
            width: 130px;
            height: 130px;
            margin: 0 auto 1rem;
        }
        
        .qr-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .tear-line {
            position: relative;
            height: 30px;
            margin: 0;
            overflow: hidden;
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
            width: 40px;
            height: 40px;
            background-color: #f8f9fa;
            border-radius: 50%;
        }
        
        .tear-circle-left {
            left: -20px;
        }
        
        .tear-circle-right {
            right: -20px;
        }
        
        .delivery-section {
            background-color: var(--light);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .delivery-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .delivery-info {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .delivery-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(241, 196, 15, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .delivery-text {
            flex: 1;
        }
        
        .delivery-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(0, 0, 0, 0.6);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }
        
        .receipt-footer {
            background-color: var(--light);
            padding: 2rem;
            text-align: center;
        }
        
        .btn-custom {
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 0.5rem;
            margin-top: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: var(--dark);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(243, 156, 18, 0.3);
        }
        
        .contact-info {
            margin: 1.5rem 0;
        }
        
        .contact-item {
            display: inline-block;
            margin: 0 1rem;
            color: rgba(0, 0, 0, 0.6);
        }
        
        .contact-item i {
            color: var(--primary);
            margin-right: 0.3rem;
        }
        
        .print-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: rgba(255, 255, 255, 0.3);
            border: none;
            color: var(--dark);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .print-btn:hover {
            background-color: rgba(255, 255, 255, 0.5);
            transform: scale(1.1);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(241, 196, 15, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.8rem;
            color: var(--primary);
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .section-title-text {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        @media print {
            body {
                background-color: white;
            }
            
            .receipt-container {
                max-width: 100%;
                margin: 0;
            }
            
            .receipt {
                box-shadow: none;
            }
            
            .success-banner, 
            .print-btn, 
            .btn-custom,
            .confetti {
                display: none !important;
            }
            
            .tear-circle-left, 
            .tear-circle-right {
                display: none;
            }
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
        <i class="bi bi-check-circle success-icon"></i>
        <h1>Order Confirmed!</h1>
        <p>Your food order has been successfully placed and will be delivered to your seat.</p>
    </div>

    <div class="receipt-container">

        <!-- Receipt -->
        <div class="receipt">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div class="order-id">ORDER #<?php echo $order['order_id']; ?></div>
                <div class="success-icon" style="font-size: 3rem; color: var(--success);">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2 class="receipt-title">Order Receipt</h2>
                <p class="receipt-subtitle">Your food order has been confirmed</p>
            </div>
            
            <!-- Receipt Body -->
            <div class="receipt-body">
                <!-- Movie Details Section -->
                <div class="section-title">
                    <div class="section-title-icon">
                        <i class="bi bi-film"></i>
                    </div>
                    <div class="section-title-text">Movie Details</div>
                </div>
                
                <div class="movie-details">
                    <?php 
                    if (!empty($order['poster_url'])) {
                        echo '<img src="../cinema/' . htmlspecialchars($order['poster_url']) . '" alt="' . htmlspecialchars($order['movie_name']) . '" class="movie-poster">';
                    } else {
                        echo '<div class="movie-poster">
                                <i class="bi bi-film" style="font-size: 2rem; color: #dee2e6;"></i>
                              </div>';
                    }
                    ?>
                    
                    <div class="movie-info">
                        <div class="movie-title"><?php echo htmlspecialchars($order['movie_name']); ?></div>
                        
                        <div class="info-row">
                            <i class="bi bi-building info-icon"></i>
                            <span><?php echo htmlspecialchars($order['name']); ?></span>
                        </div>
                        
                        <div class="info-row">
                            <i class="bi bi-display info-icon"></i>
                            <span>Screen <?php echo htmlspecialchars($order['screen_number']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tear Line -->
                <div class="tear-line">
                    <div class="tear-circle-left"></div>
                    <div class="tear-circle-right"></div>
                </div>
                
                <!-- Order Details Section -->
                <div class="section-title mt-4">
                    <div class="section-title-icon">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div class="section-title-text">Order Details</div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($order['image_url'])): ?>
                            <img src="../foodpartner/uploads/<?= htmlspecialchars($order['image_url']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-cup-hot" style="font-size: 1.8rem; color: var(--primary);"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                        <div class="product-details">
                            <span class="status-badge <?php echo strtolower($order['status']) === 'paid' ? 'status-paid' : (strtolower($order['status']) === 'pending' ? 'status-pending' : 'status-processing'); ?>">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i>
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            <span class="ms-3">Quantity: <?php echo $order['quantity']; ?></span>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        ₱<?php echo number_format($order['total_price'], 2); ?>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="qr-section">
                            <div class="qr-title"><i class="bi bi-qr-code me-2"></i>Scan for Verification</div>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=ORDER-<?php echo $order['order_id']; ?>" 
                                 class="qr-code" alt="Order QR Code">
                            <div class="mt-2 text-muted">Order #<?php echo $order['order_id']; ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="bi bi-receipt me-2" style="color: var(--primary);"></i>Payment Summary</h5>
                        <table class="pricing-table">
                            <tr>
                                <td>Unit Price</td>
                                <td class="text-end">₱<?php echo number_format($order['price'], 2); ?></td>
                            </tr>
                            <tr>
                                <td>Quantity</td>
                                <td class="text-end"><?php echo $order['quantity']; ?></td>
                            </tr>
                            <tr>
                                <td>Total Amount</td>
                                <td class="text-end">₱<?php echo number_format($order['total_price'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Tear Line -->
                <div class="tear-line mt-5">
                    <div class="tear-circle-left"></div>
                    <div class="tear-circle-right"></div>
                </div>
                
                <!-- Delivery Information Section -->
                <div class="section-title mt-4">
                    <div class="section-title-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div class="section-title-text">Delivery Information</div>
                </div>
                
                <div class="delivery-section">
                    <div class="delivery-info">
                        <div class="delivery-icon">
                            <i class="bi bi-chair"></i>
                        </div>
                        <div class="delivery-text">
                            <div class="delivery-label">Seats for Delivery</div>
                            <div class="mt-2">
                                <?php 
                                if (!empty($seats)) {
                                    foreach ($seats as $seat) {
                                        echo '<span class="seat-badge"><i class="bi bi-chair me-2"></i>' . htmlspecialchars(trim($seat)) . '</span>';
                                    }
                                } else {
                                    echo '<div class="text-muted">No seats selected</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    

                </div>
            </div>
            
            <!-- Receipt Footer -->
            <div class="receipt-footer">
      
                
                <a href="user_tickets.php" class="btn btn-primary btn-custom">
                    <i class="bi bi-house-door me-2"></i>Back to Foods
                </a>
                
                <button onclick="window.print()" class="btn btn-outline-secondary btn-custom">
                    <i class="bi bi-printer me-2"></i>Print Receipt
                </button>
                
                
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
        <?php
    } else {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found.</div></div>";
    }
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Error: Order ID missing.</div></div>";
}
?>

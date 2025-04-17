<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); 
$user_id = $_SESSION['user_id'];

// Fetch user ticket transactions
$query = "SELECT t.transaction_id, t.seats, t.total_price, t.payment_status, t.transaction_date,
                 m.title, m.duration, s.show_date, s.show_time, s.showtime_id
          FROM tbl_transactions t
          JOIN tbl_showtimes s ON t.showtime_id = s.showtime_id
          JOIN tbl_movies m ON s.movie_id = m.movie_id
          WHERE t.user_id = ?
          ORDER BY t.transaction_date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <style>
        body {
            padding-top: 76px; /* Adjust based on your navbar height */
        }
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
        
        /* Dropdown styling fixes */
        .dropdown-menu {
            background-color: white;
            border: 1px solid rgba(0,0,0,0.15);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.175);
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div>
    <!-- Include navbar -->
    <?php include '../include/userNav.php'; ?>    

    <div class="container my-4">
        <h2 class="mb-4">My Purchased Tickets</h2>

        <?php if ($result->num_rows > 0) { ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Movie</th>
                            <th>Show Date</th>
                            <th>Time</th>
                            <th>Seats</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Transaction Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= (new DateTime($row['show_date']))->format('F j, Y') ?></td>
                                <td><?= (new DateTime($row['show_time']))->format('h:i A') ?></td>
                                <td><?= htmlspecialchars($row['seats']) ?></td>
                                <td>₱<?= number_format($row['total_price'], 2) ?></td>
                                <td class="<?= $row['payment_status'] == 'paid' ? 'status-paid' : ($row['payment_status'] == 'pending' ? 'status-pending' : 'status-failed') ?>">
                                    <?= htmlspecialchars($row['payment_status']) ?>
                                </td>
                                <td><?= (new DateTime($row['transaction_date']))->format('F j, Y h:i A') ?></td>
                                <td>
                                    <?php
                                        $timezone = new DateTimeZone('Asia/Manila');
                                        $now = new DateTime(null, $timezone); 
                                        $showStart = new DateTime($row['show_date'] . ' ' . $row['show_time'], $timezone); 
                                        $showEnd = clone $showStart;
                                        $durationMinutes = $row['duration'];
                                        $showEnd->modify("+$durationMinutes minutes");
                                        
                                        $isOngoing = ($now >= $showStart && $now <= $showEnd);
                                    ?>
                                    
                                    <div class="small mb-2">
                                        Show: <?= $showStart->format('M j, g:i A') ?> - <?= $showEnd->format('g:i A') ?>
                                    </div>

                                    <?php if ($row['payment_status'] === 'paid' && $isOngoing): ?>
                                        <a href="order_food.php?showtime_id=<?= $row['showtime_id'] ?>&seats=<?= $row['seats'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-cart-plus"></i> Order Food
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Ordering available only during the movie">
                                            <i class="bi bi-cart-x"></i> Not Available
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> No tickets purchased yet.
            </div>
        <?php } ?>
    </div>
</div>
    <style>
    .navbar-nav .nav-link {
        color: white !important;
    }
    
    .navbar-nav .nav-link.active {
        color: #ffd700 !important;
    }
    
    .navbar-nav .nav-link:hover {
        color: #ffd700 !important;
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    </style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js
"></script>
<body>
<html>
    
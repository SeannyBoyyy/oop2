<?php
session_start();
include '../config.php';

// Ensure user is logged in
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
mysqli_stmt_close($stmt); // ✅ Close the statement here

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <style>
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="cinema-sidebar">
        <div class="position-sticky">
            <div class="sidebar-header text-center">
                <i class="bi bi-person-circle display-1 mb-2"></i>
                <h3 class="fw-bold"><strong>User Dashboard</strong></h3>
            </div>
            <ul class="list-unstyled components">
                <li class="active" style="font-size: 1.2em;">
                    <a href="userDashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
                </li>
                <li style="font-size: 1.2em;">
                    <a href="user_tickets.php"><i class="bi bi-ticket-perforated"></i> My Tickets</a>
                </li>
                <li style="font-size: 1.2em;">
                    <a href="user_orders.php"><i class="bi bi-cart"></i> My Orders</a>
                </li>
                <li style="font-size: 1.2em;">
                    <a href="userLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn">
                    <i class="bi bi-list text-dark"></i>
                </button>   
                <div class="ms-auto">
                    <div class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                Welcome, <?php echo htmlspecialchars($firstname); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="userLogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container my-4">
            <h2 class="mb-3"> My Purchased Tickets</h2>

            <?php if ($result->num_rows > 0) { ?>
                <table class="table table-bordered">
                    <thead>
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
                                        // Set timezone to ensure uniform comparison
                                        $timezone = new DateTimeZone('Asia/Manila');
                                        $now = new DateTime(null, $timezone);  // current time
                                        $showStart = new DateTime($row['show_date'] . ' ' . $row['show_time'], $timezone); // movie start time
                                        $showEnd = clone $showStart;
                                        $durationMinutes = $row['duration'];
                                        $showEnd->modify("+$durationMinutes minutes");

                                        // Check if the movie is currently ongoing
                                        $isOngoing = ($now >= $showStart && $now <= $showEnd);

                                        echo "Show Start: " . $showStart->format('F j, Y h:i A') . "<br>";
                                        echo "Show End: " . $showEnd->format('F j, Y h:i A') . "<br>";
                                    ?>

                                    <?php if ($row['payment_status'] === 'paid' && $isOngoing): ?>
                                        <a href="order_food.php?showtime_id=<?= $row['showtime_id'] ?>&seats=<?= $row['seats'] ?>" class="btn btn-success btn-sm">
                                            Order Food
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Ordering available only during the movie.">Not Available</button>
                                    <?php endif; ?>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p class="text-muted">No tickets purchased yet.</p>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('content').classList.toggle('active');
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

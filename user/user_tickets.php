<?php
session_start();
include '../config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

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
    <style>
        .status-paid { color: green; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
        .status-failed { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="container my-4">
    <h2>🎟 My Purchased Tickets</h2>
    <a href="userDashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

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
                    <th>Action</th> <!-- Added Action Column -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= (new DateTime($row['show_date']))->format('F j, Y') ?></td>  <!-- For Show Date: Month Day, Year -->
                        <td><?= (new DateTime($row['show_time']))->format('h:i A') ?></td>     <!-- For Show Time: 12-hour format with AM/PM -->
                        <td><?= htmlspecialchars($row['seats']) ?></td>
                        <td>₱<?= number_format($row['total_price'], 2) ?></td>
                        <td class="<?=
                            $row['payment_status'] == 'paid' ? 'status-paid' :
                            ($row['payment_status'] == 'pending' ? 'status-pending' : 'status-failed')
                        ?>">
                            <?= htmlspecialchars($row['payment_status']) ?>
                        </td>
                        <td><?= (new DateTime($row['transaction_date']))->format('F j, Y h:i A') ?></td> <!-- For Transaction Date: Month Day, Year 12-hour format with AM/PM -->
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

                            echo "Show Start: " . $showStart->format('F j, Y h:i A') . "<br>";  // Month Day, Year 12-hour format with AM/PM
                            echo "Show End: " . $showEnd->format('F j, Y h:i A') . "<br>";      // Month Day, Year 12-hour format with AM/PM

                        ?>

                        <?php if ($row['payment_status'] === 'paid' && $isOngoing): ?>
                            <a href="order_food.php?showtime_id=<?= $row['showtime_id'] ?>&seats=<?= $row['seats'] ?>" class="btn btn-success btn-sm">
                                🍔 Order Food
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled title="Ordering available only during the movie.">⏳ Not Available</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

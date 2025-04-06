<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

// Get showtime_id from the URL
$selectedShowtimeId = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : 0;

// Fetch full details of the selected showtime, movie, and cinema
$selectedShowtime = null;
if ($selectedShowtimeId > 0) {
    $selectedQuery = "SELECT 
                        s.showtime_id, s.screen_number, s.total_seats, s.price, s.show_date, s.show_time,
                        m.title, m.genre, m.rating, m.duration, m.poster_url, m.release_date,
                        c.name AS cinema_name, c.location AS cinema_location, c.status AS cinema_status, c.cinema_image
                      FROM tbl_showtimes s
                      JOIN tbl_movies m ON s.movie_id = m.movie_id
                      JOIN tbl_cinema c ON s.cinema_id = c.cinema_id
                      WHERE s.showtime_id = ?";
    $stmt = $con->prepare($selectedQuery);
    $stmt->bind_param("i", $selectedShowtimeId);
    $stmt->execute();
    $selectedResult = $stmt->get_result();
    $selectedShowtime = $selectedResult->fetch_assoc();
}

// Redirect if no valid showtime is found
if (!$selectedShowtime) {
    echo "<script>alert('Invalid Showtime ID!'); window.location.href='index.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Seat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .movie-poster {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        .seat {
            border: 1px solid #000;
            width: 40px;
            height: 40px;
            text-align: center;
            cursor: pointer;
            background-color: #ddd;
            display: inline-block;
            margin: 5px;
        }
        .seat:hover {
            background-color: #ccc;
        }
        .bg-success {
            background-color: green !important;
            color: white;
        }
    </style>
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-3">🎬 Movie & Showtime Details</h3>

    <!-- Movie & Cinema Info -->
    <div class="card p-3">
        <div class="row">
            <!-- Movie Poster -->
            <div class="col-md-4">
                <img src="../cinema/<?= htmlspecialchars($selectedShowtime['poster_url']) ?>" class="movie-poster" alt="Movie Poster">
            </div>
            <!-- Movie & Cinema Details -->
            <div class="col-md-8">
                <h4><?= htmlspecialchars($selectedShowtime['title']) ?></h4>
                <p><strong> Genre:</strong> <?= htmlspecialchars($selectedShowtime['genre']) ?></p>
                <p><strong> Rating:</strong> <?= htmlspecialchars($selectedShowtime['rating']) ?></p>
                <p><strong> Duration:</strong> <?= htmlspecialchars($selectedShowtime['duration']) ?> minutes</p>
                <p><strong> Release Date:</strong> <?= htmlspecialchars($selectedShowtime['release_date']) ?></p>
                <hr>
                <h5>Cinema: <?= htmlspecialchars($selectedShowtime['cinema_name']) ?></h5>
                <p><strong> Location:</strong> <?= htmlspecialchars($selectedShowtime['cinema_location']) ?></p>
                <p><strong> Status:</strong> <?= $selectedShowtime['cinema_status'] == 'open' ? '🟢 Open' : '🔴 Closed' ?></p>
            </div>
        </div>
    </div>

    <!-- Showtime Details -->
    <div class="card p-3 mt-3">
        <h4>Showtime Details</h4>
        <p><strong> Movie:</strong> <?= htmlspecialchars($selectedShowtime['title']) ?></p>
        <p><strong> Date:</strong> <?= htmlspecialchars($selectedShowtime['show_date']) ?></p>
        <p><strong> Time:</strong> <?= date("h:i A", strtotime($selectedShowtime['show_time'])) ?></p>
        <p><strong> Screen Number:</strong> <?= htmlspecialchars($selectedShowtime['screen_number']) ?></p>
        <p><strong> Total Seats:</strong> <?= htmlspecialchars($selectedShowtime['total_seats']) ?></p>
        <p><strong> Ticket Price:</strong> ₱<?= number_format($selectedShowtime['price'], 2) ?></p>

        <?php
        // Set default timezone to match the server's timezone or the timezone of your application
        date_default_timezone_set('Asia/Manila'); // Adjust if needed

        // Get the current date and time
        $now = new DateTime();

        // Calculate the showtime end date
        $showStart = new DateTime($selectedShowtime['show_date'] . ' ' . $selectedShowtime['show_time']);
        $showEnd = clone $showStart;
        $showEnd->modify('+' . $selectedShowtime['duration'] . ' minutes');  // Add duration to show start

        // Check if the movie is finished
        $isFinished = $now > $showEnd;
        ?>
        <!-- Display if the movie is finished -->
        <?php if ($isFinished): ?>
            <p class="text-danger">🎬 This movie has finished showing.</p>
        <?php else: ?>
            <p class="text-success">🎬 The movie is still showing.</p>
        <?php endif; ?>
    </div>

    <!-- Seat Selection -->
    <div id="seatSelection" class="mt-4">
        <h4>🎟 Select Your Seat</h4>
        <div id="seatLayout"></div>
        <!-- Display if the movie is finished -->
        <?php if ($isFinished): ?>
            <button class="btn btn-secondary btn-sm" disabled title="Available only during the or Before movie.">⏳ The movie is Finished</button>
        <?php else: ?>
            <button id="payNow" class="btn btn-primary mt-3" style="display:none;">Pay Now</button>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function () {
    let showtimeId = <?= $selectedShowtimeId ?>;
    
    function loadSeats() {
        $.ajax({
            url: "fetch_seats.php",
            method: "POST",
            data: { showtime_id: showtimeId },
            success: function (data) {
                $("#seatLayout").html(data);
            }
        });
    }

    loadSeats(); // Load seats automatically

    $(document).on("click", ".seat", function () {
        $(this).toggleClass("bg-success");
        $("#payNow").show();
    });

    $("#payNow").click(function () {
        let selectedSeats = [];

        $(".seat.bg-success").each(function () {
            selectedSeats.push({
                row_label: $(this).attr("data-row"),
                seat_number: $(this).attr("data-seat")
            });
        });

        if (selectedSeats.length === 0) {
            alert("Please select at least one seat.");
            return;
        }

        // Redirect to PayMongo Payment Page
        window.location.href = "paymongo_payment.php?showtime_id=" + showtimeId + "&seats=" + JSON.stringify(selectedSeats);
    });
});
</script>

</body>
</html>

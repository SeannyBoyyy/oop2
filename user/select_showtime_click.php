<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); // 

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
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
<body>
<?php include '../include/userNav.php'; ?>  

<div class="py-5" style="margin-top: -20px; background-color: #121212;">
    <!-- Hero Section with Cinematic Background -->
    <div class="hero-section text-light" 
         style="background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.9)), url('../cinema/<?= htmlspecialchars($selectedShowtime['poster_url']) ?>'); 
                background-size: cover; background-position: center; padding: 80px 0; margin-bottom: 40px; 
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);">
        <div class="container">
            <div class="mb-4 text-start">
                <button onclick="history.back()" class="btn btn-warning px-4 py-2 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back to Cinema
                </button>
            </div>
            <div class="row align-items-center">
                <div class="col-lg-5 col-md-12 text-center mb-4">
                    <img src="../cinema/<?= htmlspecialchars($selectedShowtime['poster_url']) ?>" 
                         alt="<?= htmlspecialchars($selectedShowtime['title']) ?>" 
                         class="img-fluid rounded-lg shadow-lg" 
                         style="max-height: 400px; object-fit: cover; border: 4px solid rgba(255,255,255,0.1); transform: rotate(-2deg);">
                </div>
                <div class="col-lg-7 col-md-12 mb-4 mb-lg-0 text-center text-lg-start ps-lg-5">
                    <div class="mb-3">
                        <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold">NOW SHOWING</span>
                    </div>
                    <h1 class="display-3 fw-bold mb-3" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                        <?= htmlspecialchars($selectedShowtime['title']) ?>
                    </h1>
                    <p class="lead mb-4 opacity-75 fs-4"><?= htmlspecialchars($selectedShowtime['genre']) ?></p>
                    
                    <div class="d-flex gap-3 mb-4 flex-wrap justify-content-center justify-content-lg-start">
                        <div class="badge bg-light text-dark p-3 px-4 fs-6 rounded-pill">
                            <i class="bi bi-star-fill text-warning me-2"></i><?= htmlspecialchars($selectedShowtime['rating']) ?>
                        </div>
                        <div class="badge bg-light text-dark p-3 px-4 fs-6 rounded-pill">
                            <i class="bi bi-clock me-2"></i><?= htmlspecialchars($selectedShowtime['duration']) ?> min
                        </div>
                        <div class="badge bg-light text-dark p-3 px-4 fs-6 rounded-pill">
                            <i class="bi bi-calendar-event me-2"></i><?= htmlspecialchars($selectedShowtime['release_date']) ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="badge <?= $selectedShowtime['cinema_status'] == 'open' ? 'bg-success' : 'bg-danger'; ?> p-3 px-4 fs-6 rounded-3">
                            <i class="bi bi-building me-2"></i><?= htmlspecialchars($selectedShowtime['cinema_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Showtime and Seat Selection -->
    <div class="container">
        <div class="row g-4">
            <!-- Showtime Details Card -->
            <div class="col-lg-4 col-md-5">
                <div class="card border-0 rounded-lg shadow-lg h-100" style="background: rgba(40, 40, 40, 0.9);">
                    <div class="card-header bg-dark text-white py-3 border-0">
                        <h4 class="mb-0"><i class="bi bi-info-circle-fill me-2"></i>Showtime Details</h4>
                    </div>
                    <div class="card-body text-white">
                        <div class="mb-3 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                            <p class="mb-1  small">MOVIE</p>
                            <p class="fs-5 fw-bold mb-0"><?= htmlspecialchars($selectedShowtime['title']) ?></p>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-center p-3 rounded flex-grow-1 me-2" style="background: rgba(0,0,0,0.2);">
                                <p class="mb-1small">DATE</p>
                                <p class="fs-6 fw-bold mb-0"><?= date("M d, Y", strtotime($selectedShowtime['show_date'])) ?></p>
                            </div>
                            <div class="text-center p-3 rounded flex-grow-1" style="background: rgba(0,0,0,0.2);">
                                <p class="mb-1 text-muted small">TIME</p>
                                <p class="fs-6 fw-bold mb-0"><?= date("h:i A", strtotime($selectedShowtime['show_time'])) ?></p>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-center p-3 rounded flex-grow-1 me-2" style="background: rgba(0,0,0,0.2);">
                                <p class="mb-1 small">SCREEN</p>
                                <p class="fs-6 fw-bold mb-0"><?= htmlspecialchars($selectedShowtime['screen_number']) ?></p>
                            </div>
                            <div class="text-center p-3 rounded flex-grow-1" style="background: rgba(0,0,0,0.2);">
                                <p class="mb-1 small">SEATS</p>
                                <p class="fs-6 fw-bold mb-0"><?= htmlspecialchars($selectedShowtime['total_seats']) ?></p>
                            </div>
                        </div>
                        
                        <div class="p-3 rounded mb-3" style="background: rgba(0,0,0,0.2);">
                            <p class="mb-1 small">TICKET PRICE</p>
                            <p class="fs-4 fw-bold mb-0 text-warning">₱<?= number_format($selectedShowtime['price'], 2) ?></p>
                        </div>

                        <?php
                        date_default_timezone_set('Asia/Manila'); 
                        $now = new DateTime();
                        $showStart = new DateTime($selectedShowtime['show_date'] . ' ' . $selectedShowtime['show_time']);
                        $showEnd = clone $showStart;
                        $showEnd->modify('+' . $selectedShowtime['duration'] . ' minutes');
                        $isFinished = $now > $showEnd;
                        ?>
                        
                        <div class="mt-3 p-3 rounded text-center <?= $isFinished ? 'bg-danger bg-opacity-25' : 'bg-success bg-opacity-25' ?>">
                            <?php if ($isFinished): ?>
                                <p class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>This movie has finished showing.</p>
                            <?php else: ?>
                                <p class="mb-0"><i class="bi bi-film me-2"></i>The movie is still showing.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Seat Selection Card -->
            <div class="col-lg-8 col-md-7">
                <div class="card border-0 rounded-lg shadow-lg h-100" style="background: rgba(40, 40, 40, 0.9);">
                    <div class="card-header bg-dark text-white py-3 border-0">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center">
                            <h4 class="mb-2 mb-sm-0"><i class="bi bi-ticket-perforated-fill me-2"></i>Select Your Seat</h4>
                            <div class="d-flex align-items-center">
                                <span class="me-3">
                                    <span class="badge p-2 rounded" style="background-color: #ddd;"></span> Available
                                </span>
                                <span>
                                    <span class="badge p-2 rounded bg-success"></span> Selected
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body text-white">
                        <div class="text-center mb-4 p-2 p-md-3 bg-dark bg-opacity-50 rounded">
                            <div class="py-2 px-3 px-md-5 bg-secondary bg-opacity-25 rounded-pill d-inline-block mb-4">
                                SCREEN
                            </div>
                            <div id="seatLayout" class="mt-4 overflow-auto" style="max-width: 100%"></div>
                        </div>
                        
                        <?php if ($isFinished): ?>
                            <div class="text-center mt-4">
                                <button class="btn btn-secondary btn-lg px-4 py-2 py-md-3 rounded-pill fw-bold" disabled>
                                    <i class="bi bi-clock-history me-2"></i>The movie has ended
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="text-center mt-4">
                                <button id="payNow" class="btn btn-warning btn-lg px-4 px-md-5 py-2 py-md-3 rounded-pill fw-bold shadow" style="display:none;">
                                    <i class="bi bi-credit-card-fill me-2"></i>Proceed to Payment
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styles for better seat appearance */
    .seat {
        border: 2px solid #555;
        width: 40px;
        height: 40px;
        text-align: center;
        cursor: pointer;
        background-color: #ddd;
        display: inline-block;
        margin: 5px;
        border-radius: 8px;
        font-weight: bold;
        line-height: 36px;
        transition: all 0.2s ease;
        color: #333;
    }
    .seat:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(255,215,0,0.7);
    }
    .seat.bg-success {
        background-color: #28a745 !important;
        color: white;
        border-color: #218838;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.7);
    }
    .disabled-seat {
        background-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>

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

    loadSeats(); 

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


        window.location.href = "paymongo_payment.php?showtime_id=" + showtimeId + "&seats=" + JSON.stringify(selectedSeats);
    });
});
</script>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

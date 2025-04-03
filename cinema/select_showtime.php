<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id']) && $_SESSION['cinema_id']) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$cinema_id = $_SESSION['cinema_id'];

// Fetch movies with showtimes
$showtimeQuery = "SELECT s.showtime_id, s.cinema_id, m.title, s.show_date, s.show_time 
                  FROM tbl_showtimes s
                  JOIN tbl_movies m ON s.movie_id = m.movie_id 
                  WHERE s.cinema_id = '$cinema_id' 
                  ORDER BY s.show_date, s.show_time";
$showtimeResult = mysqli_query($con, $showtimeQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Showtime & Seat</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/cinemaManage.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
</head>
<div class="wrapper">
    <nav id="sidebar" class="cinema-sidebar">
        <div class="position-sticky">
            <div class="sidebar-header text-center">
                <i class="bi bi-person-circle display-1 mb-2 text-black"></i>
                <h3 class="fw-bold"><strong><?= htmlspecialchars($_SESSION['cinema_name'] ?? 'Cinema'); ?></strong></h3>
            </div>
            <ul class="list-unstyled components">
                <li class="active" style="font-size: 1.1rem;">
                    <a href="cinemaOwnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manage_movies.php"><i class="bi bi-film"></i> Manage Movies</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manage_showtimes.php"><i class="bi bi-ticket"></i> Manage Showtimes</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="select_showtime.php"><i class="bi bi-clock"></i> Showtimes</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manage_cinema.php"><i class="bi bi-building"></i> Manage Cinema</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manageCinemaProfile.php"><i class="bi bi-gear"></i> Settings</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="cinemaOwnerLogout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn">
                    <i class="bi bi-list text-dark"></i>
                </button>
                <div class="ms-auto">
                    <div class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                Welcome, <?= htmlspecialchars($_SESSION['owner_name'] ?? 'Guest'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="cinemaOwnerLogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-5">
            <h2 class="text-start mb-5 fw-bold fs-1">Select Showtime & Seat</h2>

            <form>
                <div class="mb-3">
                    <label for="showtime" class="form-label">Select Showtime</label>
                    <select id="showtime" class="form-select">
                        <option value="">Choose a showtime</option>
                        <?php while ($row = mysqli_fetch_assoc($showtimeResult)): ?>
                            <option value="<?= $row['showtime_id'] ?>">
                                <?= $row['title'] ?> - <?= $row['show_date'] ?> at <?= date("h:i A", strtotime($row['show_time'])) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </form>

            <div id="seatSelection" class="mt-4" style="display:none;">
                <h4>🎟 Select Your Seat</h4>
                <div id="seatLayout"></div>
                <button id="reserveSeats" class="btn btn-success mt-3" style="display:none;">Reserve Selected Seats</button>
            </div>
        </div>
    </div>
</div>


<script>
// Fetch seats when a showtime is selected
$(document).ready(function () {
    $("#showtime").change(function () {
        let showtimeId = $(this).val();
        if (showtimeId) {
            $.ajax({
                url: "fetch_seats.php",
                method: "POST",
                data: { showtime_id: showtimeId },
                success: function (data) {
                    $("#seatLayout").html(data);
                    $("#seatSelection").show();
                    $("#reserveSeats").hide();
                }
            });
        } else {
            $("#seatSelection").hide();
        }
    });
});

// Reserve selected seats
$(document).on("click", ".seat", function () {
    $(this).toggleClass("bg-success");
    $("#reserveSeats").show();
});

$("#reserveSeats").click(function () {
    let showtimeId = $("#showtime").val();
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

    $.ajax({
        url: "reserve_seat.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({ showtime_id: showtimeId, seats: selectedSeats }),
        success: function (response) {
            let res = JSON.parse(response);
            alert(res.message);
            location.reload(); // Refresh after reservation
        }
    });
});
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            });
        });
    </script>

</body>
</html>

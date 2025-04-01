<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

// Fetch movies with showtimes
$showtimeQuery = "SELECT s.showtime_id, m.title, s.show_date, s.show_time 
                  FROM tbl_showtimes s
                  JOIN tbl_movies m ON s.movie_id = m.movie_id
                  ORDER BY s.show_date, s.show_time";
$showtimeResult = mysqli_query($con, $showtimeQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Showtime & Seat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-3">🎬 Choose Your Movie & Showtime</h3>

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
        <button id="payNow" class="btn btn-primary mt-3" style="display:none;">Pay Now</button>
    </div>
</div>

<script>
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
                    $("#payNow").hide();
                }
            });
        } else {
            $("#seatSelection").hide();
        }
    });
});

$(document).on("click", ".seat", function () {
    $(this).toggleClass("bg-success");
    $("#payNow").show();
});

$("#payNow").click(function () {
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

    // Redirect to PayMongo Payment Page
    window.location.href = "paymongo_payment.php?showtime_id=" + showtimeId + "&seats=" + JSON.stringify(selectedSeats);
});
</script>

<style>
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

</body>
</html>

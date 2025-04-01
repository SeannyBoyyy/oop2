<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id']) && $_SESSION['cinema_id']) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

// Fetch cinemas
$cinemaQuery = "SELECT * FROM tbl_cinema";
$cinemaResult = mysqli_query($con, $cinemaQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Seat Configuration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-3">Define Seat Layout</h3>

    <form id="seatForm">
        <div class="mb-3">
            <label for="cinema" class="form-label">Select Cinema</label>
            <select id="cinema" name="cinema_id" class="form-select" required>
                <option value="">Choose a cinema</option>
                <?php while ($row = mysqli_fetch_assoc($cinemaResult)): ?>
                    <option value="<?= $row['cinema_id'] ?>"><?= $row['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="showtime" class="form-label">Select Showtime</label>
            <select id="showtime" name="showtime_id" class="form-select" required>
                <option value="">Select a cinema first</option>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <label for="rows" class="form-label">Number of Rows</label>
                <input type="number" id="rows" name="rows" class="form-control" min="1" required>
            </div>
            <div class="col-md-6">
                <label for="seatsPerRow" class="form-label">Seats per Row</label>
                <input type="number" id="seatsPerRow" name="seatsPerRow" class="form-control" min="1" required>
            </div>
        </div>

        <button type="button" class="btn btn-primary mt-3" onclick="generateSeats()">Generate Seats</button>
    </form>

    <div id="seatLayout" class="mt-4"></div>

    <button id="saveSeats" class="btn btn-success mt-3" style="display:none;" onclick="saveSeatLayout()">Save Layout</button>
</div>

<script>
// Fetch showtimes based on selected cinema
$(document).ready(function () {
    $("#cinema").change(function () {
        var cinemaId = $(this).val();
        if (cinemaId) {
            $.ajax({
                url: "fetch_showtimes.php",
                method: "POST",
                data: { cinema_id: cinemaId },
                success: function (data) {
                    $("#showtime").html(data);
                }
            });
        } else {
            $("#showtime").html('<option value="">Select a cinema first</option>');
        }
    });
});

function generateSeats() {
    let rows = parseInt(document.getElementById('rows').value);
    let seatsPerRow = parseInt(document.getElementById('seatsPerRow').value);
    let seatLayout = document.getElementById('seatLayout');

    if (rows < 1 || seatsPerRow < 1) {
        alert("Rows and seats per row must be at least 1.");
        return;
    }

    let seatHtml = '<div class="d-flex flex-column align-items-center">';
    const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    for (let i = 0; i < rows; i++) {
        seatHtml += `<div class="d-flex">`;
        for (let j = 1; j <= seatsPerRow; j++) {
            let seatLabel = letters[i] + j;
            seatHtml += `<div class="seat m-1 p-2 border text-center" data-row="${letters[i]}" data-seat="${j}" style="width:40px; cursor:pointer;">${seatLabel}</div>`;
        }
        seatHtml += `</div>`;
    }
    seatHtml += '</div>';

    seatLayout.innerHTML = seatHtml;
    document.getElementById("saveSeats").style.display = "block";

    document.querySelectorAll(".seat").forEach(seat => {
        seat.addEventListener("click", function() {
            this.classList.toggle("bg-success");
        });
    });
}

function saveSeatLayout() {
    let cinemaId = $("#cinema").val();
    let showtimeId = $("#showtime").val();
    let seats = [];

    document.querySelectorAll(".seat").forEach(seat => {
        seats.push({
            row_label: seat.getAttribute("data-row"),
            seat_number: seat.getAttribute("data-seat"),
            status: seat.classList.contains("bg-success") ? "reserved" : "available"
        });
    });

    if (!cinemaId || !showtimeId || seats.length === 0) {
        alert("Please select a cinema, showtime, and define seat layout.");
        return;
    }

    $.ajax({
        url: "save_seats.php",
        method: "POST",
        contentType: "application/json",
        data: JSON.stringify({ cinema_id: cinemaId, showtime_id: showtimeId, seats: seats }),
        success: function (response) {
            let res = JSON.parse(response);
            if (res.success) {
                alert(res.message);
                location.reload(); // Refresh after saving
            } else {
                alert("Error: " + res.message);
            }
        }
    });
}


</script>

<style>
.seat {
    border: 1px solid #000;
    width: 40px;
    height: 40px;
    text-align: center;
    cursor: pointer;
    background-color: #ddd;
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

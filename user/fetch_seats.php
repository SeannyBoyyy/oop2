<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (isset($_POST['showtime_id'])) {
    $showtime_id = $_POST['showtime_id'];

    // Fetch seat layout
    $seatQuery = "SELECT row_label, seat_number, status FROM tbl_seats WHERE showtime_id = '$showtime_id'";
    $seatResult = mysqli_query($con, $seatQuery);

    $seats = [];
    while ($row = mysqli_fetch_assoc($seatResult)) {
        $seats[$row['row_label']][] = $row;
    }

    echo '<div class="d-flex flex-column align-items-center">';
    foreach ($seats as $rowLabel => $rowSeats) {
        echo '<div class="d-flex">';
        foreach ($rowSeats as $seat) {
            $statusClass = $seat['status'] === 'reserved' ? 'bg-danger' : '';
            $disabled = $seat['status'] === 'reserved' ? 'pointer-events: none;' : '';
            echo "<div class='seat m-1 p-2 border text-center $statusClass' 
                    data-row='{$seat['row_label']}' 
                    data-seat='{$seat['seat_number']}' 
                    style='width:40px; $disabled'>{$seat['row_label']}{$seat['seat_number']}</div>";
        }
        echo '</div>';
    }
    echo '</div>';
}
?>

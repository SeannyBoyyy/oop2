<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id']) && $_SESSION['cinema_id']) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

if (isset($_POST['cinema_id'])) {
    $cinema_id = $_POST['cinema_id'];
    $selected_showtime = isset($_GET['showtime_id']) ? $_GET['showtime_id'] : ''; // Get showtime_id from URL

    $query = "SELECT * FROM tbl_showtimes WHERE cinema_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $cinema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Choose a showtime</option>';
    while ($row = $result->fetch_assoc()) {
        $isSelected = ($selected_showtime == $row['showtime_id']) ? "selected" : "";
        echo '<option value="' . $row['showtime_id'] . '" ' . $isSelected . '>Screen ' . $row['screen_number'] . ' - ' . $row['show_date'] . ' ' . $row['show_time'] . '</option>';
    }
}
?>

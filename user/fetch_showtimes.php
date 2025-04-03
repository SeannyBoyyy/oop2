<?php
session_start();
include '../config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}

if (isset($_POST['cinema_id'])) {
    $cinema_id = $_POST['cinema_id'];

    $query = "SELECT * FROM tbl_showtimes WHERE cinema_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $cinema_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo '<option value="">Choose a showtime</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['showtime_id'] . '">Screen ' . $row['screen_number'] . ' - ' . $row['show_date'] . ' ' . $row['show_time'] . '</option>';
    }
}
?>

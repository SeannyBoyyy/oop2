<?php
session_start();
include '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userLogin.php");
    exit();
}
$data = json_decode(file_get_contents("php://input"), true);
$showtime_id = $data['showtime_id'];
$selected_seats = $data['seats'];

if (empty($showtime_id) || empty($selected_seats)) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// Fetch price per seat
$query = "SELECT price FROM tbl_showtimes WHERE showtime_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$seat_price = $row['price'];
$total_price = count($selected_seats) * $seat_price; // Multiply price per seat

echo json_encode(["status" => "success", "total_price" => $total_price]);
?>

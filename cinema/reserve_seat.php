<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id']) && $_SESSION['cinema_id']) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$showtime_id = $data['showtime_id'];
$seats = $data['seats'];

if (!empty($showtime_id) && !empty($seats)) {
    foreach ($seats as $seat) {
        $row_label = $seat['row_label'];
        $seat_number = $seat['seat_number'];

        // Reserve the seat
        $query = "UPDATE tbl_seats SET status = 'reserved' 
                  WHERE showtime_id = ? AND row_label = ? AND seat_number = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("isi", $showtime_id, $row_label, $seat_number);
        $stmt->execute();
    }
    echo json_encode(["success" => true, "message" => "Seats reserved successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid data."]);
}
?>

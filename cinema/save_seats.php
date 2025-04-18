<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}
$data = json_decode(file_get_contents("php://input"), true);
$cinema_id = $data['cinema_id'];
$showtime_id = $data['showtime_id'];
$seats = $data['seats'];

if (!empty($cinema_id) && !empty($showtime_id) && !empty($seats)) {
    foreach ($seats as $seat) {
        $row_label = $seat['row_label'];
        $seat_number = $seat['seat_number'];
        $status = isset($seat['status']) ? $seat['status'] : 'available';

        // Check if the seat already exists
        $check_query = "SELECT * FROM tbl_seats WHERE showtime_id = ? AND row_label = ? AND seat_number = ?";
        $stmt_check = $con->prepare($check_query);
        $stmt_check->bind_param("isi", $showtime_id, $row_label, $seat_number);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows == 0) {
            // Insert new seat
            $query = "INSERT INTO tbl_seats (showtime_id, row_label, seat_number, status) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("isis", $showtime_id, $row_label, $seat_number, $status);
            $stmt->execute();
        } else {
            // Update existing seat status
            $query = "UPDATE tbl_seats SET status = ? WHERE showtime_id = ? AND row_label = ? AND seat_number = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("sisi", $status, $showtime_id, $row_label, $seat_number);
            $stmt->execute();
        }
    }
    echo json_encode(["success" => true, "message" => "Seats saved successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid data."]);
}
?>

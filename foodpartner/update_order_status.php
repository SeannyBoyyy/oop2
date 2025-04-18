<?php
session_start();
include '../config.php'; // DB connection

if (!isset($_SESSION['partner_id'])) {
    header("Location: FoodPartnerLogin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    // Update the status in the database
    $stmt = $con->prepare("UPDATE tbl_orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Order updated successfully.";
    } else {
        $_SESSION['msg'] = "Failed to update order.";
    }
    $stmt->close();
}

header("Location: view_orders.php");
exit();

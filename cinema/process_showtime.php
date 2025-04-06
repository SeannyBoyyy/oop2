<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

// ADD SHOWTIME
if (isset($_POST['add_showtime'])) {
    $movie_id = $_POST['movie_id'];
    $cinema_id = $_POST['cinema_id'];
    $screen_number = $_POST['screen_number'];
    $total_seats = $_POST['total_seats'];
    $price = $_POST['price'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];

    $sql = "INSERT INTO tbl_showtimes (movie_id, cinema_id, screen_number, total_seats, price, show_date, show_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "iiiidss", $movie_id, $cinema_id, $screen_number, $total_seats, $price, $show_date, $show_time);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Showtime added successfully!";
    } else {
        $_SESSION['error'] = "Failed to add showtime.";
    }
    header("Location: manage_showtimes.php");
    exit();
}

// Edit Showtime
if (isset($_POST['edit_showtime'])) {
    $showtime_id = $_POST['showtime_id'];
    $movie_id = $_POST['movie_id'];
    $screen_number = $_POST['screen_number'];
    $total_seats = $_POST['total_seats'];
    $price = $_POST['price'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];

    // Owner's cinema is auto-assigned
    $cinema_id = $_SESSION['cinema_id'];

    $update_sql = "UPDATE tbl_showtimes SET 
                   movie_id = '$movie_id', screen_number = '$screen_number',
                   total_seats = '$total_seats', price = '$price',
                   show_date = '$show_date', show_time = '$show_time'
                   WHERE showtime_id = '$showtime_id' AND cinema_id = '$cinema_id'";

    if (mysqli_query($con, $update_sql)) {
        $_SESSION['success'] = "Showtime updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update showtime.";
    }
    header("Location: manage_showtimes.php");
    exit;
}

// Delete Showtime
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Optional: delete related seat records if needed
    mysqli_query($con, "DELETE FROM tbl_seats WHERE showtime_id = $delete_id");

    $delete_query = "DELETE FROM tbl_showtimes WHERE showtime_id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Showtime deleted successfully.'); window.location.href='manage_showtimes.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to delete showtime.');</script>";
    }
}

?>

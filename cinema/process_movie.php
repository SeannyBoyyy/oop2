<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

// Automatically assign cinema_id from session
$cinema_id = $_SESSION['cinema_id'];

// Function to upload the poster
function uploadPoster($file) {
    $targetDir = "uploads/";
    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($fileType), $allowedTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $targetFilePath;
        }
    }
    return false;
}

// ADD MOVIE
if (isset($_POST['add_movie'])) {
    session_start();
    include '../config.php';

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $rating = trim($_POST['rating']);
    $duration = intval($_POST['duration']);
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];

    // Upload poster
    $poster_url = uploadPoster($_FILES['poster']);

    if ($poster_url) {
        $sql = "INSERT INTO tbl_movies (title, description, genre, rating, duration, poster_url, release_date, status, cinema_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssisssi", $title, $description, $genre, $rating, $duration, $poster_url, $release_date, $status, $cinema_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Movie added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add movie.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Invalid image format.";
    }
    header("Location: manage_movies.php");
    exit();
}


// EDIT MOVIE
if (isset($_POST['edit_movie'])) {
    $movie_id = intval($_POST['movie_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $rating = trim($_POST['rating']);
    $duration = intval($_POST['duration']);
    $release_date = $_POST['release_date'];
    $status = $_POST['status'];

    $sql = "UPDATE tbl_movies SET title=?, description=?, genre=?, rating=?, duration=?, release_date=?, status=? WHERE movie_id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "ssssissi", $title, $description, $genre, $rating, $duration, $release_date, $status, $movie_id);

    // Check if a new poster is uploaded
    if (!empty($_FILES['poster']['name'])) {
        $poster_url = uploadPoster($_FILES['poster']);
        if ($poster_url) {
            $sql = "UPDATE tbl_movies SET title=?, description=?, genre=?, rating=?, duration=?, poster_url=?, release_date=?, status=? WHERE movie_id=?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssisssi", $title, $description, $genre, $rating, $duration, $poster_url, $release_date, $status, $movie_id);
        } else {
            $_SESSION['error'] = "Invalid image format.";
            header("Location: manage_movies.php");
            exit();
        }
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Movie updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update movie.";
    }
    mysqli_stmt_close($stmt);
    header("Location: manage_movies.php");
    exit();
}


// DELETE MOVIE
if (isset($_POST['delete_movie'])) {
    $movie_id = intval($_POST['movie_id']);
    $sql = "DELETE FROM tbl_movies WHERE movie_id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $movie_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Movie deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete movie.";
    }
    mysqli_stmt_close($stmt);
    header("Location: manage_movies.php");
    exit();
}
?>

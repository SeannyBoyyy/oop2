<?php
session_start();
require '../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: userLogin.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); // 


if (!isset($_GET['cinema_id'])) {
    die("Invalid request. Cinema not found.");
}

$cinema_id = intval($_GET['cinema_id']);


$sqlCinema = "SELECT * FROM tbl_cinema WHERE cinema_id = ?";
$stmt = $con->prepare($sqlCinema);
$stmt->bind_param("i", $cinema_id);
$stmt->execute();
$resultCinema = $stmt->get_result();
$cinema = $resultCinema->fetch_assoc();

if (!$cinema) {
    die("Cinema not found.");
}


$sqlMovies = "SELECT s.showtime_id, m.title, m.poster_url, m.genre, m.rating, m.duration, s.show_date, s.show_time 
                  FROM tbl_showtimes s
                  JOIN tbl_movies m ON s.movie_id = m.movie_id
                  WHERE s.cinema_id = ?
                  ORDER BY s.show_date, s.show_time";
$stmtMovies = $con->prepare($sqlMovies);
$stmtMovies->bind_param("i", $cinema_id);
$stmtMovies->execute();
$resultMovies = $stmtMovies->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cinema['name']); ?> - Movie Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .movie-poster {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .btn-custom {
            width: 100%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Cinema App</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="user_homepage.php">Home</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            Welcome, <?= htmlspecialchars($firstname); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item text-danger" href="userLogout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($cinema['name']); ?></h2>
                <p><strong>Location:</strong> <?= htmlspecialchars($cinema['location']); ?></p>
                <p><strong>Total Screens:</strong> <?= htmlspecialchars($cinema['total_screens']); ?></p>
                <p><strong>Status:</strong> <?= $cinema['status'] == 'open' ? '<span class="text-success">Open</span>' : '<span class="text-danger">Closed</span>'; ?></p>
                <?php if (!empty($cinema['cinema_image'])) : ?>
                    <img src="../uploads/<?= htmlspecialchars($cinema['cinema_image']); ?>" alt="Cinema Image" class="img-fluid rounded" width="300">
                <?php endif; ?>
            </div>
        </div>

        <h3 class="mt-4">Now Showing</h3>
        <div class="row">
            <?php if ($resultMovies->num_rows > 0) {
                while ($movie = $resultMovies->fetch_assoc()) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="../cinema/<?= htmlspecialchars($movie['poster_url']); ?>" class="card-img-top movie-poster">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($movie['title']); ?></h5>
                                <p class="text-muted">Genre: <?= htmlspecialchars($movie['genre']); ?></p>
                                <p>Rating: <strong><?= htmlspecialchars($movie['rating']); ?></strong></p>
                                <p>Duration: <?= htmlspecialchars($movie['duration']); ?> min</p>
                                <p><strong>Show Date:</strong> <?= htmlspecialchars($movie['show_date']); ?></p>
                                <p><strong>Show Time:</strong> <?= htmlspecialchars($movie['show_time']); ?></p>
                                <a href="select_showtime_click.php?showtime_id=<?= $movie['showtime_id'] ?>" class="btn btn-primary w-100">Buy Now</a>
                            </div>
                        </div>
                    </div>
                <?php }
            } else { ?>
                <p class="text-center text-muted">No movies available at this cinema.</p>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
require '../config.php'; // Include your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: userLogin.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt); // ✅ Close the statement here

// Fetch all cinemas
$sqlCinemas = "SELECT * FROM tbl_cinema WHERE status = 'open'";
$resultCinemas = $con->query($sqlCinemas);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cinema-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .movie-poster {
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
                        <a class="nav-link active" href="userDashboard.php">Dashboard</a>
                    </li>
                    
             
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            Welcome, <?php echo htmlspecialchars($firstname); ?>
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
        <h2 class="text-center mb-4">Available Cinemas</h2>
        <div class="row">
            <?php while ($cinema = $resultCinemas->fetch_assoc()) { ?>
                <div class="col-md-4 mb-3">
                    <div class="card cinema-card">
                        <img src="<?= $cinema['cinema_image'] ?: 'default_cinema.jpg'; ?>" class="card-img-top">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($cinema['name']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($cinema['location']); ?></p>
                            <a href="cinema_schedule.php?cinema_id=<?= $cinema['cinema_id']; ?>" class="btn btn-warning btn-custom">Check Schedule</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <h2 class="text-center my-4">Now Showing Movies</h2>
        <div class="row">
            <?php
            // Fetch all movies that are currently showing
            $sqlMovies = "SELECT m.*, c.name AS cinema_name FROM tbl_movies m 
                          JOIN tbl_cinema c ON m.cinema_id = c.cinema_id 
                          WHERE m.status = 'now showing'";
            $resultMovies = $con->query($sqlMovies);

            while ($movie = $resultMovies->fetch_assoc()) { ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="../cinema/<?= htmlspecialchars($movie['poster_url']); ?>" class="card-img-top movie-poster">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($movie['title']); ?></h6>
                            <p class="text-muted">Genre: <?= htmlspecialchars($movie['genre']); ?></p>
                            <p>Rating: <strong><?= htmlspecialchars($movie['rating']); ?></strong></p>
                            <p>Duration: <?= htmlspecialchars($movie['duration']); ?> min</p>
                            <p><small>Showing at: <strong><?= htmlspecialchars($movie['cinema_name']); ?></strong></small></p>
                            <a href="buy_ticket.php?movie_id=<?= $movie['movie_id']; ?>" class="btn btn-primary btn-custom">Buy</a>
                            <a href="reserve_ticket.php?movie_id=<?= $movie['movie_id']; ?>" class="btn btn-secondary btn-custom">Reserve</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <h2 class="text-center my-4">Our Food Partners</h2>
        <div class="row">
            <?php
            // Fetch active food partners along with their cinema name
            $sqlFoodPartners = "SELECT f.*, c.name AS cinema_name 
                                FROM tbl_foodpartner f 
                                JOIN tbl_cinema c ON f.cinema_id = c.cinema_id 
                                WHERE f.status = 'active' AND f.subscription_status = 'active'";
            $resultFoodPartners = $con->query($sqlFoodPartners);

            while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($partner['business_name']); ?></h5>
                            <p class="text-muted">Location: <?= htmlspecialchars($partner['cinema_name']); ?></p>
                            <p>Contact: <?= htmlspecialchars($partner['partner_email']); ?></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

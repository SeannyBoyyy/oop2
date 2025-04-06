<?php
session_start();
require '../config.php'; // Include your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: userLogin.php");
    exit();
}// Add this query to fetch food partners before the carousel code
$sqlFoodPartners = "SELECT f.*, c.name AS cinema_name 
                    FROM tbl_foodpartner f 
                    JOIN tbl_cinema c ON f.cinema_id = c.cinema_id 
                    WHERE f.status = 'active' AND f.subscription_status = 'active'";
$resultFoodPartners = $con->query($sqlFoodPartners);

if (!$resultFoodPartners) {
    die("Error fetching food partners: " . $con->error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT user_firstname, user_lastname FROM tbl_user WHERE user_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$sqlCinemas = "SELECT * FROM tbl_cinema WHERE status = 'open'";
$resultCinemas = $con->query($sqlCinemas);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
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
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
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
    

    <div class="container my-5">
        <h2 class="text-center section-title">Now Showing Movies</h2>
        <div id="nowShowingCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $sqlMovies = "SELECT m.*, c.name AS cinema_name FROM tbl_movies m 
                            JOIN tbl_cinema c ON m.cinema_id = c.cinema_id 
                            WHERE m.status = 'now showing'";
                $resultMovies = $con->query($sqlMovies);

                $isActive = true; // To set the first item as active
                while ($movie = $resultMovies->fetch_assoc()) { ?>
                    <div class="carousel-item <?= $isActive ? 'active' : ''; ?>">
                <div class="row align-items-center justify-content-center"  style="background-color: rgb(255, 241, 170);">
                    <div class="col-12 col-md-5">
                        <img src="../cinema/<?= htmlspecialchars($movie['poster_url']); ?>" class="img-fluid movie-poster" style="height: 300px; object-fit: cover; border-radius: 5px; margin: 0;">
                    </div>
                    <div class="col-12 col-md-7">
                        <div class="card-body" style=" border-radius: 10px; padding: 20px;">
                            <h4 class="card-title" style="color: #000;"><?= htmlspecialchars($movie['title']); ?></h4>
                            <p class="text-muted">Genre: <?= htmlspecialchars($movie['genre']); ?></p>
                            <p>Rating: <strong><?= htmlspecialchars($movie['rating']); ?></strong></p>
                            <p>Duration: <?= htmlspecialchars($movie['duration']); ?> min</p>
                            <p><small>Showing at: <strong><?= htmlspecialchars($movie['cinema_name']); ?></strong></small></p>
                            <div class="d-grid gap-2">
                                <a href="buy_ticket.php?movie_id=<?= $movie['movie_id']; ?>" class="btn btn-custom btn-yellow">Buy</a>
                                <a href="reserve_ticket.php?movie_id=<?= $movie['movie_id']; ?>" class="btn btn-secondary btn-custom btn-yellow-outline">Reserve</a>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                <?php 
                $isActive = false; // Set to false after the first item
                } ?>
            </div>
            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#nowShowingCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#nowShowingCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <h2 class="text-center section-title">Available Cinemas</h2>
        <div class="row">
            <?php while ($cinema = $resultCinemas->fetch_assoc()) { ?>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card cinema-card">
                <img src="<?= $cinema['cinema_image'] ?: 'default_cinema.jpg'; ?>" class="card-img-top" style="height: 300px; object-fit: cover;">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($cinema['name']); ?></h5>
                    <p class="card-text"><?= htmlspecialchars($cinema['location']); ?></p>
                    <a href="cinema_schedule.php?cinema_id=<?= $cinema['cinema_id']; ?>" class="btn btn-custom">Check Schedule</a>
                </div>
                </div>
            </div>
            <?php } ?>
        </div>


        <h2 class="text-center section-title">Our Food Partners</h2>
        <div id="foodPartnersCarousel" class="carousel slide d-lg-none" data-bs-ride="carousel" data-bs-interval="2000">
            <div class="carousel-inner">
                <?php
                $isActive = true; 
                while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                    <div class="carousel-item <?= $isActive ? 'active' : ''; ?>">
                        <div class="card">
                        <img src="../foodpartner/uploads/foodpartner_profiles/<?= $partner['image_url']?>" class="card-img-top" style="height: 300px; object-fit: cover;">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($partner['business_name']); ?></h5>
                                <p class="text-muted">Location: <?= htmlspecialchars($partner['cinema_name']); ?></p>
                                <p>Contact: <?= htmlspecialchars($partner['partner_email']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php 
                $isActive = false; 
                } ?>
            </div>

        </div>


        <div class="row d-none d-lg-flex">
            <?php
            $resultFoodPartners->data_seek(0); // Reset the result pointer for desktop view
            while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                    <img src="../foodpartner/uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']); ?>" class="card-img-top movie-poster">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($partner['business_name']); ?></h5>
                            <p class="text-muted">Location: <?= htmlspecialchars($partner['cinema_name']); ?></p>
                            <p>Contact: <?= htmlspecialchars($partner['partner_email']); ?></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
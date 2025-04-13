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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
</head>
<body>

    <?php include '../include/userNav.php'; ?>
    <div class="my-5" style="background-color: #121212;">
        <div class="hero-section">
            <div id="nowShowingCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
            <?php
            $sqlMovies = "SELECT m.*, c.cinema_id, c.name AS cinema_name FROM tbl_movies m 
            JOIN tbl_cinema c ON m.cinema_id = c.cinema_id 
            WHERE m.status = 'now showing'";
            $resultMovies = $con->query($sqlMovies);

            $isActive = true; 
            while ($movie = $resultMovies->fetch_assoc()) { ?>
            <div class="carousel-item <?= $isActive ? 'active' : ''; ?>">
            <div class="movie-hero-backdrop w-100" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../cinema/<?= htmlspecialchars($movie['poster_url']); ?>'); background-size: cover; background-position: center; min-height: 500px; height: auto;">
            <div class="container py-5 ">
            <div class="row align-items-center py-4">
            <div class="col-12 col-md-6 text-center text-md-start mb-4 mb-md-0">
            <img src="../cinema/<?= htmlspecialchars($movie['poster_url']); ?>" class="img-fluid movie-poster shadow mx-auto mx-md-0" style="max-height: 400px; object-fit: cover; border-radius: 10px;">
            </div>
            <div class="col-12 col-md-6">
            <div class="text-white text-center text-md-start">
                <h2 class="display-4 fw-bold mb-3" style="font-size: calc(1.8rem + 1.5vw);"><?= htmlspecialchars($movie['title']); ?></h2>
                <div class="movie-info mb-4">
                <span class="badge bg-warning text-dark me-2 mb-2"><i class="bi bi-film me-1"></i><?= htmlspecialchars($movie['genre']); ?></span>
                <span class="badge bg-light text-dark me-2 mb-2"><i class="bi bi-star-fill me-1"></i><?= htmlspecialchars($movie['rating']); ?></span>
                <span class="badge bg-light text-dark mb-2"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($movie['duration']); ?> min</span>
                </div>
                <p class="lead mb-4"><i class="bi  me-2"></i>Showing at: <strong><?= htmlspecialchars($movie['cinema_name']); ?></strong></p>
                <div class="d-flex gap-3 flex-wrap justify-content-center justify-content-md-start">
                <a href="cinema_schedule.php?cinema_id=<?= $movie['cinema_id']; ?>" class="btn btn-warning px-4 mb-2"><i class="bi bi-ticket-perforated me-2"></i>Buy Tickets</a>
                </div>
            </div>
            </div>
            </div>
            </div>
            </div>
            </div>
            <?php 
            $isActive = false;
            } ?>
            </div>
            
            <div class="carousel-indicators">
            <?php
            $resultMovies->data_seek(0);
            $slideIndex = 0;
            while($resultMovies->fetch_assoc()) { ?>
            <button type="button" data-bs-target="#nowShowingCarousel" data-bs-slide-to="<?= $slideIndex; ?>" <?= $slideIndex === 0 ? 'class="active"' : ''; ?> aria-current="<?= $slideIndex === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?= $slideIndex + 1; ?>"></button>
            <?php
            $slideIndex++;
            } ?>
            </div>
            </div>
        </div>
        <section class="cinemas-section py-5  bg-black" style="margin-top: -20px; background-color: #121212;">
            <div class="container">
            <h2 class="display-5 fw-bold text-light mb-5">EXPLORE OUR <span class="text-warning"> CINEMAS</span></h2>
            <div class="row g-4">
            <?php while ($cinema = $resultCinemas->fetch_assoc()) { ?>
            <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card cinema-card h-100 shadow border-0 overflow-hidden transition-hover bg-dark text-light">
                <div class="cinema-image-wrapper position-relative">
                <img src="<?= $cinema['cinema_image'] ?: '../assets/images/default_cinema.jpg'; ?>" 
                class="card-img-top" alt="<?= htmlspecialchars($cinema['name']); ?>"
                style="height: 250px; object-fit: cover; transition: transform 0.5s; opacity: 0.7;">
                <div class="position-absolute top-0 start-0 w-100 h-100  d-flex align-items-center justify-content-center opacity-0 hover-overlay" 
                style="transition: opacity 0.3s ease;">
                </div>
                <div class="position-absolute top-0 end-0 p-3">
                <span class="badge bg-success fs-6 px-3 py-2 rounded-pill"><?= htmlspecialchars($cinema['status']); ?></span>
                </div>
                </div>
            <div class="card-body text-center bg-dark bg-opacity-90 p-4">
              <h4 class="card-title fw-bold mb-3"><?= htmlspecialchars($cinema['name']); ?></h4>
              <p class="card-text mb-3"><i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($cinema['location']); ?></p>
              <a href="cinema_schedule.php?cinema_id=<?= $cinema['cinema_id']; ?>" 
                 class="btn btn-warning w-100 mt-2">
                 View Schedule & Movies
              </a>
            </div>
            </div>
            </div>
            <?php } ?>
            </div>
        </div>
        </section>

        <section class="food-partners-section py-5  text-white" style="margin-bottom: -50px; ">
            <div class="container py-5">
            <div class=" mb-5">
                <h2 class="display-5 fw-bold">Our Food <span class="text-warning">Partners</span></h2>
                <p class="lead ">Exclusive dining options for your movie experience</p>
            </div>

            <div id="foodPartnersCarousel" class="carousel slide d-lg-none" data-bs-ride="carousel">
                <div class="carousel-inner">
                <?php
                $isActive = true; 
                while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                    <div class="carousel-item <?= $isActive ? 'active' : ''; ?>">
                    <div class="card food-card border-0 shadow h-100 bg-dark bg-gradient text-white">
                        <div class="position-relative overflow-hidden">
                        <img src="../foodpartner/uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']); ?>" 
                             class="card-img-top" style="height: 300px; object-fit: cover;">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">Exclusive</span>
                        </div>
                        </div>
                        <div class="card-body text-center p-4">
                        <div class="partner-logo mb-3">
                            <span class="display-6 text-warning"><i class="bi bi-cup-hot-fill"></i></span>
                        </div>
                        <h4 class="card-title fw-bold text-warning"><?= htmlspecialchars($partner['business_name']); ?></h4>
                        <p class="card-text mb-2"><i class="bi bi-film me-2"></i>At <?= htmlspecialchars($partner['cinema_name']); ?></p>
                        </div>
                    </div>
                    </div>
                <?php 
                $isActive = false; 
                } ?>
                </div>
            </div>

            <div class="row g-4 d-none d-lg-flex">
                <?php
                $resultFoodPartners->data_seek(0); 
                while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card food-card border-0 shadow h-100 bg-dark bg-gradient text-white">
                    <div class="ribbon-wrapper">
                        <div class="ribbon bg-warning text-dark">Partner</div>
                    </div>
                    <div class="position-relative overflow-hidden">
                        <img src="../foodpartner/uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']); ?>" 
                         class="card-img-top" style="height: 220px; object-fit: cover;">
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="partner-logo mb-3">
                        <span class="display-6 text-warning"><i class="bi bi-cup-hot-fill"></i></span>
                        </div>
                        <h4 class="card-title fw-bold text-warning"><?= htmlspecialchars($partner['business_name']); ?></h4>
                        <p class="card-text mb-3"><i class="bi bi-film me-2"></i>At <?= htmlspecialchars($partner['cinema_name']); ?></p>
                    </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            </div>
        </section>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
            .navbar-nav .nav-link {
            color: white !important;
            }

            .navbar-nav .nav-link.active {
            color: #ffd700 !important;
            }

            .navbar-nav .nav-link:hover {
            color: #ffd700 !important; 
            }
            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }
            .transition-hover:hover img {
                transform: scale(1.05);
                }
            .transition-hover:hover .hover-overlay {
                opacity: 1 !important;
            }
            .cinema-card {
                border-radius: 12px;
                transition: transform 0.3s;
            }
            .cinema-card:hover {
                transform: translateY(-10px);
            }
            .food-card {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            }
            .food-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.3) !important;
            }
            .ribbon-wrapper {
            width: 85px;
            height: 88px;
            overflow: hidden;
            position: absolute;
            top: -3px;
            right: -3px;
            z-index: 10;
            }
            .ribbon {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            transform: rotate(45deg);
            position: relative;
            padding: 7px 0;
            left: -5px;
            top: 15px;
            width: 120px;
            box-shadow: 0 0 3px rgba(0,0,0,.3);
            }
            .partner-logo {
            width: 60px;
            height: 60px;
            background: rgba(0,0,0,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            }
            .food-partners-section {
            background-image: linear-gradient(to bottom, #0a0a0a, #111111);
            }
    </style>
</body>
</html>
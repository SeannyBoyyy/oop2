<?php
session_start();
require '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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


$sqlFoodPartners = "SELECT * FROM tbl_foodpartner WHERE cinema_id = ? AND status = 'active'";
$stmtFoodPartners = $con->prepare($sqlFoodPartners);
$stmtFoodPartners->bind_param("i", $cinema_id);
$stmtFoodPartners->execute();
$resultFoodPartners = $stmtFoodPartners->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($cinema['name']); ?> - Movie Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/userHomepage.css" rel="stylesheet">
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
    <?php include '../include/userNav.php'; ?>    
    <div class=" my-4" style="background-color: #121212;">
        <div class="hero-section text-light" style="background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('../uploads/<?= !empty($cinema['cinema_image']) ? htmlspecialchars($cinema['cinema_image']) : 'cinema-default.jpg'; ?>'); background-size: cover; background-position: center; padding: 60px 0 40px; margin-bottom: 30px;">
            <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 text-center mb-4 py-5">
                    <?php if (!empty($cinema['cinema_image'])) : ?>
                        <img src="../uploads/<?= htmlspecialchars($cinema['cinema_image']); ?>" alt="<?= htmlspecialchars($cinema['name']); ?>" 
                        class="img-fluid rounded shadow-lg" style="max-height: 300px; object-fit: cover; width: auto;">
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 col-md-12 mb-4 mb-lg-0 text-center text-lg-start">
                    <span class="badge bg-danger px-3 py-2 mb-2">MOVIE SCHEDULE</span>
                    <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($cinema['name']); ?></h1>
                    <p class="lead mb-4 opacity-75"><?= !empty($cinema['description']) ? htmlspecialchars($cinema['description']) : 'Experience the magic of cinema in a comfortable environment with state-of-the-art sound and picture quality.'; ?></p>
                    
                    <div class="d-flex gap-2 mb-4 flex-wrap justify-content-center justify-content-lg-start">
                        <div class="badge bg-light text-dark p-2 px-3 fs-6 mb-2">
                        <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($cinema['location']); ?>
                        </div>
                        <div class="badge bg-light text-dark p-2 px-3 fs-6 mb-2">
                        <i class="bi bi-display me-1"></i> <?= htmlspecialchars($cinema['total_screens']); ?> Screens
                        </div>
                        <div class="badge <?= $cinema['status'] == 'open' ? 'bg-success' : 'bg-danger'; ?> p-2 px-3 fs-6 mb-2">
                        <i class="bi <?= $cinema['status'] == 'open' ? 'bi-door-open' : 'bi-door-closed'; ?> me-1"></i>
                        <?= $cinema['status'] == 'open' ? 'Open Now' : 'Closed'; ?>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="container" >

        <div class="section-header  mb-5">
            
            <h2 class="display-5 fw-bold text-warning">Now Showing</h2>
            <div class="d-flex justify-content-center">
            
            </div>
            <p class="text-light lead">Catch these amazing films currently screening at <?= htmlspecialchars($cinema['name']); ?></p>
        </div>
        <div class="row">
            <?php if ($resultMovies->num_rows > 0) {
            while ($movie = $resultMovies->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white" style="opacity: 0.90;">
                    <img src="../cinema/<?= htmlspecialchars($movie['poster_url']); ?>" class="card-img-top movie-poster">
                    <div class="card-body">
                    <h5 class="card-title fw-bold"><?= htmlspecialchars($movie['title']); ?></h5>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-info text-dark"><?= htmlspecialchars($movie['genre']); ?></span>
                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($movie['rating']); ?></span>
                        <span class="badge bg-secondary"><?= htmlspecialchars($movie['duration']); ?> min</span>
                    </div>
                    <div class="mb-3 border-top border-secondary pt-2">
                        <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>
                            <strong><?= date('D, M j', strtotime($movie['show_date'])); ?></strong>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clock me-2 text-primary"></i>
                            <span class="badge bg-dark border border-light"><?= date('h:i A', strtotime($movie['show_time'])); ?></span>
                        </div>
                        </div>
                    </div>
                    <a href="select_showtime_click.php?showtime_id=<?= $movie['showtime_id'] ?>" class="btn btn-primary btn-custom w-100 mt-2">
                        <i class="bi bi-ticket-perforated me-2"></i>Book Tickets
                    </a>
                    </div>
                </div>
                </div>
            <?php }
            } else { ?>
            <p class="text-center text-muted">No movies available at this cinema.</p>
            <?php } ?>
        </div>
    </div>

    <div class="container my-5 py-5">
        <div class="section-header mb-4">
            <h2 class="display-5 fw-bold text-warning">Food Partners</h2>
            <p class="text-light lead">Enhance your movie experience with delicious treats from our partners</p>
            <div class="divider mx-auto my-3"></div>
        </div>
        
        <div class="row g-4 mt-4">
            <?php if ($resultFoodPartners->num_rows > 0) { ?>
                <?php while ($partner = $resultFoodPartners->fetch_assoc()) { ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100 shadow-sm border-0 bg-dark text-white" style="opacity: 0.90;">
                            <div class="position-relative overflow-hidden">
                                <img src="../foodpartner/uploads/foodpartner_profiles/<?= htmlspecialchars($partner['image_url']); ?>" 
                                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                                     alt="<?= htmlspecialchars($partner['business_name']); ?>">
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-success px-3 py-2 rounded-pill">Partner</span>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h4 class="card-title fw-bold mb-3"><?= htmlspecialchars($partner['business_name']); ?></h4>
                                
                                <div class="mb-3">
                                    <p class="card-text text-white-50 mb-2">
                                        <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                                        <?= htmlspecialchars($partner['partner_address']); ?>
                                    </p>
                                    <p class="card-text mb-2">
                                        <i class="bi bi-person-fill me-2 text-primary"></i>
                                        <?= htmlspecialchars($partner['partner_firstname'] . " " . $partner['partner_lastname']); ?>
                                    </p>
                                    <p class="card-text mb-3">
                                        <i class="bi bi-envelope-fill me-2 text-primary"></i>
                                        <?= htmlspecialchars($partner['partner_email']); ?>
                                    </p>
                                </div>
                                
                                <a href="view_foodproducts.php?partner_id=<?= $partner['partner_id']; ?>&cinema_id=<?= $cinema_id; ?>" 
                                   class="btn btn-primary btn-custom w-100 mt-2">
                                    <i class="bi bi-bag-check-fill me-2"></i>Browse Menu
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12 text-center py-5 bg-dark text-white" style="opacity: 0.85;">
                    <div class="empty-state">
                        <i class="bi bi-shop text-white" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0 lead">No food partners available for this cinema.</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
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
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}


$cinema_id = $_SESSION['cinema_id']; 
$sql = "SELECT * FROM tbl_movies WHERE cinema_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $cinema_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


$owner_id = $_SESSION['owner_id'];
$sql = "SELECT owner_firstname, owner_lastname, cinema_name FROM tbl_cinema_owner WHERE owner_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $owner_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $firstname, $lastname, $cinema_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <link href="../css/cinemaManageMovies.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="wrapper">
        <nav id="sidebar" class="cinema-sidebar">
            <div class="position-sticky">
                <div class="sidebar-header text-center">
                    <i class="bi bi-person-circle display-1 mb-2"></i>
                    <h3 class="fw-bold"><strong><?php echo htmlspecialchars($cinema_name); ?></strong></h3>
                </div>
                <ul class="list-unstyled components">
                    <li  style="font-size: 1.1rem;">
                        <a href="cinemaOwnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <li class="active" style="font-size: 1.1rem;">
                        <a href="manage_movies.php"><i class="bi bi-film"></i> Manage Movies</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_showtimes.php"><i class="bi bi-ticket"></i> Manage Showtimes</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="select_showtime.php"><i class="bi bi-clock"></i> Showtimes</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manage_cinema.php"><i class="bi bi-building"></i> Manage Cinema</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="manageCinemaProfile.php"><i class="bi bi-gear"></i> Settings</a>
                    </li>
                    <li style="font-size: 1.1rem;">
                        <a href="cinemaOwnerLogout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn">
                        <i class="bi bi-list text-dark"></i>
                    </button>
                    <div class="ms-auto">
                        <div class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-dark" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    Welcome, <?php  htmlspecialchars($_SESSION['owner_firstname']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="adminLogout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout</a>
                                    </li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-5">
                <h2 class="text-start mb-5 fw-bold fs-1">Manage Movies</h2>
                <button class="btn  mb-3" data-bs-toggle="modal" data-bs-target="#addMovieModal" style="background-color: #ffd700">Add Movie</button>
                <!-- Movies Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Poster</th>
                                <th>Title</th>
                                <th>Genre</th>
                                <th>Rating</th>
                                <th>Duration</th>
                                <th>Release Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td><img src="<?= $row['poster_url']; ?>" width="50"></td>
                                    <td><?= $row['title']; ?></td>
                                    <td><?= $row['genre']; ?></td>
                                    <td><?= $row['rating']; ?></td>
                                    <td><?= $row['duration']; ?> min</td>
                                    <td><?= $row['release_date']; ?></td>
                                    <td><?= ucfirst($row['status']); ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editBtn" 
                                            data-id="<?= $row['movie_id']; ?>" 
                                            data-title="<?= $row['title']; ?>"
                                            data-description="<?= $row['description']; ?>"
                                            data-genre="<?= $row['genre']; ?>"
                                            data-rating="<?= $row['rating']; ?>"
                                            data-duration="<?= $row['duration']; ?>"
                                            data-poster="<?= $row['poster_url']; ?>"
                                            data-release="<?= $row['release_date']; ?>"
                                            data-status="<?= $row['status']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#editMovieModal">Edit</button>

                                        <button class="btn btn-danger btn-sm deleteBtn" 
                                            data-id="<?= $row['movie_id']; ?>" 
                                            data-title="<?= $row['title']; ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteMovieModal">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Movie Modal -->
    <div class="modal fade" id="addMovieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <input type="text" name="title" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-12">
                                <textarea name="description" class="form-control" placeholder="Description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="genre" class="form-control" placeholder="Genre" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="rating" class="form-control" placeholder="Rating (e.g., PG-13)" required>
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="duration" class="form-control" placeholder="Duration (minutes)" required>
                            </div>
                            <div class="col-md-6">
                                <input type="date" name="release_date" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <input type="file" name="poster" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <select name="status" class="form-select">
                                    <option value="now showing">Now Showing</option>
                                    <option value="coming soon">Coming Soon</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_movie" class="btn btn-primary">Add Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Movie Modal -->
    <div class="modal fade" id="editMovieModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <input type="hidden" name="movie_id" id="editMovieId">
                            <div class="col-md-12">
                                <input type="text" name="title" id="editTitle" class="form-control" placeholder="Title" required>
                            </div>
                            <div class="col-md-12">
                                <textarea name="description" id="editDescription" class="form-control" placeholder="Description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="genre" id="editGenre" class="form-control" placeholder="Genre" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="rating" id="editRating" class="form-control" placeholder="Rating" required>
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="duration" id="editDuration" class="form-control" placeholder="Duration (minutes)" required>
                            </div>
                            <div class="col-md-6">
                                <input type="date" name="release_date" id="editRelease" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <input type="file" name="poster" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <select name="status" id="editStatus" class="form-select">
                                    <option value="now showing">Now Showing</option>
                                    <option value="coming soon">Coming Soon</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_movie" class="btn btn-warning">Update Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Movie Modal -->
    <div class="modal fade" id="deleteMovieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="movie_id" id="deleteMovieId">
                        <p>Are you sure you want to delete <strong id="deleteMovieTitle"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="delete_movie" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="movies.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            });
        });
    </script>
</body>
</html>

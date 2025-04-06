<?php
session_start();
include '../config.php';

if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$cinema_id = $_SESSION['cinema_id'];

$cinema_name = '';
$query = "SELECT name FROM tbl_cinema WHERE cinema_id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $cinema_id);
mysqli_stmt_execute($stmt);
$result_cinema = mysqli_stmt_get_result($stmt);
if ($row = mysqli_fetch_assoc($result_cinema)) {
    $cinema_name = $row['name'];
}



$sql = "SELECT s.showtime_id, m.movie_id, m.title AS movie_title, 
               s.screen_number, s.total_seats, s.price, s.show_date, s.show_time 
        FROM tbl_showtimes s
        JOIN tbl_movies m ON s.movie_id = m.movie_id
        WHERE s.cinema_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $cinema_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$movies = mysqli_query($con, "SELECT movie_id, title FROM tbl_movies WHERE cinema_id = '$cinema_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Showtimes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <link href="../css/cinemaManageMovies.css" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <nav id="sidebar" class="cinema-sidebar">
        <div class="position-sticky">
            <div class="sidebar-header text-center">
                <i class="bi bi-person-circle display-1 mb-2"></i>
                <h3 class="fw-bold"><strong><?php echo htmlspecialchars($cinema_name); ?></strong></h3>
            </div>
            <ul class="list-unstyled components">
                <li style="font-size: 1.1rem;">
                    <a href="cinemaOwnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manage_movies.php"><i class="bi bi-film"></i> Manage Movies</a>
                </li>
                <li class="active" style="font-size: 1.1rem;">
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
                                Welcome, <?php echo isset($_SESSION['owner_name']) ? htmlspecialchars($_SESSION['owner_name']) : 'Guest'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="cinemaOwnerLogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-5">
            <h2 class="text-start mb-5 fw-bold fs-1">Manage Showtimes</h2>
            <button class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addShowtimeModal" style="background-color: #ffd700">Add Showtime</button>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Screen</th>
                        <th>Total Seats</th>
                        <th>Price</th>
                        <th>Show Date</th>
                        <th>Show Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['movie_title']) ?></td>
                        <td><?= htmlspecialchars($row['screen_number']) ?></td>
                        <td><!-- <?= htmlspecialchars($row['total_seats']) ?> --> <a href="manage_cinema.php?showtime_id=<?= htmlspecialchars($row['showtime_id']) ?>" class="btn btn-success btn-sm">View</a></td>
                        <td><?= htmlspecialchars($row['price']) ?></td>
                        <td><?= htmlspecialchars($row['show_date']) ?></td>
                        <td><?= htmlspecialchars($row['show_time']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm editBtn"
                                data-id="<?= $row['showtime_id'] ?>"
                                data-movie-id="<?= $row['movie_id'] ?>"
                                data-screen="<?= $row['screen_number'] ?>"
                                data-seats="<?= $row['total_seats'] ?>"
                                data-price="<?= $row['price'] ?>"
                                data-date="<?= $row['show_date'] ?>"
                                data-time="<?= $row['show_time'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editShowtimeModal">
                                Edit
                            </button>

                            <!-- Delete Button -->
                            <a href="process_showtime.php?delete=<?= $row['showtime_id'] ?>" 
                            onclick="return confirm('Are you sure you want to delete this showtime?')"
                            class="btn btn-danger btn-sm">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Showtime Modal -->
<div class="modal fade" id="addShowtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Add Showtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_showtime.php" method="POST">
                    <select name="movie_id" class="form-control mb-2" required>
                        <option value="">Select Movie</option>
                        <?php while ($movie = mysqli_fetch_assoc($movies)) { ?>
                            <option value="<?= $movie['movie_id'] ?>"><?= htmlspecialchars($movie['title']) ?></option>
                        <?php } ?>
                    </select>
                    <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">
                    <input type="number" name="screen_number" class="form-control mb-2" placeholder="Screen Number" required>
                    <input type="number" name="total_seats" class="form-control mb-2" placeholder="Total Seats" required>
                    <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>
                    <input type="date" name="show_date" class="form-control mb-2" required>
                    <input type="time" name="show_time" class="form-control mb-2" required>
                    <button type="submit" name="add_showtime" class="btn btn-primary w-100">Add Showtime</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Showtime Modal -->
<div class="modal fade" id="editShowtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Showtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_showtime.php" method="POST">
                    <input type="hidden" name="showtime_id" id="edit_showtime_id">
                    
                    <!-- Movie Selection -->
                    <label>Movie</label>
                    <select name="movie_id" id="edit_movie_id" class="form-control mb-2" required>
                        <option value="">Select Movie</option>
                        <?php
                        $movies = mysqli_query($con, "SELECT movie_id, title FROM tbl_movies");
                        while ($movie = mysqli_fetch_assoc($movies)) {
                            echo "<option value='{$movie['movie_id']}'>{$movie['title']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Cinema (Hidden, Auto-Assigned) -->
                    <input type="hidden" name="cinema_id" value="<?= $cinema_id ?>">

                    <label>Screen Number</label>
                    <input type="number" name="screen_number" id="edit_screen_number" class="form-control mb-2" required>

                    <label>Total Seats</label>
                    <input type="number" name="total_seats" id="edit_total_seats" class="form-control mb-2" required>

                    <label>Price</label>
                    <input type="number" step="0.01" name="price" id="edit_price" class="form-control mb-2" required>

                    <label>Show Date</label>
                    <input type="date" name="show_date" id="edit_show_date" class="form-control mb-2" required>

                    <label>Show Time</label>
                    <input type="time" name="show_time" id="edit_show_time" class="form-control mb-2" required>

                    <button type="submit" name="edit_showtime" class="btn btn-warning w-100">Update Showtime</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".editBtn").forEach(button => {
        button.addEventListener("click", () => {
            document.getElementById("edit_showtime_id").value = button.getAttribute("data-id");
            
            // Movie Selection Fix: Ensure correct option is selected
            let movieDropdown = document.getElementById("edit_movie_id");
            let movieId = button.getAttribute("data-movie-id");
            for (let option of movieDropdown.options) {
                if (option.value == movieId) {
                    option.selected = true;
                    break;
                }
            }

            document.getElementById("edit_screen_number").value = button.getAttribute("data-screen");
            document.getElementById("edit_total_seats").value = button.getAttribute("data-seats");
            document.getElementById("edit_price").value = button.getAttribute("data-price");
            document.getElementById("edit_show_date").value = button.getAttribute("data-date");
            document.getElementById("edit_show_time").value = button.getAttribute("data-time");
        });
    });
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
                document.getElementById('content').classList.toggle('active');
            });
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

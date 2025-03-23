<?php
session_start();
include '../config.php';

// Check if cinema owner is logged in
if (!isset($_SESSION['owner_id'])) {
    header("Location: cinemaOwnerLogin.php");
    exit();
}

$cinema_id = $_SESSION['cinema_id'];

$sql = "SELECT s.showtime_id, m.movie_id, m.title AS movie_title, 
               s.screen_number, s.total_seats, s.price, s.show_date, s.show_time 
        FROM tbl_showtimes s
        JOIN tbl_movies m ON s.movie_id = m.movie_id
        WHERE s.cinema_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $cinema_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch movies for dropdown
$movies = mysqli_query($con, "SELECT movie_id, title FROM tbl_movies WHERE cinema_id = '$cinema_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Showtimes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Manage Showtimes</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addShowtimeModal">Add Showtime</button>
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
                <td><?= htmlspecialchars($row['total_seats']) ?></td>
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

                    <button class="btn btn-danger btn-sm deleteBtn"
                        data-id="<?= $row['showtime_id'] ?>"
                        data-bs-toggle="modal" data-bs-target="#deleteShowtimeModal">
                        Delete
                    </button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Add Showtime Modal -->
<div class="modal fade" id="addShowtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Showtime</h5></div>
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

<!-- Delete Showtime Modal -->
<div class="modal fade" id="deleteShowtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Delete Showtime</h5></div>
            <div class="modal-body">
                <p>Are you sure you want to delete this showtime?</p>
                <form action="process_showtime.php" method="POST">
                    <input type="hidden" name="showtime_id" id="delete_showtime_id">
                    <button type="submit" name="delete_showtime" class="btn btn-danger w-100">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Showtime Modal -->
<div class="modal fade" id="editShowtimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Showtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

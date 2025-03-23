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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Movies</h2>

        <!-- Add Movie Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMovieModal">Add Movie</button>

        <!-- Movies Table -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
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

    <!-- Add Movie Modal -->
    <div class="modal fade" id="addMovieModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
                        <textarea name="description" class="form-control mb-2" placeholder="Description" required></textarea>
                        <input type="text" name="genre" class="form-control mb-2" placeholder="Genre" required>
                        <input type="text" name="rating" class="form-control mb-2" placeholder="Rating (e.g., PG-13)" required>
                        <input type="number" name="duration" class="form-control mb-2" placeholder="Duration (minutes)" required>
                        <input type="date" name="release_date" class="form-control mb-2" required>
                        <input type="file" name="poster" class="form-control mb-2" required>
                        <select name="status" class="form-control">
                            <option value="now showing">Now Showing</option>
                            <option value="coming soon">Coming Soon</option>
                        </select>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Edit Movie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="process_movie.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="movie_id" id="editMovieId">
                        <input type="text" name="title" id="editTitle" class="form-control mb-2" required>
                        <textarea name="description" id="editDescription" class="form-control mb-2" required></textarea>
                        <input type="text" name="genre" id="editGenre" class="form-control mb-2" required>
                        <input type="text" name="rating" id="editRating" class="form-control mb-2" required>
                        <input type="number" name="duration" id="editDuration" class="form-control mb-2" required>
                        <input type="date" name="release_date" id="editRelease" class="form-control mb-2" required>
                        <input type="file" name="poster" class="form-control mb-2">
                        <select name="status" id="editStatus" class="form-control">
                            <option value="now showing">Now Showing</option>
                            <option value="coming soon">Coming Soon</option>
                        </select>
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
</body>
</html>

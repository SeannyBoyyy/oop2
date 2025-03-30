<?php
include '../config.php';

session_start();
if (!isset($_SESSION['partner_id'])) {
    header("Location: foodPartnerLogin.php"); // Redirect if not logged in
    exit();
}

$partner_id = $_SESSION['partner_id'];

// Fetch food products
$query = "SELECT f.*, p.business_name FROM tbl_foodproducts f 
          JOIN tbl_foodpartner p ON f.partner_id = p.partner_id WHERE f.partner_id = '$partner_id'";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Food Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-4">Manage Food Products</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Add Product</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Business Name</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['product_id'] ?></td>
                    <td><?= $row['business_name'] ?></td>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['category'] ?></td>
                    <td>₱<?= number_format($row['price'], 2) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm editBtn"
                            data-id="<?= $row['product_id'] ?>"
                            data-name="<?= $row['product_name'] ?>"
                            data-category="<?= $row['category'] ?>"
                            data-price="<?= $row['price'] ?>"
                            data-desc="<?= $row['description'] ?>"
                            data-status="<?= $row['status'] ?>"
                            data-bs-toggle="modal" data-bs-target="#editProductModal">
                            Edit
                        </button>
                        <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $row['product_id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="food_crud.php" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Food Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="partner_id" value="1"> <!-- Change this dynamically -->
                <div class="mb-3">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Category</label>
                    <input type="text" name="category" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Price</label>
                    <input type="number" name="price" class="form-control" required step="0.01">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_product" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="food_crud.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Food Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="mb-3">
                    <label>Product Name</label>
                    <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Category</label>
                    <input type="text" name="category" id="edit_category" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Price</label>
                    <input type="number" name="price" id="edit_price" class="form-control" required step="0.01">
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" id="edit_desc" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_product" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('edit_product_id').value = this.dataset.id;
        document.getElementById('edit_product_name').value = this.dataset.name;
        document.getElementById('edit_category').value = this.dataset.category;
        document.getElementById('edit_price').value = this.dataset.price;
        document.getElementById('edit_desc').value = this.dataset.desc;
        document.getElementById('edit_status').value = this.dataset.status;
    });
});

document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function() {
        let productId = this.dataset.id;
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "food_crud.php?delete_id=" + productId;
            }
        });
    });
});
</script>

</body>
</html>

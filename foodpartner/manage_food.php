<?php
include '../config.php';

session_start();
if (!isset($_SESSION['partner_id'])) {
    header("Location: foodPartnerLogin.php"); // Redirect if not logged in
    exit();
}

$partner_id = $_SESSION['partner_id'];

// Get business name for header
$query_business = "SELECT business_name FROM tbl_foodpartner WHERE partner_id = ?";
$stmt_business = $con->prepare($query_business);
$stmt_business->bind_param("i", $partner_id);
$stmt_business->execute();
$result_business = $stmt_business->get_result();
$row_business = $result_business->fetch_assoc();
$business_name = $row_business['business_name'];
$stmt_business->close();

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/adminDashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="wrapper">
    <nav id="sidebar" class="cinema-sidebar">
        <div class="position-sticky">
            <div class="sidebar-header text-center">
                <i class="bi bi-shop display-1 mb-2"></i>
                <h3 class="fw-bold"><strong><?php echo htmlspecialchars($business_name); ?></strong></h3>
            </div>
            <ul class="list-unstyled components">
                <li style="font-size: 1.1rem;">
                    <a href="foodPartnerDashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="active" style="font-size: 1.1rem;">
                    <a href="manage_food.php"><i class="bi bi-bag"></i> Manage Products</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="view_orders.php"><i class="bi bi-cart-check"></i> Manage Orders</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="manage_foodpartner_profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li style="font-size: 1.1rem;">
                    <a href="FoodPartnerlogout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
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
                            <a class="nav-link dropdown-toggle text-dark" href="#" id="partnerDropdown" role="button" data-bs-toggle="dropdown">
                                Welcome, <?php echo htmlspecialchars($business_name); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="FoodPartnerlogout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-5">
            <h2 class="text-start mb-5 fw-bold fs-1">Manage Food Products</h2>
            <button class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal" style="background-color: #ffd700">Add Product</button>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
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
                                    <div class="d-flex flex-wrap gap-1">
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
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Add Food Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="food_crud.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="partner_id" value="<?= $partner_id ?>">
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
                    <button type="submit" name="add_product" class="btn btn-primary w-100">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Edit Food Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="food_crud.php" method="POST">
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
                    <button type="submit" name="update_product" class="btn btn-warning w-100">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
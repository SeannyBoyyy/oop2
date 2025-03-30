<?php
include '../config.php';

session_start();
if (!isset($_SESSION['partner_id'])) {
    header("Location: foodPartnerLogin.php"); // Redirect if not logged in
    exit();
}

// ADD PRODUCT
if (isset($_POST['add_product'])) {
    $partner_id = $_SESSION['partner_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    // IMAGE UPLOAD
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_folder = "uploads/" . basename($image_name);

    if (move_uploaded_file($image_tmp, $image_folder)) {
        $query = "INSERT INTO tbl_foodproducts (partner_id, product_name, category, price, description, image_url, status)
                  VALUES ('$partner_id', '$product_name', '$category', '$price', '$description', '$image_name', '$status')";
        if ($con->query($query)) {
            echo "<script>
                alert('Product added successfully');
                window.location.href = 'manage_food.php';
            </script>";
        } else {
            echo "Error: " . $con->error;
        }
    } else {
        echo "Failed to upload image.";
    }
}

// UPDATE PRODUCT
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $query = "UPDATE tbl_foodproducts SET 
              product_name = '$product_name', 
              category = '$category', 
              price = '$price', 
              description = '$description', 
              status = '$status' 
              WHERE product_id = '$product_id'";
    
    if ($con->query($query)) {
        echo "<script>
            alert('Product updated successfully');
            window.location.href = 'manage_food.php';
        </script>";
    } else {
        echo "Error: " . $con->error;
    }
}

// DELETE PRODUCT
if (isset($_GET['delete_id'])) {
    $product_id = $_GET['delete_id'];
    $query = "DELETE FROM tbl_foodproducts WHERE product_id = '$product_id'";
    
    if ($con->query($query)) {
        echo "<script>
            alert('Product deleted successfully');
            window.location.href = 'manage_food.php';
        </script>";
    } else {
        echo "Error: " . $con->error;
    }
}

$con->close();
?>

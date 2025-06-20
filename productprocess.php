<?php
include 'db.php';

$uploadDir = 'product_image/';

// Add product
if (isset($_POST['addProduct'])) {
    $name = $conn->real_escape_string($_POST['productName']);
    $desc = $conn->real_escape_string($_POST['productDescription']);
    $price = floatval($_POST['productPrice']);
    $stock = intval($_POST['productStock']);
    $category = intval($_POST['productCategory']);
    $status = $conn->real_escape_string($_POST['productStatus']);

    // Handle image upload
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === 0) {
        // Rename file to avoid conflicts
        $imageName = time() . '_' . basename($_FILES['productImage']['name']);
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['productImage']['tmp_name'], $imagePath)) {
            $sql = "INSERT INTO products 
                (Product_Name, Product_Description, Product_Price, Product_Stock, Product_Image, Product_Status, Category_ID)
                VALUES ('$name', '$desc', $price, $stock, '$imageName', '$status', $category)";
            if (!$conn->query($sql)) {
                echo "Error: " . $conn->error;
                exit();
            }
        } else {
            echo "Failed to upload image.";
            exit();
        }
    } else {
        echo "No image uploaded or upload error.";
        exit();
    }

    header("Location: productmanagement.php");
    exit();
}

// Edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['productId']);
    $name = $conn->real_escape_string($_POST['productName']);
    $desc = $conn->real_escape_string($_POST['productDescription']);
    $price = floatval($_POST['productPrice']);
    $stock = intval($_POST['productStock']);
    $category = intval($_POST['productCategory']);
    $status = $conn->real_escape_string($_POST['productStatus']);

    $uploadDir = 'product_image/';

    // Fetch existing image name in case no new image is uploaded
    $res = $conn->query("SELECT Product_Image FROM products WHERE Product_ID = $id");
    $existingImage = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['Product_Image'] : '';

    // Handle image upload
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === 0) {
        // Delete old image if it exists
        if ($existingImage && file_exists($uploadDir . $existingImage)) {
            unlink($uploadDir . $existingImage);
        }

        $imageName = time() . '_' . basename($_FILES['productImage']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $imagePath)) {
            echo "Failed to upload new image.";
            exit();
        }
    } else {
        // Keep old image
        $imageName = $existingImage;
    }

    $sql = "UPDATE products SET 
                Product_Name = '$name',
                Product_Description = '$desc',
                Product_Price = $price,
                Product_Stock = $stock,
                Product_Image = '$imageName',
                Product_Status = '$status',
                Category_ID = $category
            WHERE Product_ID = $id";

    if (!$conn->query($sql)) {
        echo "Error updating product: " . $conn->error;
        exit();
    }

    header("Location: productmanagement.php");
    exit();
}


// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Delete image from folder
    $res = $conn->query("SELECT Product_Image FROM products WHERE Product_ID = $id");
    if ($res && $res->num_rows > 0) {
        $img = $res->fetch_assoc()['Product_Image'];
        if ($img && file_exists($uploadDir . $img)) {
            unlink($uploadDir . $img);
        }
    }

    // Delete product from DB
    if (!$conn->query("DELETE FROM products WHERE Product_ID = $id")) {
        echo "Error deleting product: " . $conn->error;
        exit();
    }

    header("Location: productmanagement.php");
    exit();
}
?>

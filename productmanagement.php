<?php
include 'db.php';

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter by product category
$product_filter = isset($_GET['product_categories']) ? $_GET['product_categories'] : '';

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM product_categories");

// Build WHERE clause
$where = [];
if ($product_filter !== '') {
    $where[] = "p.Category_ID = '" . mysqli_real_escape_string($conn, $product_filter) . "'";
}

$where_sql = '';
if (count($where) > 0 ) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// Fetch filtered & paginated products with category names
$product_query = "SELECT p.*, c.Category_Name 
                  FROM products p 
                  LEFT JOIN product_categories c ON p.Category_ID = c.Category_ID
                  $where_sql
                  LIMIT $limit OFFSET $offset";
$products = $conn->query($product_query);

// Get total number of matching rows
$total_sql = "SELECT COUNT(*) 
              FROM products p 
              LEFT JOIN product_categories c ON p.Category_ID = c.Category_ID
              $where_sql";
$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_array($total_result)[0];
$total_pages = ceil($total_rows / $limit);

// Count dashboard cards
$total = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$inStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE Product_Stock > 0")->fetch_assoc()['count'];
$outOfStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE Product_Stock = 0")->fetch_assoc()['count'];
$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE Product_Stock > 0 AND Product_Stock < 10")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management</title>
    <link rel="stylesheet" href="css/A-variable.css" />
    <link rel="stylesheet" href="css/A-base.css" />
    <link rel="stylesheet" href="css/productmanagement.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="navigation">
            <ul>
                <li class="logo-section">
                    <img src="images/Logo.jpg" alt="logo" class="logo" /> 
                    <div class="admin-label">ADMIN</div>
                </li>
                <li>
                    <a href="Admindash.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="staffmanagement.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Staff</span>
                    </a>
                </li>
                <li>
                    <a href="taskmanagement.php">
                        <span class="icon">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Tasks</span>
                    </a>
                </li>
                <li>
                    <a href="productmanagement.php">
                        <span class="icon">
                            <ion-icon name="cube-outline"></ion-icon>
                        </span>
                        <span class="title">Products</span>
                    </a>
                </li>
                <li>
                    <a href="stock_reports.php">
                        <span class="icon">
                            <ion-icon name="archive-outline"></ion-icon>
                        </span>
                        <span class="title">Stock Status</span>
                    </a>
                </li>
                <li>
                    <a href="systemlogin.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon>
            </div>
        </div>
        <!-- ================ PRODUCT Details List ================= -->
        <div class="productManagement">
            <h2>Product Management</h2>
            <button onclick="document.getElementById('addModal').style.display='flex'">Add New Product</button>
            <!-- Category Filter Form -->
            <form method="GET" action="productmanagement.php" style="margin-bottom: 15px;">
            <label for="product_categories">Category:</label>
            <select name="product_categories" id="product_categories">
                <option value="">All Categories</option>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['Category_ID'] ?>" <?= $product_filter == $cat['Category_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['Category_Name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Filter</button>
        </form>
            <h2>Product List</h2>
        </div>
            <div class="recentOrders">
                <!-- ========================= PAGINATION ==================== -->
                <div class="pagination">
                    <?php
                    $adjacents = 2;
                    $start = max(1, $page - $adjacents);
                    $end = min($total_pages, $page + $adjacents);

                    if ($page > 1) {
                        echo '<a href="?page=' . ($page - 1) . '&product_categories=' . urlencode($product_filter) . '">Prev</a>';
                    }

                    if ($start > 1) {
                        echo '<a href="?page=1&product_categories=' . urlencode($product_filter) . '">1</a>';
                        if ($start > 2) echo '...';
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        if ($i == $page) {
                            echo '<strong>' . $i . '</strong>';
                        } else {
                            echo '<a href="?page=' . $i . '&product_categories=' . urlencode($product_filter) . '">' . $i . '</a>';
                        }
                    }

                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '...';
                        echo '<a href="?page=' . $total_pages . '&product_categories=' . urlencode($product_filter) . '">' . $total_pages . '</a>';
                    }

                    if ($page < $total_pages) {
                        echo '<a href="?page=' . ($page + 1) . '&product_categories=' . urlencode($product_filter) . '">Next</a>';
                    }
                    ?>
                </div>

                <table class="productTable">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ProductList">
                    <?php while ($row = $products->fetch_assoc()): ?>
                    <tr>
                        <td><img src="product_image/<?= $row['Product_Image'] ?>" width="60"></td>
                        <td><?= htmlspecialchars($row['Product_Name']) ?></td>
                        <td><?= htmlspecialchars($row['Category_Name']) ?></td>
                        <td>RM <?= number_format($row['Product_Price'], 2) ?></td>
                        <td><?= $row['Product_Stock'] ?></td>
                        <td>
                            <span class="status-badge <?= $row['Product_Status'] ?>">
                                <?= ucfirst($row['Product_Status']) ?>
                            </span>
                        </td>
                        <td>
                        <div class="action-buttons">
                            <a href="productprocess.php?edit=<?= $row['Product_ID'] ?>" class="editBtn" 
                                data-id="<?= $row['Product_ID'] ?>"
                                data-name="<?= htmlspecialchars($row['Product_Name'], ENT_QUOTES) ?>"
                                data-desc="<?= htmlspecialchars($row['Product_Description'], ENT_QUOTES) ?>"
                                data-price="<?= $row['Product_Price'] ?>"
                                data-category="<?= $row['Category_ID'] ?>"
                                data-status="<?= $row['Product_Status'] ?>"
                                data-image="<?= $row['Product_Image'] ?>">Edit</a>
                            <a href="productprocess.php?delete=<?= $row['Product_ID'] ?>" 
                                class="deleteBtn" 
                                onclick="return confirm('Delete this product?')">Delete</a>
                        </div>
                    </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
        <!-- Add Product Modal -->
         <div id="addModal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('addModal').style.display='none'">&times;</span>
                <h3>Add New Product</h3>
                <form id="productForm" action="productprocess.php" method="POST" enctype="multipart/form-data" novalidate>
        <!-- Product Name -->
         <div class="form-group">
            <label for="productName">Product Name *</label>
            <input type="text" id="productName" name="productName" class="form-control" required>
            <div class="invalid-feedback">Please provide a product name</div>
        </div>
        <!-- Description -->
        <div class="form-group">
            <label for="productDescription">Description *</label>
            <textarea id="productDescription" name="productDescription" class="form-control" rows="3" required></textarea>
            <div class="invalid-feedback">Please provide a description</div>
        </div>    
        <!-- Price and Stock -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="productPrice">Price (RM) *</label>
                <input type="number" step="0.01" id="productPrice" name="productPrice" class="form-control" required>
                <div class="invalid-feedback">Please provide a valid price</div>
            </div>
            <div class="form-group col-md-6">
                <label for="productStock">Stock Quantity *</label>
                <input type="number" id="productStock" name="productStock" class="form-control" required>
                <div class="invalid-feedback">Please provide stock quantity</div>
            </div>
        </div>        
        <!-- Category and Status -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="productCategory">Category *</label>
                <select id="productCategory" name="productCategory" class="form-control" required>
                    <option value="" disabled selected>Select category</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['Category_ID'] ?>"><?= $cat['Category_Name'] ?></option>
                    <?php endforeach; ?>
                </select>
            <div class="invalid-feedback">Please select a category</div>
            </div>
            <div class="form-group col-md-6">
                <label for="productStatus">Status *</label>
                <select id="productStatus" name="productStatus" class="form-control" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
        </select>
    </div>
</div>    
            <!-- Image Upload -->
            <div class="form-group">
                <label>Product Image *</label>
                <div class="custom-file">
                    <input type="file" name="productImage" id="productImage" class="custom-file-input" accept="image/*" required>
                    <label class="custom-file-label" for="productImage" id="fileLabel"></label>
                    <div class="invalid-feedback">Please select an image</div>
                </div>
            </div>
            <button type="submit" name="addProduct" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</div>
</div>
</div>
</div>
<!-- Edit Product Modal -->
 <div id="editModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <h3>Edit Product</h3>
        <form action="productprocess.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="productId" id="editProductId" required>
            <input type="hidden" name="action" value="edit">

            <input type="text" name="productName" id="editProductName" placeholder="Product Name" required>
            <textarea name="productDescription" id="editProductDescription" placeholder="Description" required></textarea>
            <input type="number" step="0.01" name="productPrice" id="editProductPrice" placeholder="Price" required>

            <select name="productCategory" id="editProductCategory" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['Category_ID'] ?>"><?= $cat['Category_Name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="productStatus" id="editProductStatus" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <p>Current Image:</p>
            <img id="currentImagePreview" src="" width="100" style="margin-bottom: 10px;">
            <p>Change Image (optional):</p>
            <input type="file" name="productImage" accept="image/*">
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
    <!-- =========== Scripts =========  -->
<script src="js/productmanagement.js"></script>
<script src="js/main.js"></script>
</body>
</html>
<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conn->close();
?>
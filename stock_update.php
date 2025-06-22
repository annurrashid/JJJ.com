<?php
session_start();
include 'db.php';

// Show errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check login
if (!isset($_SESSION['staff_id'])) {
    header("Location: systemlogin.php");
    exit();
}

$staffId = $_SESSION['staff_id'];
$staffName = $_SESSION['staff_name'] ?? 'Staff';

// Fetch categories
$categories = $conn->query("SELECT * FROM product_categories");

// Filter logic (optional)
$categoryFilter = $_GET['category'] ?? '';
$whereClause = $categoryFilter !== '' ? "WHERE Category_ID = '" . $conn->real_escape_string($categoryFilter) . "'" : "";

// Fetch product list
$productList = $conn->query("SELECT * FROM products $whereClause ORDER BY Product_Name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['new_stock'])) {
    $productId = intval($_POST['product_id']);
    $addQuantity = intval($_POST['new_stock']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $res = $conn->query("SELECT Product_Stock FROM products WHERE Product_ID = $productId");
    if ($res && $res->num_rows == 1) {
        $data = $res->fetch_assoc();
        $previous = $data['Product_Stock'];
        $newStock = $previous + $addQuantity;

        $conn->query("UPDATE products SET Product_Stock = $newStock WHERE Product_ID = $productId");

        $conn->query("INSERT INTO product_updates 
            (product_id, staff_id, previous_stock, new_stock, difference, notes) 
            VALUES ($productId, $staffId, $previous, $newStock, $addQuantity, '$notes')");

        $_SESSION['message'] = "Stock added successfully!";
    } else {
        $_SESSION['error'] = "Product not found.";
    }

    header("Location: inventory_update.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Update</title>
    <link rel="stylesheet" href="css/variable.css" />
    <link rel="stylesheet" href="css/base.css" />
    <link rel="stylesheet" href="css/inventory.css" />
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
<div class="navigation">
        <ul>
            <li class="logo-section">
            <img src="images/Logo.jpg" alt="Logo" class="logo" />
            <div class="staff-name"><?php echo htmlspecialchars($staffName); ?></div>
        </li>
        <li>
            <a href="staffdash.php">
                <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="stafftask.php">
                <span class="icon"><ion-icon name="checkmark-circle-outline"></ion-icon></span>
                <span class="title">Task Management</span>
            </a>
        </li>
        <li>
            <a href="orderstatus.php">
                <span class="icon">
                    <ion-icon name="cube-outline"></ion-icon>
                </span>
                <span class="title">Order Status</span>
            </a>
        </li>
        <li>
            <a href="inventory_update.php">
                <span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span>
                <span class="title">Inventory Report</span>
            </a>
        </li>
        <li>
            <a href="systemlogin.php">
                <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                <span class="title">Sign Out</span>
            </a>
        </li>
        </ul>
    </div>
    <div class="main">
        <div class="topbar">
        <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
    </div>
    <div class="container">
        <h1>Update Stock</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="success-msg"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Optional Category Filter -->
        <form method="GET" style="margin-bottom: 20px;">
            <label for="category">Category:</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['Category_ID'] ?>" <?= $categoryFilter == $cat['Category_ID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['Category_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Stock Update Form -->
        <form method="POST" class="inventory-form">
            <div class="form-group">
                <label for="product_id">Select Product:</label>
                <select name="product_id" id="product_id" required>
                    <option value="">-- Select Product --</option>
                    <?php while ($row = $productList->fetch_assoc()): ?>
                        <option value="<?= $row['Product_ID'] ?>">
                            <?= htmlspecialchars($row['Product_Name']) ?> (Stock: <?= $row['Product_Stock'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="new_stock">Add Stock Quantity:</label>
                <input type="number" name="new_stock" id="new_stock" min="1" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea name="notes" id="notes" rows="3"></textarea>
            </div>

            <button type="submit" class="btn">Update Stock</button>
        </form>

        <!-- Recent Logs -->
        <div class="recent-updates">
            <h2>Recent Product Updates</h2>
            <?php
            $logs = $conn->query("
                SELECT p.Product_Name, u.previous_stock, u.new_stock, u.difference, u.notes, u.update_time
                FROM product_updates u
                JOIN products p ON u.product_id = p.Product_ID
                WHERE u.staff_id = $staffId
                ORDER BY u.update_time DESC
                LIMIT 5
            ");
            ?>
            <?php if ($logs && $logs->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Previous</th>
                            <th>New</th>
                            <th>Diff</th>
                            <th>Notes</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['Product_Name']) ?></td>
                                <td><?= $log['previous_stock'] ?></td>
                                <td><?= $log['new_stock'] ?></td>
                                <td class="positive">+<?= $log['difference'] ?></td>
                                <td><?= htmlspecialchars($log['notes']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($log['update_time'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent updates found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>

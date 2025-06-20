<?php
session_start();
include 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: systemlogin.php");
    exit();
}

// Pagination setup
$limit = 10; // rows per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter: payment_status only
$payment_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

// Build WHERE clause
$where = [];
if ($payment_filter !== '') {
    $where[] = "p.payment_status = '" . mysqli_real_escape_string($conn, $payment_filter) . "'";
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where);
}

// Get total rows for pagination
$total_sql = "SELECT COUNT(*) 
              FROM payments p 
              JOIN customer c ON p.Cust_ID = c.Cust_ID
              JOIN order_items oi ON p.Order_Item_ID = oi.Order_Item_ID
              $where_sql";
$total_result = mysqli_query($conn, $total_sql);
$total_rows = mysqli_fetch_array($total_result)[0];
$total_pages = ceil($total_rows / $limit);

// Fetch current page data
$sql = "SELECT p.Payment_ID, oi.Product_ID, oi.Quantity, p.Payment_Amount, p.Payment_Status, c.Cust_Name
        FROM payments p
        JOIN customer c ON p.Cust_ID = c.Cust_ID
        JOIN order_items oi ON p.Order_Item_ID = oi.Order_Item_ID
        $where_sql
        ORDER BY p.Payment_ID DESC
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- ======= Styles ====== -->
    <link rel="stylesheet" href="css/A-variable.css" />
    <link rel="stylesheet" href="css/A-base.css" />
    <link rel="stylesheet" href="css/admindash.css">
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <!-- =============== Navigation ================ -->
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
                    <a href="inventory_reports.php">
                        <span class="icon">
                            <ion-icon name="archive-outline"></ion-icon>
                        </span>
                        <span class="title">Inventory Status</span>
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
            <!-- ================ PAYMENT Details List ================= -->
            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Payment Status</h2>
                    </div>

                    <form method="GET" action="Admindash.php" style="margin-bottom: 15px;">
                        <label for="payment_status">Payment Status:</label>
                        <select name="payment_status" id="payment_status">
                            <option value="">All</option>
                            <option value="Completed" <?= $payment_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="Pending" <?= $payment_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Failed" <?= $payment_filter === 'Failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="Refunded" <?= $payment_filter === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                        <button type="submit">Filter</button>
                    </form>
                    <table>
                        <thead>
                            <tr>
                                <td>Customer Name</td>
                                <td>Product ID</td>
                                <td>Quantity</td>
                                <td>Price</td>
                                <td>Payment Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['Cust_Name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Product_ID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                                    echo "<td>RM " . number_format($row['Payment_Amount'], 2) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Payment_Status']) . "</td>";
                                    echo "</tr>";
                                }
                             ?>
                        </tbody>
                    </table>
                    <div class="pagination" style="margin-top: 15px;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&payment_status=<?= urlencode($payment_filter) ?>">Prev</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <?php if ($p == $page): ?>
                                <strong><?= $p ?></strong>
                            <?php else: ?>
                                <a href="?page=<?= $p ?>&payment_status=<?= urlencode($payment_filter) ?>"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page + 1 ?>&payment_status=<?= urlencode($payment_filter) ?>">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="js/main.js"></script>
</body>
</html>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conn->close();
?>

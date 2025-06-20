<?php
session_start();
include 'db.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: systemlogin.php");
    exit();
}

$staffId = $_SESSION['staff_id'];
$staffName = $_SESSION['staff_name'] ?? 'Staff';

// Update order status if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Order_ID']) && isset($_POST['Order_Status'])) {
    $orderId = $_POST['Order_ID'];
    $orderStatus = $_POST['Order_Status'];
    
    $updateQuery = "UPDATE `Orders` SET Order_Status = ? WHERE Order_ID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $orderStatus, $orderId);
    
    if (!$stmt->execute()) {
        die("Update failed: " . $stmt->error);
    }
    $stmt->close();
    
    header("Location: orderstatus.php");
    exit();
}

// Get all orders with complete information 
$query = "
    SELECT 
        o.Order_ID,
        o.Order_Date,
        o.Order_Status,
        o.Total_Amount,
        c.Cust_Name,
        c.Cust_Email,
        c.Cust_PhoneNum,
        MAX(p.Payment_Status) AS Payment_Status,
        GROUP_CONCAT(
            CONCAT('ID:', pr.Product_ID, ' - ', pr.Product_Name, ' (', oi.Quantity, ' x RM', oi.Unit_Price, ')')
            SEPARATOR ', '
        ) AS products
    FROM orders o
    JOIN customer c ON o.Cust_ID = c.Cust_ID
    JOIN order_items oi ON o.Order_ID = oi.Order_ID
    JOIN products pr ON oi.Product_ID = pr.Product_ID
    LEFT JOIN payments p ON oi.Order_Item_ID = p.Order_Item_ID
    GROUP BY o.Order_ID
    ORDER BY o.Order_Date DESC";

$result = $conn->query($query); 

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Status Management</title>
    <link rel="stylesheet" href="css/variable.css" />
    <link rel="stylesheet" href="css/base.css" />
    <link rel="stylesheet" href="css/orderstatus.css" />
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

        <h2>Order Status Management</h2>
        
        <div class="card">
            <div class="card-header">
                <h3>Current Orders</h3>
            </div>
            
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['Order_ID']) ?></td>
                                <td><?= htmlspecialchars($order['Cust_Name']) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['Cust_Email']) ?><br>
                                    <?= htmlspecialchars($order['Cust_PhoneNum']) ?>
                                </td>
                                <td><?= htmlspecialchars($order['products']) ?></td>
                                <td>RM <?= number_format($order['Total_Amount'], 2) ?></td>
                                <td><?= date('d M Y', strtotime($order['Order_Date'])) ?></td>
                                <td>
                                    <span class="payment payment-<?= strtolower($order['Payment_Status'] ?? 'pending') ?>">
                                        <?= isset($order['Payment_Status']) ? htmlspecialchars($order['Payment_Status']) : 'Pending' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status status-<?= strtolower($order['Order_Status']) ?>">
                                        <?= htmlspecialchars($order['Order_Status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="Order_ID" value="<?= $order['Order_ID'] ?>">
                                        <select name="Order_Status" class="status-select">
                                            <option value="Pending" <?= $order['Order_Status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Processing" <?= $order['Order_Status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="Completed" <?= $order['Order_Status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $order['Order_Status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="update-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-orders">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: systemlogin.php");
    exit();
}

include 'db.php';

// Fetch product update logs
$logs = $conn->query("
    SELECT pu.*, p.Product_Name, s.Staff_Name 
    FROM product_updates pu
    JOIN products p ON pu.Product_ID = p.Product_ID
    JOIN staff s ON pu.Staff_ID = s.Staff_ID
    ORDER BY pu.Update_Time DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Stock Update Report</title>
    <link rel="stylesheet" href="css/A-variable.css" />
    <link rel="stylesheet" href="css/A-base.css" />
    <link rel="stylesheet" href="css/stock-report.css">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="container" style="display: flex;">
    <!-- Sidebar -->
    <div class="navigation">
        <ul>
            <li class="logo-section">
                <img src="images/Logo.jpg" alt="logo" class="logo" /> 
                <span class="title">ADMIN</span></a></li>
            <li>
                    <a href="Admindash.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span></a></li>
                <li>
                    <a href="staffmanagement.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Staff</span></a></li>
                <li>
                    <a href="taskmanagement.php">
                        <span class="icon">
                            <ion-icon name="checkmark-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Tasks</span></a></li>
                <li>
                    <a href="productmanagement.php">
                        <span class="icon">
                            <ion-icon name="cube-outline"></ion-icon>
                        </span>
                        <span class="title">Products</span></a></li>
                <li>
                    <a href="stock_reports.php">
                        <span class="icon">
                            <ion-icon name="archive-outline"></ion-icon>
                        </span>
                        <span class="title">Stock Status</span></a></li>
                <li>
                    <a href="systemlogin.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span></a></li>
                    </ul>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
        </div>

        <div class="report-container">
            <h2>Product Stock Update Report</h2>

            <?php if ($logs->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Updated By</th>
                            <th>Previous Stock</th>
                            <th>New Stock</th>
                            <th>Difference</th>
                            <th>Notes</th>
                            <th>Update Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <form method="post" action="download_report_pdf.php" style="margin-bottom: 20px;">
    <button type="submit" class="btn-download">Download PDF Report</button>
</form>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['Product_Name']) ?></td>
                                <td><?= htmlspecialchars($log['Staff_Name']) ?></td>
                                <td><?= $log['Previous_Stock'] ?></td>
                                <td><?= $log['New_Stock'] ?></td>
                                <td class="positive">+<?= $log['Difference'] ?></td>
                                <td><?= htmlspecialchars($log['Notes']) ?></td>
                                <td><?= date("M j, Y g:i A", strtotime($log['Update_Time'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No product update records found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>

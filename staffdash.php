<?php
session_start();
include 'db.php';

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: systemlogin.php");
    exit();
}

$staffId = $_SESSION['staff_id'];

// Get staff name
$query = "SELECT Staff_Name FROM staff WHERE Staff_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$stmt->bind_result($staffName);
$stmt->fetch();
$stmt->close();

function statusClass($status) {
    return strtolower(str_replace(' ', '-', $status));
}

// Get tasks assigned to this staff using JOIN with staff_tasks bridge table
$tasksQuery = "
    SELECT t.Task_Title, t.Task_Description, t.Task_Deadline, t.Task_Status 
    FROM tasks t
    INNER JOIN staff_tasks st ON t.Task_ID = st.Task_ID
    WHERE st.Staff_ID = ?
    ORDER BY t.Task_Deadline ASC
";
$stmt = $conn->prepare($tasksQuery);
$stmt->bind_param("i", $staffId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Staff Dashboard</title>
    <link rel="stylesheet" href="css/variable.css" />
    <link rel="stylesheet" href="css/base.css" />
    <link rel="stylesheet" href="css/staffdash.css" />
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
            <a href="stock_update.php">
                <span class="icon"><ion-icon name="clipboard-outline"></ion-icon></span>
                <span class="title">Stock Report</span>
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

    <div class="details">
        <div class="recentOrders">
            <div class="cardHeader">
                <h2>Your Assigned Tasks</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Deadline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($task = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['Task_Title']); ?></td>
                                <td><?php echo htmlspecialchars($task['Task_Description']); ?></td>
                                <td><?php echo htmlspecialchars($task['Task_Deadline']); ?></td>
                                <td>
                                    <span class="status <?php echo statusClass($task['Task_Status']); ?>">
                                        <?php echo htmlspecialchars($task['Task_Status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No tasks assigned.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

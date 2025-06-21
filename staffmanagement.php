<?php

include 'db.php';

// Add new staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $conn->real_escape_string($_POST['staffName']);
    $salary = floatval($_POST['staffSalary']);
    $phone = $conn->real_escape_string($_POST['staffPhonenum']);
    $email = $conn->real_escape_string($_POST['staffEmail']);
    $position = $conn->real_escape_string($_POST['staffPosition']);
    $password = $conn->real_escape_string($_POST['staffPassword']);
    $status = $conn->real_escape_string($_POST['staffStatus']);

    $conn->query("INSERT INTO staff (Staff_Name, Staff_Salary, Staff_PhoneNum, Staff_Email, Staff_Position, Staff_Password, Staff_Status) 
                  VALUES ('$name', $salary, '$phone', '$email', '$position', '$password', '$status')");

    header("Location: staffmanagement.php");
    exit();
}

// Edit staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['staffID']);
    $name = $conn->real_escape_string($_POST['staffName']);
    $salary = floatval($_POST['staffSalary']);
    $phone = $conn->real_escape_string($_POST['staffPhonenum']);
    $email = $conn->real_escape_string($_POST['staffEmail']);
    $position = $conn->real_escape_string($_POST['staffPosition']);
    $password = $conn->real_escape_string($_POST['staffPassword']);
    $status = $conn->real_escape_string($_POST['staffStatus']);

    $conn->query("UPDATE staff SET 
        Staff_Name = '$name',
        Staff_Salary = $salary,
        Staff_PhoneNum = '$phone',
        Staff_Email = '$email',
        Staff_Position = '$position',
        Staff_Password = '$password',
        Staff_Status = '$status'
        WHERE Staff_ID = $id");

    header("Location: staffmanagement.php");
    exit();
}

// Delete staff
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM staff WHERE Staff_ID = $id");
    header("Location: staffmanagement.php");
    exit();
}

// Fetch and count staff
$staffResult = $conn->query("SELECT * FROM staff ORDER BY Staff_ID DESC");

$totalStaff = 0;
$activeStaff = 0;
$inactiveStaff = 0;
$onLeaveStaff = 0;
while ($row = $staffResult->fetch_assoc()) {
    $totalStaff++;
    switch (strtolower($row['Staff_Status'])) {
        case 'active': $activeStaff++; break;
        case 'inactive': $inactiveStaff++; break;
        case 'onleave': $onLeaveStaff++; break;
    }
}
$staffResult = $conn->query("SELECT * FROM staff ORDER BY Staff_ID DESC");

// Fetch staff from DB
$staffResult = $conn->query("SELECT * FROM staff ORDER BY Staff_ID DESC");

// Count total, active, inactive, onLeave staff for cards
$totalStaff = 0;
$activeStaff = 0;
$inactiveStaff = 0;
$onLeaveStaff = 0;
while ($row = $staffResult->fetch_assoc()) {
    $totalStaff++;
    switch (strtolower($row['Staff_Status'])) {
        case 'active': $activeStaff++; break;
        case 'inactive': $inactiveStaff++; break;
        case 'onleave': $onLeaveStaff++; break;
    }
}
// Re-fetch for displaying in table
$staffResult = $conn->query("SELECT * FROM staff ORDER BY Staff_ID DESC");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Staff Management</title>
    <link rel="stylesheet" href="css/A-variable.css" />
    <link rel="stylesheet" href="css/A-base.css" />
    <link rel="stylesheet" href="css/staff.css" />
    <script src="https://unpkg.com/ionicons@5.5.2/dist/ionicons.js"></script>
</head>

<body>
    <!-- =============== Navigation ================ -->
        <div class="navigation">
            <ul>
                <li class="logo-section">
                <img src="images/Logo.jpg" alt="logo" class="logo" /> 
                <span class="title">ADMIN</span></a></li>
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
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>

            <!-- ================= Staff Management Content ================= -->
            <div class="cardBox">
                <div class="card">
                    <div class="numbers" id="totalStaff"><?= $totalStaff ?></div>
                    <div class="cardName">Total Staff</div>
                    <div class="iconBx"><ion-icon name="people-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="activeStaff"><?= $activeStaff ?></div>
                    <div class="cardName">Active Staff</div>
                    <div class="iconBx"><ion-icon name="checkmark-circle-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="inactiveStaff"><?= $inactiveStaff ?></div>
                    <div class="cardName">Inactive Staff</div>
                    <div class="iconBx"><ion-icon name="close-circle-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="onLeaveStaff"><?= $onLeaveStaff ?></div>
                    <div class="cardName">Staff on Leave</div>
                    <div class="iconBx"><ion-icon name="time-outline"></ion-icon></div>
                </div>
            </div>

            <!-- ========== Staff Management Section ========== -->
            <div class="staffManagement">
                <h2 class="section-title">Staff Management</h2>

                <!-- Add Staff Modal -->
                <div id="addStaffModal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form id="addStaffForm" method="POST" action="staffmanagement.php">
                            <input type="hidden" name="action" value="add" />
                            <h3>Staff Name:</h3>
                            <input type="text" name="staffName" placeholder="Staff Name" required><br>
                            <h3>Staff Salary:</h3>
                            <input type="number" step="0.01" name="staffSalary" placeholder="staffSalary" required>
                            <h3>Staff Phone Num:</h3>
                            <input type="text" name="staffPhonenum" placeholder="staffPhonenum" required>
                            <h3>Staff Email:</h3>
                            <input type="email" name="staffEmail" placeholder="staffEmail" required>
                            <h3>Staff Position:</h3>
                            <input type="text" name="staffPosition" placeholder="staffPosition" required />
                            <h3>Staff Password:</h3>
                            <input type="password" name="staffPassword" placeholder="staffPassword" required />

                            <h3>Staff Status:</h3>
                            <select name="staffStatus" id="staffStatus" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="onLeave">On Leave</option>
                            </select>
                            <button onclick="document.getElementById('addStaffModal').style.display='flex'">Add New Staff</button>
                        </form>
                    </div>
                </div>

                <!-- Edit Staff Modal -->
                <div id="editStaffModal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <form id="editStaffForm" class="modal-form" method="POST" action="staffmanagement.php">
                            <input type="hidden" name="action" value="edit" />
                            <input type="hidden" name="staffID" id="editStaffID" />
                            <h3>Staff Name:</h3>
                            <input type="text" name="staffName" id="editStaffName" required />
                            <h3>Staff Salary:</h3>
                            <input type="number" step="0.01" name="staffSalary" id="editStaffSalary" required />
                            <h3>Staff Phone Num:</h3>
                            <input type="text" name="staffPhonenum" id="editStaffPhonenum" required />
                            <h3>Staff Email:</h3>
                            <input type="email" name="staffEmail" id="editStaffEmail" required />
                            <h3>Staff Position:</h3>
                            <input type="text" name="staffPosition" id="editStaffPosition" required />
                            <h3>Staff Password:</h3>
                            <input type="password" name="staffPassword" id="editStaffPassword" required />
                            
                            <h3>Staff Status:</h3>
                            <select name="staffStatus" id="editStaffStatus" required>
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="onLeave">On Leave</option>
                            </select>
                            <button type="submit">Update Staff</button>
                        </form>
                    </div>
                </div>
            </div>


                <!-- Staff Table -->
                <div class="cardHeader">
                    <button onclick="openModal()" class="btn" id="addStaffBtn">Add New Staff</button>
                    <h2>Staff List</h2>
                </div>
                <div class="recentOrders">
                    <table class="staffTable">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Salary</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="staffList">
                            <?php while ($row = $staffResult->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['Staff_Name']) ?></td>
                                    <td><?= number_format($row['Staff_Salary'], 2) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_PhoneNum']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Email']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Position']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Password']) ?></td>
                                    <td><?= htmlspecialchars($row['Staff_Status']) ?></td>
                                    <td>
                                        <button onclick="openEditModal(
                                        <?= $row['Staff_ID'] ?>,
                                        '<?= htmlspecialchars($row['Staff_Name'], ENT_QUOTES) ?>',
                                        <?= $row['Staff_Salary'] ?>,
                                        '<?= htmlspecialchars($row['Staff_PhoneNum'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($row['Staff_Email'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($row['Staff_Position'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($row['Staff_Password'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($row['Staff_Status'], ENT_QUOTES) ?>'
                                        )">Edit</button>
                                            <a href="staffmanagement.php?delete=<?= $row['Staff_ID'] ?>" class="deleteBtn" onclick="return confirm('Delete Staff Record?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/staffmanagement.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
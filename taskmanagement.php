<?php

include 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = $conn->real_escape_string($_POST['taskTitle']);
        $description = $conn->real_escape_string($_POST['taskDescription']);
        $deadline = $conn->real_escape_string($_POST['taskDeadline']);
        $status = 'pending';

        $conn->query("INSERT INTO tasks (Task_Title, Task_Description, Task_Deadline, Task_Status) VALUES ('$title', '$description', '$deadline', '$status')");
        $taskId = $conn->insert_id;

        if (!empty($_POST['assignedStaff']) && is_array($_POST['assignedStaff'])) {
            foreach ($_POST['assignedStaff'] as $staffId) {
                $staffId = intval($staffId);
                $conn->query("INSERT INTO staff_tasks (Task_ID, Staff_ID, Status) VALUES ($taskId, $staffId, 'pending')");
            }
        }

        header("Location: taskmanagement.php");
        exit();
    }

    if ($action === 'edit') {
        $id = intval($_POST['taskID']);
        $title = $conn->real_escape_string($_POST['taskTitle']);
        $description = $conn->real_escape_string($_POST['taskDescription']);
        $deadline = $conn->real_escape_string($_POST['taskDeadline']);

        $conn->query("UPDATE tasks SET Task_Title = '$title', Task_Description = '$description', Task_Deadline = '$deadline' WHERE Task_ID = $id");

        $conn->query("DELETE FROM staff_tasks WHERE Task_ID = $id");

        if (!empty($_POST['assignedStaff']) && is_array($_POST['assignedStaff'])) {
            foreach ($_POST['assignedStaff'] as $staffId) {
                $staffId = intval($staffId);
                $conn->query("INSERT INTO staff_tasks (Task_ID, Staff_ID, Status) VALUES ($id, $staffId, 'pending')");
            }
        }

        header("Location: taskmanagement.php");
        exit();
    }
}

// Handle deletions
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM staff_tasks WHERE Task_ID = $id");
    $conn->query("DELETE FROM tasks WHERE Task_ID = $id");
    header("Location: taskmanagement.php");
    exit();
}

// Fetch tasks and staff
$tasksResult = $conn->query("
    SELECT t.*, GROUP_CONCAT(s.Staff_Name SEPARATOR ', ') as assigned_staff
    FROM tasks t
    LEFT JOIN staff_tasks st ON t.Task_ID = st.Task_ID
    LEFT JOIN staff s ON st.Staff_ID = s.Staff_ID
    GROUP BY t.Task_ID
    ORDER BY t.Task_Deadline ASC
");

$staffResult = $conn->query("SELECT * FROM staff WHERE Staff_Status = 'active' ORDER BY Staff_Name");

// Count task statistics
$totalTasksResult = $conn->query("SELECT COUNT(*) as total FROM tasks");
$totalTasks = $totalTasksResult->fetch_assoc()['total'];

$pendingTasksResult = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE Task_Status = 'pending'");
$pendingTasks = $pendingTasksResult->fetch_assoc()['total'];

$inProgressTasksResult = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE Task_Status = 'in progress'");
$inProgressTasks = $inProgressTasksResult->fetch_assoc()['total'];

$completedTasksResult = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE Task_Status = 'completed'");
$completedTasks = $completedTasksResult->fetch_assoc()['total'];

$now = date('Y-m-d H:i:s');
$overdueTasksResult = $conn->query("SELECT COUNT(*) as total FROM tasks WHERE Task_Deadline < '$now' AND Task_Status != 'completed'");
$overdueTasks = $overdueTasksResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Task Management</title>
    <link rel="stylesheet" href="css/A-variable.css" />
    <link rel="stylesheet" href="css/A-base.css" />
    <link rel="stylesheet" href="css/task.css" />
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <!-- =============== Navigation ================ -->
        <div class="navigation">
            <ul>
                <li class="logo-section">
                    <img src="images/Logo.jpg" alt="Logo" class="logo" />
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
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>

            <!-- ================= Task Management Content ================= -->
            <div class="cardBox">
                <div class="card">
                    <div class="numbers" id="totalTasks"><?= $totalTasks ?></div>
                    <div class="cardName">Total Tasks</div>
                    <div class="iconBx"><ion-icon name="list-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="pendingTasks"><?= $pendingTasks ?></div>
                    <div class="cardName">Pending Tasks</div>
                    <div class="iconBx"><ion-icon name="time-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="inProgressTasks"><?= $inProgressTasks ?></div>
                    <div class="cardName">In Progress</div>
                    <div class="iconBx"><ion-icon name="hourglass-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="completedTasks"><?= $completedTasks ?></div>
                    <div class="cardName">Completed</div>
                    <div class="iconBx"><ion-icon name="checkmark-done-outline"></ion-icon></div>
                </div>
                <div class="card">
                    <div class="numbers" id="overdueTasks"><?= $overdueTasks ?></div>
                    <div class="cardName">Overdue</div>
                    <div class="iconBx"><ion-icon name="alert-circle-outline"></ion-icon></div>
                </div>
            </div>

            <div class="taskManagement">
                <h2 class="section-title">Task Management</h2>

                <!-- Add Task Modal -->
                <div id="addTaskModal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form id="addtaskForm" method="POST" action="taskmanagement.php">
                            <input type="hidden" name="action" value="add">
                            <h3>Task Title:</h3>
                            <input type="text" name="taskTitle" placeholder="Task Title" required><br>
                            <h3>Task Description:</h3>
                            <textarea name="taskDescription" placeholder="Task Description" required></textarea>
                            <h3>Task Deadline:</h3>
                            <input type="datetime-local" name="taskDeadline" required><br>
                            <h3>Assign to Staff:</h3>
                            <div class="task-assignment">
                                <?php while ($staff = $staffResult->fetch_assoc()): ?>
                                    <div>
                                        <input type="checkbox" name="assignedStaff[]" value="<?= $staff['Staff_ID'] ?>" id="staff_<?= $staff['Staff_ID'] ?>">
                                        <label for="staff_<?= $staff['Staff_ID'] ?>"><?= htmlspecialchars($staff['Staff_Name']) ?></label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <button type="submit">Add Task</button>
                        </form>
                    </div>
                </div>


                <!-- Edit Task Modal -->
                <div id="editTaskModal" class="modal" style="display:none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <form id="edittaskForm" method="POST" action="taskmanagement.php">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="taskID" id="editTaskID">
                            <h3>Task Title:</h3>
                            <input type="text" name="taskTitle" id="editTaskTitle" placeholder="Task Title" required><br>
                            <h3>Task Description:</h3>
                            <textarea name="taskDescription" id="editTaskDescription" placeholder="Task Description" required></textarea>
                            <h3>Task Deadline:</h3>
                            <input type="datetime-local" name="taskDeadline" id="editTaskDeadline" required><br>
                            <h3>Assign to Staff:</h3>
                            <div class="task-assignment" id="editStaffAssignment">
                            </div>
                            <button type="submit">Update Task</button>
                        </form>
                    </div>
                </div>

                <!-- Task List -->
                <div class="TaskHeader">
                    <button onclick="openModal()" class="btn">Add New Task</button>
                    <h2>Task List</h2>
                </div>
                <div class="list-table">
                    <table class="staffTable">
                        <thead>
                            <tr>
                                <th>Task Title</th>
                                <th>Description</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Assigned Staff</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($task = $tasksResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($task['Task_Title']) ?></td>
                                    <td><?= htmlspecialchars($task['Task_Description']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($task['Task_Deadline'])) ?></td>
                                    <td><?= htmlspecialchars($task['Task_Status']) ?></td>
                                    <td><?= htmlspecialchars($task['assigned_staff']) ?></td>
                                    <td>
                                        <button class="edit-btn" onclick='editTask(<?= json_encode($task) ?>)'>Edit</button>
                                        <a class="delete-btn" href="?delete=<?= $task['Task_ID'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="js/taskmanagement.js"></script>
    <script src="js/main.js"></script>
</body>
</html>

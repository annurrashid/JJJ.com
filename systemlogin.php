<?php
session_start();
include 'db.php';

$login_error = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "login") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if ($role === "admin") {
        $sql = "SELECT * FROM admin WHERE Admin_Name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // DEBUG: uncomment these lines to help debug issues
            // echo "Typed: $password <br>";
            // echo "Stored: " . $admin['Admin_Password'] . "<br>";

            if ($password === trim($admin['Admin_Password'])) {
                $_SESSION['admin_id'] = $admin['Admin_ID'];
                $_SESSION['admin_name'] = $admin['Admin_Name'];
                $_SESSION['role'] = 'admin';
                header("Location: Admindash.php");
                exit();
            } else {
                $login_error = "Invalid admin password.";
            }
        } else {
            $login_error = "Admin not found.";
        }

    } elseif ($role === "staff") {
        $sql = "SELECT * FROM staff WHERE Staff_Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $staff = $result->fetch_assoc();

            // DEBUG: uncomment these lines to help debug issues
            echo "Typed: $password <br>";
            echo "Stored: " . $staff['Staff_Password'] . "<br>";

            if ($password === trim($staff['Staff_Password'])) {
                $_SESSION['staff_id'] = $staff['Staff_ID'];
                $_SESSION['staff_name'] = $staff['Staff_Name'];
                $_SESSION['role'] = 'staff';
                header("Location: staffdash.php");
                exit();
            } else {
                $login_error = "Invalid staff password.";
            }
        } else {
            $login_error = "Staff not found.";
        }
    } else {
        $login_error = "Invalid role selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Login</title>
    <link rel="stylesheet" href="css/systemlogin.css">
    <style>
        background-image: url("images/Bg.jpg");
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>System Login</h1>
        <div id="login" class="form-section active">
            <?php if ($login_error): ?>
                <p class="error"><?= $login_error ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="role">Login As:</label>
                    <select name="role" id="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email/Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
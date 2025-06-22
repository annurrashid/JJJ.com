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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/systemlogin.css" />
  <title>JJJ | Admin/Staff Login</title>
  <style>
    .form-message {
      margin-bottom: 10px;
      text-align: center;
      font-weight: bold;
    }
    .form-message.success { color: green; }
    .form-message.error { color: red; }
  </style>
</head>
<body>
    <div class="wrapper">
        <div class="form-box">
            <form class="login-container" method="POST" action="systemlogin.php">
                <input type="hidden" name="action" value="login">

                <div class="top">
                    <header>Admin / Staff Login</header>
                </div>

                <!-- Optional: Display login error -->
                <?php if (!empty($login_error)) : ?>
                    <div class="form-message error"><?= htmlspecialchars($login_error) ?></div>
                <?php endif; ?>

                <!-- Role selection -->
                <div class="input-box">
                    <select name="role" class="input-field" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    </select>
                    <i class="bx bx-id-card"></i>
                </div>

                <!-- Username or Email -->
                <div class="input-box">
                    <input type="text" name="username" class="input-field" placeholder="Admin Name / Staff Email" required>
                    <i class="bx bx-user"></i>
                </div>

                <!-- Password -->
                <div class="input-box">
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                    <i class="bx bx-lock-alt"></i>
                </div>

                <!-- Submit -->
                <div class="input-box">
                    <input type="submit" class="submit" value="Login">
                </div>
            </form>
            </div>
        </div>
    </body>
</html>

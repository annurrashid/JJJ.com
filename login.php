<?php
session_start();
require 'db.php';

$message = '';
$formType = 'login'; // default

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT Cust_ID, Cust_Password FROM customer WHERE Cust_Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Cust_Password'])) {
                $_SESSION['Cust_ID'] = $row['Cust_ID'];
                header("Location: index.php");
                exit;
            } else {
                $message = "Wrong password.";
            }
        } else {
            $message = "Email not found.";
        }
        $stmt->close();
    } elseif ($formType === 'register') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $password_raw = $_POST['password'];

        if ($name === "" || $phone === "" || $email === "" || $password_raw === "") {
            $message = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } else {
            $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("SELECT Cust_Email FROM customer WHERE Cust_Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Email already registered!";
            } else {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO customer (Cust_Name, Cust_PhoneNum, Cust_Email, Cust_Password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $phone, $email, $password_hashed);
                if ($stmt->execute()) {
                    $message = "Registration successful! Please log in.";
                    $formType = 'login'; // Show login form after register
                } else {
                    $message = "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href=" css/login.css" />
  <title>JJJ | Login & Registration</title>
  <style>
    .form-message {
      color: red;
      margin-bottom: 10px;
      text-align: center;
    }
    .success {
      color: green;
    }
  </style>
</head>
<body onload="setInitialForm()">
<div class="wrapper">
  <nav class="nav">
    <div class="nav-logo"><p>JJJ.com</p></div>
    <div class="nav-menu" id="navMenu">
      <ul>
        <li><a href="index.php" class="link active">Home</a></li>
        <li><a href="blog.html" class="link">Blog</a></li>
        <li><a href="service.html" class="link">Services</a></li>
        <li><a href="about.html" class="link">About</a></li>
      </ul>
    </div>
    <div class="nav-button">
      <button class="btn white-btn" id="loginBtn" onclick="loginForm()">Sign In</button>
      <button class="btn" id="registerBtn" onclick="registerForm()">Sign Up</button>
    </div>
    <div class="nav-menu-btn"><i class="bx bx-menu" onclick="myMenuFunction()"></i></div>
  </nav>

  <div class="form-box">
    <!-- Login Form -->
    <form class="login-container" id="login" method="POST" action="login.php">
      <input type="hidden" name="form_type" value="login">
      <div class="top">
        <span>Don't have an account? <a href="#" onclick="registerForm()">Sign Up</a></span>
        <header>Login</header>
      </div>
      <?php if ($formType === 'login' && $message): ?>
        <div class="form-message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <div class="input-box">
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        <i class="bx bx-user"></i>
      </div>
      <div class="input-box">
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <i class="bx bx-lock-alt"></i>
      </div>
      <div class="input-box">
        <input type="submit" class="submit" value="Sign In">
      </div>
      <div style="text-align: center; margin-top: 10px;">
  <a href="forgot_password.php">Forgot Password?</a>
</div>

    </form>

    <!-- Register Form -->
    <form class="register-container" id="register" method="POST" action="login.php">
      <input type="hidden" name="form_type" value="register">
      <div class="top">
        <span>Have an account? <a href="#" onclick="loginForm()">Login</a></span>
        <header>Sign Up</header>
      </div>
      <?php if ($formType === 'register' && $message): ?>
        <div class="form-message <?= ($message === 'Registration successful! Please log in.') ? 'success' : '' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>
      <div class="two-forms">
        <div class="input-box">
          <input type="text" name="name" class="input-field" placeholder="Name" required>
          <i class="bx bx-user"></i>
        </div>
        <div class="input-box">
          <input type="text" name="phone" class="input-field" placeholder="Phone Number" required>
          <i class="bx bx-phone"></i>
        </div>
      </div>
      <div class="input-box">
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        <i class="bx bx-envelope"></i>
      </div>
      <div class="input-box">
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        <i class="bx bx-lock-alt"></i>
      </div>
      <div class="input-box">
        <input type="submit" class="submit" value="Register">
      </div>
    </form>
  </div>
</div>
<script src="js/login.js"></script>
</body>
</html>

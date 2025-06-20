<?php
session_start();
if (!isset($_SESSION['Cust_ID'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';
$cust_id = $_SESSION['Cust_ID'];

$stmt = $conn->prepare("SELECT Cust_Name, Cust_Email, Cust_PhoneNum FROM customer WHERE Cust_ID = ?");
$stmt->bind_param("i", $cust_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>マイプロフィール - My Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Jost&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      scroll-behavior: smooth;
      font-family: "Jost", sans-serif;
      list-style: none;
      text-decoration: none;
    }

    body {
      background: url('https://images.unsplash.com/photo-1587620962725-abab7fe55159') no-repeat center center fixed;
      background-size: cover;
      color: #333;
    }

    .container {
      max-width: 500px;
      margin: 80px auto;
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      padding: 40px;
      text-align: center;
    }

    h1 {
      font-size: 2.2em;
      margin-bottom: 8px;
      color: #d6336c;
    }

    .japanese-text {
      font-size: 1em;
      color: #666;
      margin-bottom: 20px;
    }

    .info-box {
      text-align: left;
    }

    .info-box p {
      margin: 12px 0;
      font-size: 1.1em;
    }

    .logout-btn, .edit-btn {
      background-color: #f26a8d;
      color: white;
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 1em;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
      margin-right: 10px;
    }

    .logout-btn:hover, .edit-btn:hover {
      background-color: #d94c70;
    }

    /* Modal styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .modal-content h2 {
      margin-bottom: 20px;
      color: #d6336c;
    }

    .modal-content input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .modal-content button {
      margin-top: 15px;
      padding: 10px 20px;
      background-color: #dea447;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    .modal-content button:hover {
      background-color: #c39234;
    }

    .close {
      float: right;
      font-size: 24px;
      font-weight: bold;
      color: #aaa;
      cursor: pointer;
    }

    .close:hover {
      color: #333;
    }

    .back-btn {
  position: absolute;
  top: 20px;
  left: 20px;
  background-color: #f7a1c4;
  color: white;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 0.95em;
  text-decoration: none;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  transition: background-color 0.3s ease;
}

.back-btn:hover {
  background-color: #d9709c;
}

  </style>
</head>
<body>
<a href="index.php" class="back-btn">← Back to Shopping</a>
<div class="container">

  <div class="container">
    <h1>ようこそ, <?= htmlspecialchars($user['Cust_Name']) ?> さん</h1>
    <div class="japanese-text">Welcome to your profile!</div>

    <div class="info-box">
      <p><strong>Email:</strong> <?= htmlspecialchars($user['Cust_Email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($user['Cust_PhoneNum']) ?></p>
    </div>

    <button class="edit-btn" onclick="document.getElementById('editModal').style.display='block'">編集 - Edit</button>
    <a href="logout.php"><button class="logout-btn">ログアウト - Logout</button></a>
  </div>

  <!-- Modal for Edit Profile -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
      <h2>Edit Profile</h2>
      <form action="updateprofile.php" method="POST">
        <input type="text" name="Cust_Name" value="<?= htmlspecialchars($user['Cust_Name']) ?>" placeholder="Name" required>
        <input type="email" name="Cust_Email" value="<?= htmlspecialchars($user['Cust_Email']) ?>" placeholder="Email" required>
        <input type="text" name="Cust_PhoneNum" value="<?= htmlspecialchars($user['Cust_PhoneNum']) ?>" placeholder="Phone" required>
        <input type="password" name="Cust_Password" placeholder="New Password (leave blank to keep current)">
        <button type="submit">Save Changes</button>
      </form>
    </div>
  </div>

  <a href="index.php" class="back-btn">← Back to Shopping</a>

  <script>
    window.onclick = function(event) {
      const modal = document.getElementById('editModal');
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>
</body>
</html>
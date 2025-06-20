<?php
session_start();
if (!isset($_SESSION['Cust_ID'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$cust_id = $_SESSION['Cust_ID'];
$name = $_POST['Cust_Name'];
$email = $_POST['Cust_Email'];
$phone = $_POST['Cust_PhoneNum'];
$password = $_POST['Cust_Password'];

// Update with or without password
if (!empty($password)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE customer SET Cust_Name=?, Cust_Email=?, Cust_PhoneNum=?, Cust_Password=? WHERE Cust_ID=?");
    $stmt->bind_param("ssssi", $name, $email, $phone, $hashed, $cust_id);
} else {
    $stmt = $conn->prepare("UPDATE customer SET Cust_Name=?, Cust_Email=?, Cust_PhoneNum=? WHERE Cust_ID=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $cust_id);
}

if ($stmt->execute()) {
    header("Location: userprofile.php"); // or wherever your profile file is
} else {
    echo "Update failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

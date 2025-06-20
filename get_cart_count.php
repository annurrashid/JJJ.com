<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$cust_id = $_SESSION['Cust_ID'] ?? null;
if (!$cust_id) {
    echo json_encode(['count' => 0]);
    exit;
}

$order_id = $_SESSION['last_order_id'] ?? 0;

$stmt = $conn->prepare("SELECT SUM(Quantity) AS count FROM order_items WHERE Order_ID = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$count = $data['count'] ?? 0;

echo json_encode(['count' => $count]);
exit;

<?php
require 'db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// ✅ Step 0: Require login
if (!isset($_SESSION['Cust_ID'])) {
    header("Location: login.php");
    exit;
}

$cust_id = $_SESSION['Cust_ID']; // replace $_SESSION['user_id']


// Step 1: Create a new Order if not exist in session or order was deleted
$order_id = $_SESSION['last_order_id'] ?? null;
$order_exists = false;

if ($order_id) {
    // Check if the order ID exists in DB
    $check_order = $conn->prepare("SELECT 1 FROM orders WHERE Order_ID = ?");
    $check_order->bind_param("i", $order_id);
    $check_order->execute();
    $check_order->store_result();
    $order_exists = $check_order->num_rows > 0;
    $check_order->close();
}

if (!$order_id || !$order_exists) {
    // Create new order
    $createOrder = $conn->prepare("INSERT INTO orders (Cust_ID, Order_Date) VALUES (?, NOW())");
    $createOrder->bind_param("i", $cust_id);

    if ($createOrder->execute()) {
        $_SESSION['last_order_id'] = $conn->insert_id;
        $order_id = $_SESSION['last_order_id'];
    } else {
        echo json_encode(["error" => "Failed to create order"]);
        exit;
    }
}


$order_id = $_SESSION['last_order_id'];

// Step 2: Get POST data
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Step 3: Validate input
if (!$product_id || $quantity < 1) {
    echo json_encode(["error" => "Invalid product ID or quantity"]);
    exit;
}

// Step 4: Get product price
$stmt = $conn->prepare("SELECT Product_Name, Product_Price FROM products WHERE Product_ID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Product not found"]);
    exit;
}

$product = $result->fetch_assoc();
$unit_price = floatval($product['Product_Price']);

// Step 5: Check if item already in cart
$check = $conn->prepare("SELECT Order_Item_ID, Quantity FROM order_items WHERE Order_ID = ? AND Product_ID = ?");
$check->bind_param("ii", $order_id, $product_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Update existing item quantity
    $row = $res->fetch_assoc();
    $new_qty = $row['Quantity'] + $quantity;

    $update = $conn->prepare("UPDATE order_items SET Quantity = ? WHERE Order_Item_ID = ?");
    $update->bind_param("ii", $new_qty, $row['Order_Item_ID']);
    if ($update->execute()) {
        echo json_encode(["success" => "Item quantity updated"]);
    } else {
        echo json_encode(["error" => "Failed to update item"]);
    }
} else {
    // Insert new item
    $insert = $conn->prepare("INSERT INTO order_items (Order_ID, Product_ID, Quantity, Unit_Price) VALUES (?, ?, ?, ?)");
    $insert->bind_param("iiid", $order_id, $product_id, $quantity, $unit_price);

    if ($insert->execute()) {
        header("Location: cart.php?success=1");
        exit();
    } else {
        echo "Error: " . $insert->error;
    }
    $insert->close();    
}
?>

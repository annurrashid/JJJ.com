<?php
require 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_item_id'])) {
    $order_item_id = $_POST['order_item_id'];

    // Step 1: Delete any related payment first
    $deletePayment = $conn->prepare("DELETE FROM payments WHERE Order_Item_ID = ?");
    $deletePayment->bind_param("i", $order_item_id);
    $deletePayment->execute();

    // Step 2: Delete the cart item
    $stmt = $conn->prepare("DELETE FROM order_items WHERE Order_Item_ID = ?");
    $stmt->bind_param("i", $order_item_id);
    
    if ($stmt->execute()) {
        header("Location: cart.php?deleted=1");
        exit;
    } else {
        echo "Error deleting item.";
    }
}
?>

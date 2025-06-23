<?php
require 'db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['order_item_id'], $_POST['quantity'])) {
        header("Location: cart.php");
        exit;
    }

    $order_item_id = intval($_POST['order_item_id']);
    $new_quantity = intval($_POST['quantity']);

    // Get Product_ID linked to this order item
    $stmt = $conn->prepare("SELECT Product_ID FROM order_items WHERE Order_Item_ID = ?");
    $stmt->bind_param("i", $order_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $product_id = $row['Product_ID'];

        // Get Product_Stock for that product
        $stmt2 = $conn->prepare("SELECT Product_Stock FROM products WHERE Product_ID = ?");
        $stmt2->bind_param("i", $product_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        if ($row2 = $result2->fetch_assoc()) {
            $max_stock = intval($row2['Product_Stock']);

            // Ensure quantity between 1 and max_stock
            if ($new_quantity < 1) {
                $new_quantity = 1;
            } elseif ($new_quantity > $max_stock) {
                $new_quantity = $max_stock;
            }

            // Update quantity in order_items
            $stmt3 = $conn->prepare("UPDATE order_items SET Quantity = ? WHERE Order_Item_ID = ?");
            $stmt3->bind_param("ii", $new_quantity, $order_item_id);
            $stmt3->execute();
        }
    }
}

// Redirect back to cart page after update
header("Location: cart.php");
exit;
?>

<?php
require 'db.php';
require 'vendor/autoload.php'; // PHPMailer autoload
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $address = trim($_POST['address']);
    $payment_method = $_POST['payment-method'];
    $amount = floatval($_POST['payment-amount']);
    $cust_id = intval($_POST['cust-id']);
    $order_id = intval($_POST['order-id']);

    $valid_methods = ['Credit Card', 'Bank Transfer'];
    if (!in_array($payment_method, $valid_methods)) {
        die("Invalid payment method.");
    }

    // Determine payment and order status
    $payment_status = 'Completed';
    $order_status = 'Pending';


    // Calculate total from order_items
    $total_stmt = $conn->prepare("SELECT SUM(Quantity * Unit_Price) AS total FROM order_items WHERE Order_ID = ?");
    $total_stmt->bind_param("i", $order_id);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_row = $total_result->fetch_assoc();
    $total_amount = $total_row['total'] ?? 0.0;
    $total_stmt->close();

    // Update total amount in orders
    $update_total_stmt = $conn->prepare("UPDATE orders SET Total_Amount = ? WHERE Order_ID = ?");
    $update_total_stmt->bind_param("di", $total_amount, $order_id);
    $update_total_stmt->execute();
    $update_total_stmt->close();

    // Insert payment
    // Loop through all items in the order
    $item_stmt = $conn->prepare("SELECT Order_Item_ID, Quantity, Unit_Price FROM order_items WHERE Order_ID = ?");
    $item_stmt->bind_param("i", $order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();

    while ($item = $item_result->fetch_assoc()) {
        $order_item_id = $item['Order_Item_ID'];
        $item_amount = $item['Quantity'] * $item['Unit_Price'];

        // Insert payment per item
        $payment_stmt = $conn->prepare("INSERT INTO payments (Cust_ID, Order_Item_ID, Payment_Amount, Payment_Method, Payment_Status) VALUES (?, ?, ?, ?, ?)");
        $payment_stmt->bind_param("iisss", $cust_id, $order_item_id, $item_amount, $payment_method, $payment_status);
        $payment_stmt->execute();
        $payment_stmt->close();
    }

    $item_stmt->close();

  if ($payment_method && $payment_status) {
        // Deduct stock from each product in this order
        $order_items_stmt = $conn->prepare("SELECT Product_ID, Quantity FROM order_items WHERE Order_ID = ?");
        $order_items_stmt->bind_param("i", $order_id);
        $order_items_stmt->execute();
        $order_items_result = $order_items_stmt->get_result();

        while ($item = $order_items_result->fetch_assoc()) {
            $product_id = $item['Product_ID'];
            $qty_ordered = $item['Quantity'];

            // Deduct quantity from product stock
            $update_stock_stmt = $conn->prepare("UPDATE products SET Product_Stock = Product_Stock - ? WHERE Product_ID = ?");
            $update_stock_stmt->bind_param("ii", $qty_ordered, $product_id);
            $update_stock_stmt->execute();
            $update_stock_stmt->close();
        }

        $order_items_stmt->close();


        // If payment is completed, update order status
        if ($payment_status === 'Completed') {
            $update_order_stmt = $conn->prepare("UPDATE orders SET Order_Status = ? WHERE Order_ID = ?");
            $update_order_stmt->bind_param("si", $order_status, $order_id);
            $update_order_stmt->execute();
            $update_order_stmt->close();
        }

        // Get customer email and name
        $cust_stmt = $conn->prepare("SELECT Cust_Name, Cust_Email FROM customer WHERE Cust_ID = ?");
        $cust_stmt->bind_param("i", $cust_id);
        $cust_stmt->execute();
        $cust_result = $cust_stmt->get_result();
        $cust_data = $cust_result->fetch_assoc();
        $customer_email = $cust_data['Cust_Email'];
        $customer_name = $cust_data['Cust_Name'];
        $cust_stmt->close();

        

        // Send email receipt
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anakindatinwalker@gmail.com';
            $mail->Password   = 'empxabefjnagpnpw';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('anakindatinwalker@gmail.com', 'JJJ.com');
            $mail->addAddress($customer_email, $customer_name);
            $mail->isHTML(true);
            $mail->Subject = 'JJJ.com - Order Receipt #' . $order_id;
          // Get order items for breakdown
$items_stmt = $conn->prepare("
SELECT p.Product_Name, oi.Quantity, oi.Unit_Price 
FROM order_items oi 
JOIN products p ON oi.Product_ID = p.Product_ID 
WHERE oi.Order_ID = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$item_rows_html = '';
while ($item = $items_result->fetch_assoc()) {
$subtotal = $item['Quantity'] * $item['Unit_Price'];
$item_rows_html .= '
    <tr>
        <td style="padding: 8px; border: 1px solid #ccc;">' . htmlspecialchars($item['Product_Name']) . '</td>
        <td style="padding: 8px; border: 1px solid #ccc;">' . $item['Quantity'] . '</td>
        <td style="padding: 8px; border: 1px solid #ccc;">RM ' . number_format($item['Unit_Price'], 2) . '</td>
        <td style="padding: 8px; border: 1px solid #ccc;">RM ' . number_format($subtotal, 2) . '</td>
    </tr>';
}
$items_stmt->close();

$mail->Body = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; background-color: #fff; padding: 20px;">
    <div style="text-align: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
        <h2 style="color: #3C4C6A; margin: 0;">JJJ.com</h2>
        <p style="color: #888; font-size: 14px;">Formal Purchase Receipt</p>
    </div>
    <p style="font-size: 16px; color: #3C4C6A;">Dear <strong>' . htmlspecialchars($customer_name) . '</strong>,</p>
    <p style="font-size: 15px; color: #3C4C6A;">Thank you for your purchase. Below is a summary of your order:</p>

    <h3 style="margin-top: 20px; color: #3C4C6A;">Order Details</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="padding: 10px; border: 1px solid #ccc;">Product</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Quantity</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Unit Price</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            ' . $item_rows_html . '
            <tr>
                <td colspan="3" style="padding: 10px; border: 1px solid #ccc; text-align: right;"><strong>Total Paid</strong></td>
                <td style="padding: 10px; border: 1px solid #ccc;"><strong>RM ' . number_format($amount, 2) . '</strong></td>
            </tr>
        </tbody>
    </table>

    <p><strong>Order ID:</strong> ' . htmlspecialchars($order_id) . '<br>
    <strong>Payment Method:</strong> ' . htmlspecialchars($payment_method) . '<br>
    <strong>Status:</strong> ' . htmlspecialchars($payment_status) . '</p>

    <h4 style="color: #3C4C6A;">Shipping Address</h4>
    <p style="margin: 0; color: #3C4C6A;">' . nl2br(htmlspecialchars($address)) . '</p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    <p style="font-size: 14px; color: #888;">For any questions, contact us at 
    <a href="mailto:support@jjj.com" style="color: #E27D7D;">support@jjj.com</a>.</p>
    <p style="font-size: 14px; color: #888;">Thank you for shopping at <strong>JJJ.com</strong>!</p>
</div>';

            $mail->AltBody = 'Thank you for your purchase! Your order ID is ' . $order_id . '. Amount paid: RM ' . number_format($amount, 2) . '.';
            $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
        }

        unset($_SESSION['last_order_id']);
        echo "<script>
            localStorage.removeItem('cart');
            alert('Payment successful! A receipt has been sent to your email.');
            window.location.href = 'index.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
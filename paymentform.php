<?php
require 'db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in and has a last order ID
if (!isset($_SESSION['Cust_ID']) || !isset($_SESSION['last_order_id'])) {
    header("Location: cart.php"); // Redirect to cart if not logged in or no order
    exit();
}

$order_id = $_SESSION['last_order_id'];

// Fetch cart items for the order
$stmt = $conn->prepare("
    SELECT 
        oi.Order_Item_ID,
        oi.Quantity, 
        oi.Unit_Price, 
        p.Product_Name 
    FROM order_items oi 
    JOIN products p ON oi.Product_ID = p.Product_ID 
    WHERE oi.Order_ID = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $row_total = $row['Quantity'] * $row['Unit_Price'];
    $total += $row_total;
    $items[] = array_merge($row, ['Row_Total' => $row_total]);
}


$cust_name = '';
$cust_id = $_SESSION['Cust_ID'];

$stmtCust = $conn->prepare("SELECT Cust_Name FROM customer WHERE Cust_ID = ?");
$stmtCust->bind_param("i", $cust_id);
$stmtCust->execute();
$resultCust = $stmtCust->get_result();
if ($rowCust = $resultCust->fetch_assoc()) {
    $cust_name = $rowCust['Cust_Name'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment</title>
    <style>
    :root {
        --bg-soft-mustard: #f6e3b3;
        --text-muted-navy: #3c4c6a;
        --accent-soft-coral: #e27d7d;
        --highlight-pale-sand: #fff8e7;
        --text-misty-grey: #d3d3d3;
        --hover-dusty-orange: #e4b97f;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--bg-soft-mustard);
        color: var(--text-muted-navy);
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 40px 20px;
    }

    .payment-container {
        background: var(--highlight-pale-sand);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        width: 420px;
        padding: 32px;
        box-sizing: border-box;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .back-btn {
        display: inline-block;
        margin-bottom: 16px;
        font-size: 0.9rem;
        color: var(--text-muted-navy);
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        border-radius: 6px;
        padding: 6px 12px;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: var(--hover-dusty-orange);
        color: white;
    }

    h1 {
        margin-top: 0;
        font-weight: 600;
        font-size: 1.8rem;
        text-align: center;
        color: var(--text-muted-navy);
        margin-bottom: 24px;
    }

    .summary {
        margin-bottom: 24px;
        font-size: 1rem;
        color: var(--text-muted-navy);
        background: var(--bg-soft-mustard);
        border-radius: 10px;
        padding: 16px;
        line-height: 1.6;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    label {
        font-size: 0.95rem;
        margin-bottom: 4px;
    }

    input,
    select {
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid var(--text-misty-grey);
        font-size: 1rem;
        background: #fff;
        transition: border 0.2s ease;
    }

    input:focus,
    select:focus {
        border-color: var(--accent-soft-coral);
        outline: none;
        box-shadow: 0 0 0 3px rgba(226, 125, 125, 0.2);
    }

    .pay-btn {
        background-color: var(--accent-soft-coral);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 12px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .pay-btn:hover {
        background-color: var(--hover-dusty-orange);
    }

    .success-message {
        color: #2e7d32;
        text-align: center;
        margin-top: 18px;
        font-weight: 500;
    }
    </style>
</head>
<body>
    <div class="payment-container" role="region" aria-label="Payment Form">
        <a href="cart.php" class="back-btn" aria-label="Back to Cart">← Back to Cart</a>
        <h1>Payment</h1>
        <div class="summary" id="order-summary">
            <strong>Order Summary:</strong><br>
            <?php foreach ($items as $item): ?>
                <div><?= htmlspecialchars($item['Product_Name']) ?> (<?= $item['Quantity'] ?>) - RM <?= number_format($item['Row_Total'], 2) ?></div>
            <?php endforeach; ?>
            <strong>Total: RM <?= number_format($total, 2) ?></strong>
        </div>

        <form id="payment-form" method="POST" action="process_payment.php" autocomplete="off">
            <input type="hidden" name="payment-amount" id="payment-amount" value="<?= number_format($total, 2) ?>" />
            <input type="hidden" name="cust-id" id="cust-id" value="<?= $_SESSION['Cust_ID'] ?>" />
            <input type="hidden" name="order-id" id="order-id" value="<?= $order_id ?>" />

            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" required value="<?= htmlspecialchars($cust_name) ?>" />


            <label for="address">Shipping Address</label>
            <input type="text" id="address" name="address" required />

            <label for="payment-method">Payment Method</label>
            <select id="payment-method" name="payment-method" required>
                <option value="">Select</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <div id="credit-card-fields" style="display: none">
                <label for="card-number">Card Number</label>
                <input type="text" id="card-number" maxlength="19" placeholder="1234 5678 9012 3456" />

                <label for="expiry">Expiry Date</label>
                <input type="text" id="expiry" maxlength="5" placeholder="MM/YY" />

                <label for="cvv">CVV</label>
                <input type="text" id="cvv" maxlength="4" placeholder="123" />
            </div>

            <button type="submit" class="pay-btn">Pay Now</button>
        </form>
        <div class="success-message" id="success-message" style="display: none">
            Payment successful! Thank you for your purchase.
        </div>
    </div>
    <script>
        // Show/hide credit card fields based on payment method
        document.getElementById("payment-method").addEventListener("change", function () {
            document.getElementById("credit-card-fields").style.display = this.value === "Credit Card" ? "block" : "none";
        });
    </script>
</body>
</html>

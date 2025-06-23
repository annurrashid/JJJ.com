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
  <link href="https://fonts.googleapis.com/css2?family=Jost&display=swap" rel="stylesheet" />
  <style>
    :root {
      --pink: #f472b6;
      --yellow: #fcd34d;
      --white-glass: rgba(255, 255, 255, 0.1);
      --text-light: #f3f4f6;
      --shadow: rgba(0, 0, 0, 0.2);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Jost", sans-serif;
    }

    body {
      background: url("images/bg2.jpg") center center / cover no-repeat fixed;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
      color: var(--text-light);
    }

    .overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(6px);
      z-index: -1;
    }

    .payment-container {
      background: var(--white-glass);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 32px;
      border-radius: 20px;
      width: 100%;
      max-width: 460px;
      box-shadow: 0 8px 30px var(--shadow);
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h1 {
      text-align: center;
      font-size: 1.8rem;
      margin-bottom: 24px;
      color: var(--pink);
    }

    .back-btn {
      display: inline-block;
      margin-bottom: 16px;
      font-size: 0.9rem;
      color: var(--text-light);
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      transition: 0.3s;
    }

    .back-btn:hover {
      background-color: var(--pink);
      color: white;
    }

    .summary {
      margin-bottom: 24px;
      background: rgba(255, 255, 255, 0.05);
      padding: 16px;
      border-radius: 12px;
      font-size: 0.95rem;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    label {
      font-size: 0.9rem;
    }

    input, select {
        padding: 10px;
        border-radius: 10px;
        border: none;
        background: rgba(255, 255, 255, 0.15);
        color: white;
        font-size: 1rem;
    }

    input::placeholder {
        color: #ddd;
    }

    input:focus, select:focus {
        outline: none;
        border: 2px solid var(--pink);
    }

    /* Enhanced style for the dropdown */
    select.payment-method {
        padding-left: 45px; /* for spacing before text */
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        border-radius: 12px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml;utf8,<svg fill='white' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
    }

    select.payment-method option {
        background-color: #333;
        color: #fff;
    }

    .pay-btn {
      background-color: var(--pink);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .pay-btn:hover {
      background-color: #ec4899;
    }

    #credit-card-fields input {
      background: rgba(255,255,255,0.15);
      color: white;
    }

    .success-message {
      text-align: center;
      color: #bbf7d0;
      margin-top: 16px;
    }
  </style>
</head>
<body>
  <div class="overlay"></div>

  <div class="payment-container" role="region" aria-label="Payment Form">
    <a href="cart.php" class="back-btn">← Back to Cart</a>
    <h1>Payment</h1>

    <div class="summary">
      <strong>Order Summary:</strong><br>
      <?php foreach ($items as $item): ?>
        <div><?= htmlspecialchars($item['Product_Name']) ?> (<?= $item['Quantity'] ?>) - RM <?= number_format($item['Row_Total'], 2) ?></div>
      <?php endforeach; ?>
      <strong>Total: RM <?= number_format($total, 2) ?></strong>
    </div>

    <form method="POST" action="process_payment.php" autocomplete="off">
      <input type="hidden" name="payment-amount" value="<?= number_format($total, 2) ?>" />
      <input type="hidden" name="cust-id" value="<?= $_SESSION['Cust_ID'] ?>" />
      <input type="hidden" name="order-id" value="<?= $order_id ?>" />

      <label for="fullname">Full Name</label>
      <input type="text" id="fullname" name="fullname" required value="<?= htmlspecialchars($cust_name) ?>" placeholder="e.g. Hana Tanaka" />

      <label for="address">Shipping Address</label>
      <input type="text" id="address" name="address" required placeholder="Enter your address" />

      <label for="payment-method">Payment Method</label>
      <select id="payment-method" name="payment-method" class="payment-method" required>
        <option value="">Select Payment Method</option>
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
    document.getElementById("payment-method").addEventListener("change", function () {
      document.getElementById("credit-card-fields").style.display = this.value === "Credit Card" ? "block" : "none";
    });
  </script>
</body>
</html>

<?php
require 'db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['last_order_id'])) {
    $order_id = $_SESSION['last_order_id'];
} else {
    // Try to find the latest uncompleted order for this user
    if (isset($_SESSION['Cust_ID'])) {
        $cust_id = $_SESSION['Cust_ID'];
        $stmt = $conn->prepare("SELECT Order_ID FROM orders WHERE Cust_ID = ? ORDER BY Order_Date DESC LIMIT 1");
        $stmt->bind_param("i", $cust_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $order_id = $row['Order_ID'];
            $_SESSION['last_order_id'] = $order_id; // Restore into session
        } else {
            $order_id = null; // No cart found
        }
    } else {
        $order_id = null; // User not logged in
    }
}

// Handle delete success message
$deleteSuccess = isset($_GET['deleted']) && $_GET['deleted'] == '1';

// Fetch cart items
$stmt = $conn->prepare("
    SELECT 
        oi.Order_Item_ID,
        oi.Quantity, 
        oi.Unit_Price, 
        p.Product_Name, 
        p.Product_Image,
        p.Product_Stock
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
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <script src="https://cdn.tailwindcss.com"></script>
   <style>
      body {
        background: url("images/bg2.jpg") center center / cover no-repeat fixed;
        overflow: hidden;
      }
      .overlay {
        background-color: rgba(0, 0, 0, 0.4); /* Dark semi-transparent */
        backdrop-filter: blur(1px);
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
      }

      button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
  </style>
</head>
<body class="relative text-white">
  
  <!-- Dark Overlay -->
  <div class="overlay"></div>
  
  <!-- Main Content -->
   <div class="max-w-4xl mx-auto p-6 relative z-10">
    <a href="index.php" class="inline-block mb-4 text-pink-400 hover:text-pink-300 font-semibold">
      &larr; Back to Products
    </a>

    <h1 class="text-3xl font-bold mb-6">Your Shopping Cart</h1>

    <?php if (isset($_GET['success'])): ?>
      <div class="bg-white/20 backdrop-blur text-green-200 p-4 mb-4 rounded-lg border border-green-400">Item successfully added to cart!</div>
    <?php endif; ?>

    <?php if ($deleteSuccess): ?>
      <div class="bg-white/20 backdrop-blur text-red-200 p-4 mb-4 rounded-lg border border-red-400">Item removed from cart.</div>
    <?php endif; ?>

    <?php if (count($items) > 0): ?>
      <div class="space-y-4">
        <?php foreach ($items as $item): ?>
          <div class="border border-white/20 bg-white/10 backdrop-blur p-4 rounded-lg shadow-sm flex justify-between items-start">
            
            <div class="flex items-start gap-4 max-w-xs">
              <img src="product_image/<?= htmlspecialchars($item['Product_Image']) ?>" 
                  onerror="this.onerror=null; this.src='product_image/placeholder.jpg';" 
                  class="w-16 h-16 object-cover rounded-lg border" 
                  alt="<?= htmlspecialchars($item['Product_Name'] ?? 'Product') ?>">
              <div>
                <h4 class="text-lg font-medium"><?= htmlspecialchars($item['Product_Name']) ?></h4>
                <div class="text-white font-bold mt-1">
                  RM <?= number_format($item['Row_Total'], 2) ?>
                </div>
              </div>
            </div>

            <div class="flex flex-col items-end gap-2">
              <div class="flex items-center gap-4">
                <form method="POST" action="update_quantity.php" class="inline-flex">
                  <input type="hidden" name="order_item_id" value="<?= $item['Order_Item_ID'] ?>">
                  <input type="hidden" name="quantity" value="<?= max(1, $item['Quantity'] - 1) ?>">
                  <button type="submit" <?= $item['Quantity'] <= 1 ? 'disabled' : '' ?> 
                    class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded select-none">-</button>
                </form>

                <div class="text-white font-semibold select-none">
                  <?= $item['Quantity'] ?>
                </div>

                <form method="POST" action="update_quantity.php" class="inline-flex">
                  <input type="hidden" name="order_item_id" value="<?= $item['Order_Item_ID'] ?>">
                  <input type="hidden" name="quantity" value="<?= min($item['Product_Stock'], $item['Quantity'] + 1) ?>">
                  <button type="submit" <?= $item['Quantity'] >= $item['Product_Stock'] ? 'disabled' : '' ?> 
                    class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded select-none">+</button>
                </form>
              </div>

              <form method="POST" action="deletecartitem.php" onsubmit="return confirm('Remove this item from cart?');">
                <input type="hidden" name="order_item_id" value="<?= $item['Order_Item_ID'] ?>">
                <button type="submit" class="text-sm text-red-300 hover:text-red-500">Remove</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="border-t border-white/20 pt-4 mt-6 flex justify-between items-center">
        <p class="text-xl font-bold">
          Total: RM <?= number_format($total, 2) ?>
        </p>
        <div>
          <a href="paymentform.php?order_id=<?= $order_id ?>"
             class="bg-pink-400 hover:bg-pink-500 text-white px-6 py-2 rounded-lg shadow">
             Proceed to Payment
          </a>
          <a href="index.php#trending-all"
             class="ml-4 bg-white text-gray-800 hover:bg-gray-200 px-6 py-2 rounded-lg shadow">
             Continue Shopping
          </a>
        </div>
      </div>
    <?php else: ?>
      <p class="text-gray-300 mt-4">Your cart is empty.</p>
    <?php endif; ?>
  </div> 
</body>
</html>

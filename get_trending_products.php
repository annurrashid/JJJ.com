<?php
include 'db.php';

// Fetch 30 random active products
$trendingSql = "SELECT * FROM products WHERE Product_Status = 'active' ORDER BY RAND() LIMIT 8";
$trendingResult = $conn->query($trendingSql);

if ($trendingResult && $trendingResult->num_rows > 0):
  while($row = $trendingResult->fetch_assoc()):
?>
  <div class="row">
    <img src="product_image/<?php echo htmlspecialchars($row['Product_Image']); ?>" alt="<?php echo htmlspecialchars($row['Product_Name']); ?>">
    <div class="product-text">
      <h5><?php echo ((int)$row['Product_Stock'] <= 0) ? "Out of Stock" : "Sale"; ?></h5>
    </div>
    <div class="heart-icon" onclick="toggleLike(this)">
  <i class="bx bx-heart"></i>
</div>


    <div class="price">
      <h4><?php echo htmlspecialchars($row['Product_Name']); ?></h4>
      <p>RM <?php echo number_format((float)$row['Product_Price'], 2); ?></p>
    </div>
    <form method="post" action="addtocart.php">
      <input type="hidden" name="product_id" value="<?php echo $row['Product_ID']; ?>">
      <input type="hidden" name="quantity" value="1">
      <button type="submit" class="add-to-cart-btn" <?php if ((int)$row['Product_Stock'] <= 0) echo 'disabled'; ?>>Add to Cart</button>
    </form>
  </div>
<?php
  endwhile;
else:
  echo "<p style='text-align:center;'>No trending products available.</p>";
endif;
?>

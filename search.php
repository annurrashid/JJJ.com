<?php
include 'db.php';



$search = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($search === '') {
  echo "<p>Please enter a search keyword.</p>";
  exit;
}

$sql = "SELECT * FROM products WHERE Product_Status = 'active' AND Product_Name LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = '%' . $search . '%';
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Results</title>
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Jost', sans-serif;
      margin: 0;
      padding: 0;
      background: #f9f9f9;
      color: #333;
    }
    .container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 0 20px;
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 28px;
      color: #444;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      color: #e27d7d;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .back-btn:hover {
      color: #c25b5b;
    }
    .products {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 30px;
    }
    .row {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.05);
      overflow: hidden;
      transition: transform 0.3s ease;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .row:hover {
      transform: translateY(-5px);
    }
    .row img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .product-text {
      padding: 10px;
      font-size: 14px;
      color: #888;
    }
    .ratting, .price {
      padding: 0 10px;
    }
    .ratting i {
      color: #f7c94b;
    }
    .price h4 {
      margin: 10px 0 5px;
      font-size: 16px;
      font-weight: 500;
    }
    .price p {
      margin: 0;
      color: #e27d7d;
      font-weight: bold;
    }
    .add-to-cart {
      margin: 10px;
      padding: 8px;
      background-color: #e27d7d;
      color: white;
      text-align: center;
      border-radius: 6px;
      font-size: 14px;
      border: none;
      cursor: pointer;
      width: 100%;
      transition: background-color 0.3s ease;
    }
    .add-to-cart:hover {
      background-color: #c25b5b;
    }
    p.no-result {
      text-align: center;
      font-size: 18px;
      margin-top: 40px;
      color: #777;
    }
    form {
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php" class="back-btn"><i class="bx bx-arrow-back"></i> Back to Home</a>
    <h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
    <div class="products">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="row">
            <img src="product_image/<?php echo htmlspecialchars($row['Product_Image']); ?>" alt="<?php echo htmlspecialchars($row['Product_Name']); ?>">
            <div class="product-text">
              <h5><?php echo ((int)$row['Product_Stock'] <= 0) ? "Out of Stock" : "Sale"; ?></h5>
            </div>
            <div class="ratting">
              <i class="bx bx-star"></i>
              <i class="bx bx-star"></i>
              <i class="bx bx-star"></i>
              <i class="bx bx-star"></i>
              <i class="bx bxs-star-half"></i>
            </div>
            <div class="price">
              <h4><?php echo htmlspecialchars($row['Product_Name']); ?></h4>
              <p>RM <?php echo number_format((float)$row['Product_Price'], 2); ?></p>
            </div>

            <?php if ((int)$row['Product_Stock'] > 0): ?>
              <form action="addtocart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $row['Product_ID']; ?>">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="add-to-cart">
                  <i class="bx bx-cart"></i> Add to Cart
                </button>
              </form>
            <?php else: ?>
              <div class="add-to-cart" style="background-color: #ccc; cursor: not-allowed;">Out of Stock</div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="no-result">No products found matching your search.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

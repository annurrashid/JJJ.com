<?php
session_start();

// DEBUG: Show session data for troubleshooting
echo "<!-- SESSION DEBUG START\n";
print_r($_SESSION);
echo "\nSESSION DEBUG END -->";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JJJ.com</title>

  <!-- CSS -->
  <link rel="stylesheet" href="css/index.css" />

  <!-- Fonts and Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@100;200;300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" />
</head>
<body>
  <header>
    <!-- FIXED logo image path -->
    <a href="#contact-all" class="logo">
      <img src="images/logo.jpg" alt="JJJ Logo" />
    </a>

    <ul class="navmenu">
  <li><a href="#">Home</a></li>

  <li class="dropdown">
    <a href="#">Category</a>
    <ul class="dropdown-menu">
      <li><a href="#trending-men-clothing">Men</a></li>
      <li><a href="#trending-women-clothing">Women</a></li>
      <li><a href="#trending-baby-clothing">Baby</a></li>
      <li><a href="#trending-accessories">Accessories</a></li>
      <li><a href="#trending-bags">Bags</a></li>  
      <li><a href="#trending-footwear">Footwear</a></li>
      <li><a href="#trending-home-goods">Home Goods</a></li>
      <li><a href="#trending-toys">Toys</a></li>
    </ul>
  </li>

  <li class="dropdown">
    <a href="#trending-all">Shop</a>
  </li>
  
  <li><a href="blog.html">Blog</a></li>
  <li><a href="service.html">Services</a></li>
  <li><a href="about.html">About</a></li>
</ul>

<!-- In your navigation -->
<div class="nav-icon">
  <div class="search-container">
    <a href="#" id="search-toggle"><i class="bx bx-search"></i></a>
    <div class="search-dropdown" id="search-dropdown">
      <form action="search.php" method="get">
        <input type="text" name="query" placeholder="Search for products...">
      </form>
    </div>
  </div>

  <?php if (isset($_SESSION['Cust_ID'])): ?>
    <a href="userprofile.php"><i class="bx bx-user-circle"></i></a>
  <?php else: ?>
    <a href="login.php"><i class="bx bx-user"></i></a>
  <?php endif; ?>

  <a href="cart.php" class="cart-link">
    <i class="bx bx-cart"></i>
    <span class="cart-count">0</span>
  </a>
</div>

  </header>

    <section class="main-home">
      <div class="main-text">
        <h5>Spring Collection</h5>
        <h1>
          New Spring <br />
          Collection 2025
        </h1>
        <p>There's Nothing like Trend</p>

        <a href="#trending-all" class="main-btn"
          >Shop Now <i class="bx bx-right-arrow-alt"></i
        ></a>
      </div>

      <div class="down-arrow">
        <a href="#trending-all" class="down"><i class="bx bx-down-arrow-alt"></i
        ></a>
      </div>
    </section>

    <?php include 'db.php'; ?>
<section class="trending-product" id="trending-all">
  <div class="center-text">
    <h2>🔥 Trending Now</h2>
  </div>

  <div class="products" id="trending-products-container">
    <!-- Products will be loaded here -->
  </div>
</section>



<?php
// Correct mapping using category names from your database
$categories = [
  1 => 'Baby Clothing',
  2 => 'Women Clothing',
  3 => 'Men Clothing',
  4 => 'Accessories',
  5 => 'Bags',
  6 => 'FootWear',
  7 => 'Home Goods',
  8 => 'Toys',
];

foreach ($categories as $catID => $catName):
?>

<section class="trending-product" id="trending-<?php echo strtolower(str_replace(' ', '-', $catName)); ?>">
  <div class="center-text">
    <h2><?php echo $catName; ?></h2>
  </div>

  <div class="products">
    <?php
      // Randomize trending items within category
      $sql = "SELECT * FROM products WHERE Product_Status = 'active' AND Category_ID= $catID ORDER BY RAND() LIMIT 100";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0):
        while($row = $result->fetch_assoc()):
          $imagePath = "product_image/" . htmlspecialchars($row['Product_Image']);
          $imageAlt = htmlspecialchars($row['Product_Name']);
    ?>
    <div class="row">
      <img src="<?php echo $imagePath; ?>" alt="<?php echo $imageAlt; ?>">

      <div class="product-text">
        <h5><?php echo ((int)$row['Product_Stock'] <= 0) ? "Out of Stock" : "Sale"; ?></h5>
      </div>

      <div class="heart-icon" onclick="toggleLike(this)">
  <i class="bx bx-heart"></i>
</div>

<div class="rating">
  <i class="bx bx-star star" data-value="1"></i>
  <i class="bx bx-star star" data-value="2"></i>
  <i class="bx bx-star star" data-value="3"></i>
  <i class="bx bx-star star" data-value="4"></i>
  <i class="bx bx-star star" data-value="5"></i>
</div>



      <div class="price">
        <h4><?php echo $imageAlt; ?></h4>
        <p>RM <?php echo number_format((float)$row['Product_Price'], 2); ?></p>
      </div>

      <form method="post" action="addtocart.php">
        <input type="hidden" name="product_id" value="<?php echo $row['Product_ID']; ?>">
        <button type="submit" class="add-to-cart-btn" <?php if ((int)$row['Product_Stock'] <= 0) echo 'disabled'; ?>>
          Add to Cart
        </button>
      </form>
    </div>
    <?php endwhile; else: ?>
      <p style="text-align:center;">No <?php echo $catName; ?> products available.</p>
    <?php endif; ?>
  </div>
</section>

<?php endforeach; ?>

    <!--contact-section-->
    <section class="contact" id="contact-all">
      <div class="contact-info">
        <div class="first-info">
          <img src="images/Logo.jpg" alt="" />

          <p>
            C-3-07 Blok C Taman Pinggiran Delima, <br />
            43100 Hulu Langat, Selangor
          </p>
          <p>01130803309</p>
          <p>2023876504@student.uitm.edu.my</p>

          <div class="social-icon">
            <a href="https://www.facebook.com/JalanJalanJapan.Malaysia/"><i class="bx bxl-facebook"></i></a>
            <a href="#"><i class="bx bxl-twitter"></i></a>
            <a href="https://www.instagram.com/jalanjalanjapanofficial/?hl=en"><i class="bx bxl-instagram"></i></a>
            <a href="https://www.tiktok.com/@jalanjalanjapan?_t=ZS-8xPxfpnyq2r&_r=1"><i class="bx bxl-tiktok"></i></a>
            <a href="#"><i class="bx bxl-linkedin"></i></a>
          </div>
        </div>

        <div class="second-info">
          <h4>Support</h4>
          <p>Contact us</p>
          <p>About page</p>
          <p>Shopping & Returns</p>
          <p>Privacy</p>
        </div>

        <div class="third-info">
          <h4>Shop</h4>
          <p>Men's Shopping</p>
          <p>Women's Shopping</p>
          <p>Kids's Shopping</p>
          <p>Furniture</p>
          <p>Discount</p>
        </div>

        <div class="fourth-info">
          <h4>Company</h4>
          <p>About</p>
          <p>Blog</p>
          <p>Login</p>
        </div>

        <div class="five">
          <h4>Subcribe</h4>
          <p>
            Receive Updates, Hot Deals, Discounts Sent Straight In Your Inbox
          </p>
          <p>
            Get the latest fashion news and product launches just by subscribing to our newsletter.
          </p>
          <p>
            JJJ is a reliable and user-friendly platform that delivers quality products and services with efficiency and integrity.
          </p>
        </div>
      </div>
    </section>

    <div class="end-text">
      <p>
        Copyright &#169; 2025 JJJ.com. All Rights Reserved. Made by Mystic MIAH
      </p>
    </div>

    <script src="js/index.js"></script>
  </body>
</html>

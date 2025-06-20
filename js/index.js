const header = document.querySelector("header");

// Sticky header on scroll
window.addEventListener("scroll", function () {
  header.classList.toggle("sticky", window.scrollY > 0);
});

// Toggle nav menu icon
let menu = document.querySelector("#menu-icon");
let navmenu = document.querySelector(".navmenu");

console.log("Script is running!");

document.addEventListener("DOMContentLoaded", () => {
  const searchToggle = document.getElementById("search-toggle");
  const searchDropdown = document.getElementById("search-dropdown");

  searchToggle.addEventListener("click", function (e) {
    e.preventDefault();
    // Toggle visibility
    if (searchDropdown.style.display === "block") {
      searchDropdown.style.display = "none";
    } else {
      searchDropdown.style.display = "block";
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const categoryLink = document.querySelector('.dropdown > a[href="#"]'); // or use a better ID
  const dropdownItem = categoryLink.closest(".dropdown");

  categoryLink.addEventListener("click", function (e) {
    e.preventDefault();
    dropdownItem.classList.toggle("open");
  });

  // Optional: close if clicking outside
  document.addEventListener("click", function (e) {
    if (!dropdownItem.contains(e.target)) {
      dropdownItem.classList.remove("open");
    }
  });
});
function updateCartCount() {
  fetch("get_cart_count.php")
    .then((res) => res.json())
    .then((data) => {
      const cartCountElement = document.querySelector(".cart-count");
      if (cartCountElement) {
        cartCountElement.textContent = data.count;
      }
    });
}

document.addEventListener("DOMContentLoaded", () => {
  updateCartCount(); // Auto-load cart count on page load
});

function loadTrendingProducts() {
  fetch("get_trending_products.php")
    .then((response) => response.text())
    .then((html) => {
      document.getElementById("trending-products-container").innerHTML = html;
    });
}

// Initial load
loadTrendingProducts();

// Refresh every 2 minutes (120000 ms)
setInterval(loadTrendingProducts, 5000);

function toggleLike(el) {
  el.classList.toggle("liked");
  const icon = el.querySelector("i");
  icon.classList.toggle("bx-heart");
  icon.classList.toggle("bxs-heart");
}

function rateProduct(event) {
  const ratingEl = event.currentTarget;
  const stars = ratingEl.querySelectorAll("i");
  const clickedValue = parseInt(event.target.getAttribute("data-value"));

  stars.forEach((star) => {
    const value = parseInt(star.getAttribute("data-value"));
    if (value <= clickedValue) {
      star.classList.add("selected");
    } else {
      star.classList.remove("selected");
    }
  });

  // Optionally, send rating to server via AJAX here
}

function toggleLike(el) {
  el.classList.toggle("liked");
  const icon = el.querySelector("i");
  icon.classList.toggle("bx-heart");
  icon.classList.toggle("bxs-heart");
}

document.querySelectorAll(".rating").forEach((ratingEl) => {
  ratingEl.addEventListener("click", function (event) {
    const clickedStar = event.target.closest(".star");
    if (!clickedStar) return;

    const ratingValue = parseInt(clickedStar.getAttribute("data-value"));
    const allStars = ratingEl.querySelectorAll(".star");

    allStars.forEach((star) => {
      const value = parseInt(star.getAttribute("data-value"));
      if (value <= ratingValue) {
        star.classList.add("selected");
        star.classList.remove("bx-star");
        star.classList.add("bxs-star");
      } else {
        star.classList.remove("selected");
        star.classList.remove("bxs-star");
        star.classList.add("bx-star");
      }
    });

    // Optionally send ratingValue to server
    console.log("Rated:", ratingValue);
  });
});

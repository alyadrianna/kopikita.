feather.replace();

let cart = [];

// Update cart count badge
function updateCartCount() {
  const cartCount = document.getElementById("cart-count");
  if (cartCount) {
    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCount.textContent = totalQty;
  }
}

// Show the checkout form inside the cart panel
function showCheckoutForm() {
  const form = document.getElementById('checkout-form');
  form.style.display = 'block';
}

// Close the cart panel
function closeCart() {
  document.getElementById('cart-panel').classList.remove('active');
}

// Add product to cart (increase qty if exists)
function addToCart(name, price) {
  const existing = cart.find(item => item.name === name);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ name, price: parseFloat(price), quantity: 1 });
  }

  alert(`${name} telah ditambah ke dalam troli!`);
  updateCartCount();
  updateCartView();
}

// Open and update the cart panel
function openCart() {
  document.getElementById("cart-panel").classList.add("active");
  updateCartView();
}

// Refresh cart panel with current items and total
function updateCartView() {
  const cartItemsDiv = document.getElementById("cart-items");
  const cartTotal = document.getElementById("cart-total");

  cartItemsDiv.innerHTML = "";
  let total = 0;

  if (cart.length === 0) {
    cartItemsDiv.innerHTML = "<p>Troli anda kosong.</p>";
    cartTotal.textContent = "Jumlah: RM 0.00";
    return;
  }

  cart.forEach((item, index) => {
    total += item.price * item.quantity;

    const div = document.createElement("div");
    div.classList.add("cart-item");
    div.innerHTML = `
      <span class="cart-item-name">${item.name}</span> x ${item.quantity}<br>
      <span class="cart-item-price">RM ${item.price.toFixed(2)} each</span>
      <button class="cart-remove" onclick="removeFromCart(${index})">Buang</button>
    `;
    cartItemsDiv.appendChild(div);
  });

  cartTotal.textContent = `Jumlah: RM ${total.toFixed(2)}`;
}

// Remove item from cart by index
function removeFromCart(index) {
  cart.splice(index, 1);
  updateCartCount();
  updateCartView();
}

// Wait for DOM loaded to setup event listeners
document.addEventListener("DOMContentLoaded", function () {
  // Add to cart buttons
  document.querySelectorAll(".add-to-cart-btn").forEach(button => {
    button.addEventListener("click", function () {
      const name = this.dataset.name;
      const price = this.dataset.price;
      addToCart(name, price);
    });
  });

  // Cart icon click opens cart panel
  const cartIcon = document.getElementById("shopping-cart");
  if (cartIcon) {
    cartIcon.addEventListener("click", function (e) {
      e.preventDefault();
      openCart();
    });
  }

  // Search box toggle
  const searchBtn = document.getElementById("search-btn");
  const searchBox = document.getElementById("search-box");

  searchBtn.addEventListener("click", function (e) {
    e.preventDefault();
    if (searchBox.style.display === "none" || searchBox.style.display === "") {
      searchBox.style.display = "block";
      document.getElementById("search-input").focus();
    } else {
      searchBox.style.display = "none";
    }
  });

  // Search filtering menu cards
  document.getElementById("search-input").addEventListener("input", function () {
    const query = this.value.toLowerCase();
    const menuCards = document.querySelectorAll(".menu-card");

    menuCards.forEach(card => {
      const title = card.querySelector(".menu-card-title").textContent.toLowerCase();
      card.style.display = title.includes(query) ? "block" : "none";
    });
  });

  updateCartCount();
  updateCartView();

  // Checkout form submission handler
  document.getElementById('checkout-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    // Append cart items as JSON string to the form data
    formData.append('cart', JSON.stringify(cart));

    fetch(form.action, {
      method: form.method,
      body: formData
    })
    .then(response => response.text())
    .then(data => {
      alert(data); // Show PHP response

      form.reset();
      form.style.display = 'none';

      // Clear cart after successful order
      cart = [];
      updateCartCount();
      updateCartView();
      closeCart();
    })
    .catch(error => {
      alert('Error submitting order: ' + error);
    });
  });
});

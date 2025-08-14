<?php
$host = 'localhost';
$db = 'kopikita';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get POST data safely
$name = $_POST['customer-name'] ?? '';
$email = $_POST['customer-email'] ?? '';
$phone = $_POST['customer-phone'] ?? '';
$address = $_POST['customer-address'] ?? '';
$cartJson = $_POST['cart'] ?? '[]';

// Decode cart JSON into PHP array
$cart = json_decode($cartJson, true);

if (!$cart || !is_array($cart)) {
  die("Invalid cart data.");
}
$cartJson = $_POST['cart'] ?? '[]';

$stmt = $conn->prepare("INSERT INTO orders (name, email, phone, address, cart) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $phone, $address, $cartJson);



if ($stmt->execute()) {
  $order_id = $stmt->insert_id; // Get inserted order ID

  // Prepare statement to insert each cart item into order_items
  $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");

  foreach ($cart as $item) {
    $product_name = $item['name'];
    $price = $item['price'];
    $quantity = $item['quantity'];

    $item_stmt->bind_param("isdi", $order_id, $product_name, $price, $quantity);
    $item_stmt->execute();
  }

  $item_stmt->close();

  echo "Order submitted successfully! Terima kasih atas pesanan anda.";
} else {
  echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

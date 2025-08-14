<?php
// Database connection
$host = 'localhost';
$db = 'kopikita';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM orders WHERE id=$id");
  header("Location: admin.php");
  exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
  $id = intval($_POST['order_id']);
  $status = $_POST['status'];
  $allowed = ['Pending', 'Preparing', 'Done'];
  if (in_array($status, $allowed)) {
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: admin.php");
  exit;
}

// Fetch orders
$result = $conn->query("SELECT * FROM orders ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Manage Orders</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f9f9f9;
      margin: 0; padding: 20px; color: #333;
    }
    h1 {
      text-align: center; margin-bottom: 30px; color: #444;
    }
    table {
      width: 100%; border-collapse: collapse;
      background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      border-radius: 8px; overflow: hidden;
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
      text-align: left; vertical-align: top;
    }
    th {
      background-color: #007BFF;
      color: white;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    tr:hover {
      background-color: #f1f7ff;
    }
    td:last-child {
      white-space: nowrap;
    }
    a.delete {
      color: #e74c3c;
      font-weight: bold;
      text-decoration: none;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    a.delete:hover {
      color: #c0392b;
      text-decoration: underline;
    }
    .no-orders {
      text-align: center;
      padding: 20px;
      font-style: italic;
      color: #666;
    }
    select.status-select {
      padding: 5px 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-weight: 600;
      cursor: pointer;
    }
    /* Responsive */
    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        position: absolute; top: -9999px; left: -9999px;
      }
      tr {
        border: 1px solid #ccc; margin-bottom: 15px; border-radius: 8px; background: #fff; padding: 10px;
      }
      td {
        border: none; position: relative; padding-left: 50%; white-space: normal; text-align: left;
      }
      td::before {
        position: absolute; top: 12px; left: 15px; width: 45%; padding-right: 10px;
        white-space: nowrap; font-weight: 600; color: #007BFF;
      }
      td:nth-of-type(1)::before { content: "ID"; }
      td:nth-of-type(2)::before { content: "Nama"; }
      td:nth-of-type(3)::before { content: "Email"; }
      td:nth-of-type(4)::before { content: "Telefon"; }
      td:nth-of-type(5)::before { content: "Alamat"; }
      td:nth-of-type(6)::before { content: "Order Details"; }
      td:nth-of-type(7)::before { content: "Order Date"; }
      td:nth-of-type(8)::before { content: "Status"; }
      td:nth-of-type(9)::before { content: "Actions"; }
    }
  </style>
</head>
<body>
  <h1>Manage Orders</h1>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Telefon</th>
        <th>Alamat</th>
        <th>Order Details</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($order = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($order['id']); ?></td>
            <td><?php echo htmlspecialchars($order['name']); ?></td>
            <td><?php echo htmlspecialchars($order['email']); ?></td>
            <td><?php echo htmlspecialchars($order['phone']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
            <td>
              <?php
              if (!empty($order['cart'])) {
                $items = json_decode($order['cart'], true);
                if (is_array($items)) {
                  foreach ($items as $item) {
                    echo htmlspecialchars($item['name']) . " x " . intval($item['quantity']) . "<br>";
                  }
                } else {
                  echo "Invalid cart data";
                }
              } else {
                echo "No details";
              }
              ?>
            </td>
            <td><?php echo htmlspecialchars($order['created_at'] ?? ''); ?></td>
            <td>
              <form method="POST" action="admin.php" style="margin:0;">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>" />
                <select name="status" class="status-select" onchange="this.form.submit()">
                  <?php
                  $statuses = ['Pending', 'Preparing', 'Done'];
                  foreach ($statuses as $status) {
                    $selected = ($order['status'] === $status) ? 'selected' : '';
                    echo "<option value=\"$status\" $selected>$status</option>";
                  }
                  ?>
                </select>
                <input type="hidden" name="update_status" value="1" />
              </form>
            </td>
            <td>
              <a href="?delete=<?php echo $order['id']; ?>" class="delete" onclick="return confirm('Hapus pesanan ini?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="9" class="no-orders">Tiada pesanan.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>

<?php
$conn->close();
?>

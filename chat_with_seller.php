<?php
session_start();
include("connection.php");

// Get seller & product from URL
$sellerId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;  
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;  

// Logged in user
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Quick validation
if (!$sellerId || !$productId) {
    die("Error: seller_id or product_id missing in URL");
}

// Fetch seller's shop name
$query = "SELECT shopname FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$result = $stmt->get_result();
$seller = $result->fetch_assoc();
if (!$seller) {
    $seller = ['shopname' => 'Unknown Seller'];
}

// Fetch product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$productResult = $stmt->get_result();
$product = $productResult->fetch_assoc();
if (!$product) {
    $product = [
        'picture'      => 'placeholder.png',
        'product_name' => 'Unknown Product',
        'description'  => 'No description available.',
        'price'        => 0.00
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat with <?php echo htmlspecialchars($seller['shopname']); ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; background-color: #f4f7fa; color: #333; }
    .chat-container { max-width: 800px; margin: 50px auto; background-color: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; gap: 20px; }
    header { background-color: #007bff; color: #fff; padding: 20px; text-align: center; display: flex; align-items: center; justify-content: space-between; }
    header h2 { font-size: 24px; margin: 0; }
    header button { background: none; border: none; font-size: 24px; color: white; cursor: pointer; }
    header button:hover { color: black; }
    .product-section { display: flex; padding: 20px; background-color: #f9f9f9; border-bottom: 1px solid #ddd; }
    .product-image { max-width: 150px; margin-right: 20px; }
    .product-details { flex: 1; }
    .product-details h3 { font-size: 20px; margin-bottom: 10px; }
    .product-details p { font-size: 16px; }
    .chat-box { padding: 20px; height: 400px; overflow-y: scroll; border-bottom: 1px solid #ddd; background-color: #f9f9f9; display: flex; flex-direction: column; gap: 15px; }
    .message { display: inline-block; max-width: 70%; padding: 10px 15px; border-radius: 20px; font-size: 16px; line-height: 1.5; word-wrap: break-word; position: relative; color: #333; }
    .message-you { background-color: #007bff; color: #fff; align-self: flex-end; border-radius: 20px 20px 0 20px; }
    .message-seller { background-color: #e6e6e6; align-self: flex-start; border-radius: 20px 20px 20px 0; }
    .message-you:before { position: absolute; width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-bottom: 10px solid #007bff; right: -10px; top: 10px; }
    .message-seller:before { content: ""; position: absolute; width: 0; height: 0; border-left: 10px solid transparent; border-right: 10px solid transparent; border-bottom: 10px solid #e6e6e6; left: -10px; top: 10px; }
    .timestamp { font-size: 12px; color: black; margin-top: 5px; text-align: right; }  
    .message-seller .timestamp, .admin-reply .timestamp { text-align: left; }
    .chat-form { display: flex; padding: 15px 20px; background-color: #fff; border-top: 1px solid #ddd; }
    .chat-form input { flex: 1; padding: 12px 15px; font-size: 16px; border: 1px solid #ddd; border-radius: 25px; background-color: #f9f9f9; }
    .chat-form button { background-color: #007bff; color: #fff; padding: 12px 20px; border: none; border-radius: 25px; cursor: pointer; margin-left: 15px; }
    .chat-form button:hover { background-color: #0056b3; }
  </style>
</head>
<body>
  <div class="chat-container">
    <header>
      <button onclick="window.location.href='product_detail.php?id=<?php echo $productId; ?>'"><i class="fa fa-arrow-left"></i></button>
      <h2>Chat with <?php echo htmlspecialchars($seller['shopname']); ?></h2>
    </header>

    <div class="product-section">
      <img src="<?php echo htmlspecialchars($product['picture']); ?>" alt="Product Image" class="product-image">
      <div class="product-details">
        <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
      </div>
    </div>

    <div class="chat-box" id="chatBox"></div>

    <form class="chat-form" id="chatForm" onsubmit="return sendMessage();">
      <input type="text" id="messageInput" placeholder="Type a message..." required>
      <button type="submit">Send</button>
    </form>
  </div>

  <script>
    function loadMessages() {
      const productId = <?php echo $productId; ?>;
      const sellerId = <?php echo $sellerId; ?>;
      const userId = <?php echo $userId; ?>;
      fetch('load_messages.php?seller_id=' + sellerId + '&user_id=' + userId + '&product_id=' + productId)
        .then(response => response.text())
        .then(data => {
          document.getElementById('chatBox').innerHTML = data;
          document.getElementById('chatBox').scrollTop = document.getElementById('chatBox').scrollHeight;
        });
    }

    function sendMessage() {
      const message = document.getElementById('messageInput').value;
      if (message.trim() === "") return false;
      const productId = <?php echo $productId; ?>;
      const sellerId = <?php echo $sellerId; ?>;
      const userId = <?php echo $userId; ?>;
      fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `message=${encodeURIComponent(message)}&product_id=${productId}&user_id=${userId}&seller_id=${sellerId}`
      })
      .then(() => {
        document.getElementById('messageInput').value = '';
        loadMessages();
      });
      return false;
    }

    window.onload = loadMessages;
    setInterval(loadMessages, 3000); // auto refresh every 3s
  </script>
</body>
</html>

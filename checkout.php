<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'db_connect.php'; // Ensure this path is correct
include 'KeyGenerator.php';
include 'Transaction.php';
include 'Blockchain.php';
include 'ReceiptGenerator.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the cart is empty
if (empty($_SESSION['cart'])) {
    // Redirect to cart page or show a message
    echo "<p>Your cart is empty. Please add items to your cart before checking out.</p>";
    echo "<a href='products.php'>Return to Products</a>";
    exit; 
}


// Retrieve user ID and cart items from the session
$userId = $_SESSION['user_id']; // Replace with your actual session variable
$cartItems = $_SESSION['cart']; // Assuming cart is stored in session

// Calculate total amount from cart items
$totalAmount = 0;
foreach ($cartItems as $productId => $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// Placeholder for Blockchain transaction
// Initialize blockchain
$blockchain = new Blockchain();

// Example transaction data (replace with actual data)
$senderKeys = KeyGenerator::generateKeys();
$receiverKeys = KeyGenerator::generateKeys();

$transactionData = Transaction::create($senderKeys['publicKey'], $receiverKeys['publicKey'], $totalAmount);
$signedTransaction = Transaction::sign($transactionData, $senderKeys['privateKey']);

// Add transaction to blockchain
$blockchain->addBlock([$signedTransaction], '0'); // '0' is a placeholder for the previous hash

// Insert order into database
$stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
$stmt->bind_param("id", $userId, $totalAmount);
$stmt->execute();
$orderId = $stmt->insert_id;

// Insert each cart item into order_items table
foreach ($cartItems as $productId => $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $orderId, $productId, $item['quantity'], $item['price']);
    $stmt->execute();
}

// Generate a receipt for the transaction
$receipt = ReceiptGenerator::generate($blockchain, $signedTransaction);

// Format and display the receipt
echo "<h1>Order Receipt</h1>";
echo "<p>Order ID: " . $orderId . "</p>";
echo "<p>Date: " . date("Y-m-d H:i:s") . "</p>";
echo "<table>";
echo "<tr><th>Product ID</th><th>Quantity</th><th>Price</th></tr>";
foreach ($cartItems as $productId => $item) {
    echo "<tr><td>{$productId}</td><td>{$item['quantity']}</td><td>\${$item['price']}</td></tr>";
}
echo "</table>";
echo "<p>Total Amount: \${$totalAmount}</p>";
echo "<p>Blockchain Transaction:</p>";
echo "<pre>" . print_r($receipt, true) . "</pre>";

// Clear the cart from the session after checkout
unset($_SESSION['cart']);
?>

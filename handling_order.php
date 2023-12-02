<?php
session_start();
require_once('backend/db_connect.php'); // Ensure this path is correct

if (!isset($_SESSION['orderDetails']) || !isset($_SESSION['totalAmount']) || !isset($_SESSION['user_id'])) {
    echo "<p>Error: Receipt details not found.</p>";
    exit;
}

$orderDetails = $_SESSION['orderDetails'];
$totalAmount = $_SESSION['totalAmount'];
$user_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session
$timestamp = date("Y-m-d H:i:s");

// Insert order into 'orders' table
$stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
$stmt->bind_param("id", $user_id, $totalAmount);
$stmt->execute();
$order_id = $conn->insert_id;

// Insert each item into 'order_items' table
foreach ($orderDetails as $productId => $details) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $productId, $details['quantity'], $details['price']);
    $stmt->execute();
}

// Clear cart session
unset($_SESSION['cart']);

// Store necessary details in the session
$_SESSION['orderConfirmation'] = [
    'order_id' => $order_id,
    'totalAmount' => $totalAmount,
    'timestamp' => $timestamp
];

// Redirect to confirmation page
header('Location: receipt.php?order_id=' . $order_id);
exit;
?>

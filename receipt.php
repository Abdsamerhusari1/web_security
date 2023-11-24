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
$username= $_SESSION['username'];

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
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <div>
                <a href="index.php" class="px-3 hover:text-gray-300">Home</a>
                <a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
                
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <?php if(isset($_SESSION['orderDetails']) && isset($_SESSION['totalAmount']) && isset($_SESSION['user_id'])): ?>
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold">Payment Receipt</h2>
                    <p><strong>Username:</strong> <?= htmlspecialchars($order_id) ?></p>
                    <p><strong>Total Amount:</strong> $<?= number_format($totalAmount, 2) ?></p>
                    <p><strong>Order Number:</strong> <?= number_format($order_id) ?></p>
                    <p><strong>Date:</strong> <?= $timestamp ?></p>
                    
                    <h3 class="font-bold mt-4">Order Details:</h3>
                    <ul>
                        <?php foreach($orderDetails as $details): ?>
                            <li><?= htmlspecialchars($details['name']) ?>, Quantity: <?= htmlspecialchars($details['quantity']) ?>, Price: $<?= number_format($details['price'], 2) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <form action="index.php" method="post" class="mt-8">
                        <input type="hidden" name="emptyCart" value="1">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back to Home</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-red-500 text-center">Error: Receipt details not found.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Footer -->
	<footer class="bg-gray-800 text-white text-center p-4 mt-auto">
		© 2023 Group 2 Shop
    </footer>
</body>
</html>


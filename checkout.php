<?php
if (isset($error_message) && !empty($error_message)) {
    echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Checkout</title>

<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'backend/db_connect.php';

// Redirect to login with a return path if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $redirectDelay = 3; // Delay in seconds
    $redirectURL = "login.php?from=checkout";
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="<?php echo $redirectDelay; ?>;url=<?php echo $redirectURL; ?>">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center">
        <p class="text-green-500">Please log in to proceed with checkout. Redirecting to login page in <?php echo $redirectDelay; ?> seconds...</p>
    </div>
</body>
</html>

<?php
    exit;
}

// Function to send POST requests to the blockchain server
function postRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

// If the user is logged in but the cart is empty, show a message
if (empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. Please add items to your cart before checking out.</p>";
    echo "<a href='index.php'>Return to Products</a>";
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

// Send the transaction to the blockchain server
$transactionData = array(
    'sender' => 'shop_address', // Replace with the shop's blockchain address
    'recipient' => 'user_blockchain_address', // Replace with the user's blockchain address
    'amount' => $totalAmount
);
$blockchainResponse = postRequest('http://localhost:5001/transactions/new', $transactionData);

// Clear the cart from the session after checkout
unset($_SESSION['cart']);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-8 bg-white shadow-lg max-w-2xl">
        <?php if (isset($error_message)): ?>
            <p class="text-red-500 text-center"><?php echo $error_message; ?></p>
            <div class="text-center mt-4">
                <a href="products.php" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">Return to Products</a>
            </div>
        <?php else: ?>
            <!-- Receipt Content -->
            <h2 class="text-3xl font-bold mb-4 text-center">Order Receipt</h2>

            <!-- Order Details -->
            <div class="mb-6">
                <p><strong>Order ID:</strong> <?php echo $orderId; ?></p>
                <p><strong>Date:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>
            </div>

            <!-- Items Purchased Table -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-2">Items Purchased</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border p-3">Product ID</th>
                            <th class="border p-3">Quantity</th>
                            <th class="border p-3">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $productId => $item): ?>
                        <tr>
                            <td class="border p-3"><?php echo $productId; ?></td>
                            <td class="border p-3"><?php echo $item['quantity']; ?></td>
                            <td class="border p-3">$<?php echo number_format($item['price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Total Amount -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold">Total Amount</h3>
                <p class="text-lg">$<?php echo number_format($totalAmount, 2); ?></p>
            </div>

            <!-- Blockchain Transaction -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold mb-2">Blockchain Transaction</h3>
                <pre class="bg-gray-200 p-4"><?php echo htmlspecialchars(print_r($receipt, true)); ?></pre>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
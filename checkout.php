<?php
session_start();
require_once('backend/db_connect.php'); // Ensure this path is correct

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
        <p class="text-green-500 text-lg">Please log in to proceed with checkout. Redirecting to login page in <?php echo $redirectDelay; ?> seconds...</p>
    </div>
</body>
</html>

<?php
    exit;
}


function getProductDetails($conn, $orderDetails) {
    $productDetails = [];
    foreach ($orderDetails as $productId => $details) {
        $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $productDetails[$productId] = [
                'name' => $product['name'],
                'quantity' => $details['quantity'],
                'price' => $details['price']
            ];
        }
    }
    return $productDetails;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orderDetails'])) {
    $orderDetails = json_decode(base64_decode($_POST['orderDetails']), true);
    $productDetails = getProductDetails($conn, $orderDetails);

    $totalAmount = 0;
    foreach ($productDetails as $item) {
        $totalAmount += $item['quantity'] * $item['price'];
    }

    // Store order details and total amount in session for later use
    $_SESSION['orderDetails'] = $productDetails;
    $_SESSION['totalAmount'] = $totalAmount;
}
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <nav class="bg-gray-800 text-white text-center p-4">
        <div class="container mx-auto flex justify-between items-center">
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
            <?php if(isset($productDetails)): ?>
                <h2 class="text-2xl font-bold my-4">Checkout</h2>
                <div>
                    <h3 class="font-bold">Order Summary:</h3>
                    <?php foreach($productDetails as $productId => $details): ?>
                        <div class="bg-white p-4 rounded-lg shadow-lg my-4">
                            <p><?= htmlspecialchars($details['name']) ?></p>
                            <p>Quantity: <?= htmlspecialchars($details['quantity']) ?></p>
                            <p>Price: $<?= number_format($details['price'], 2) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="font-bold mt-4">Total Amount: $<?= number_format($totalAmount, 2) ?></p>
                <form action="payment_confirmation.php" method="post">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Pay</button>
                </form>
            <?php else: ?>
                <p class="text-red-500 text-lg mt-4">Error: No order details found. Please try again.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
    <?php $conn->close(); ?>
</body>
</html>

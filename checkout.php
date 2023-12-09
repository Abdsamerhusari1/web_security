<?php
// Check if the connection is not secure (HTTP) and redirect to HTTPS if needed.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}
?>

<?php
// Display an error message if it is set and not empty.
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Checkout</title>

<?php
session_start();

require_once('backend/db_connect.php');

// Redirect to login with a return path if not logged in.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    $redirectDelay = 3; // Delay in seconds
    $redirectURL = "login.php?from=checkout";
    ?>
<!-- Redirect to login page if not logged in -->
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
        <p class="text-green-500 text-lg">Please log in to proceed with checkout. Redirecting to the login page in <?php echo $redirectDelay; ?> seconds...</p>
    </div>
</body>
</html>
<?php
    exit;
}

// Function to retrieve product details based on order details.
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    if (isset($_POST['orderDetails'])) {
        // Decode and retrieve order details from the POST data.
        $orderDetails = json_decode(base64_decode($_POST['orderDetails']), true);
        $productDetails = getProductDetails($conn, $orderDetails);

        // Calculate the total amount for the order.
        $totalAmount = 0;
        foreach ($productDetails as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Store order details and total amount in the session for later use.
        $_SESSION['orderDetails'] = $productDetails;
        $_SESSION['totalAmount'] = $totalAmount;
    }
}

$hashedPublicKey = 'a5da4d31a0a674f3ad9cdd3c83fc78176381e1769a3212718cc6a3ff800e03f9';
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation menu -->
    <nav class="bg-gray-800 text-white text-center p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-lg">Group 2 Shop</div>
            <div>
                <!-- Navigation links based on user login status -->
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
                <!-- Display checkout information if order details are available -->
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
                <p class="font-bold mt-4">Hashed Public Key: <span id="hashedKey"><?php echo $hashedPublicKey; ?></span></p>

               <!-- Payment Form -->
               <form action="payment_confirmation.php" method="post">
                    <!-- Include order details as hidden input -->
                    <input type='hidden' name='orderDetails' value='<?= base64_encode(json_encode($orderDetails)) ?>'>

                    <!-- Field for Transaction ID -->
                    <label for="transactionId">Transaction ID:</label>
                    <input type="text" id="transactionId" name="transactionId" required class="p-2 border rounded mb-4">

                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Confirm Payment</button>
                </form>
            <?php else: ?>
                <!-- Display an error message if no order details are found -->
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

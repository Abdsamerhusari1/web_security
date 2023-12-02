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

<title>Cart</title>

<?php
session_start();
require_once('backend/db_connect.php');

// Function to update the quantity of a product in the cart.
function updateCartQuantity($conn, $productId, $quantity) {
    if ($quantity <= 0) {
        // Remove the product from the cart if the quantity is zero or negative.
        unset($_SESSION['cart'][$productId]); 
    } else {
        // Retrieve product price from the database and update the cart.
        $stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $product['price']
            ];
        }
    }
}

// Process POST requests to update cart quantities.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        updateCartQuantity($conn, $productId, intval($quantity));
    }
}

// Function to display the contents of the shopping cart.
function displayCart($conn) {
    $totalPrice = 0;
    if (!empty($_SESSION['cart'])) {
        // Display the cart items and calculate the total price.
        echo "<div class='flex justify-center'><h2 class='text-2xl font-bold my-4'>Your Shopping Cart</h2></div>";
        echo "<form id='cart-form' method='post'>";
        echo "<ul class='list-none'>";

        foreach ($_SESSION['cart'] as $productId => $details) {
            // Retrieve product name from the database.
            $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $productName = "Unknown";
            if ($product = $result->fetch_assoc()) {
                $productName = $product['name'];
            }

            // Calculate subtotal and update total price.
            $subtotal = $details['quantity'] * $details['price'];
            $totalPrice += $subtotal;

            // Display product information in the cart.
            echo "<li class='flex justify-center items-center my-2'>";
            echo "<strong class='flex-1 text-center'>" . htmlspecialchars($productName) . "</strong> ";
            echo "<input type='number' name='quantities[$productId]' value='" . $details['quantity'] . "' min='0' class='mx-2 p-2 border rounded text-center' onchange='updateCart()'> ";
            echo "<span class='flex-1 text-center'>@ $" . htmlspecialchars($details['price']) . " each</span> ";
            echo "<span class='flex-1 text-center'>Subtotal: $" . number_format($subtotal, 2) . "</span>";
            echo "</li>";
        }

        echo "</ul>";
        echo "</form>"; 

        // Display the total price and a button to proceed to checkout.
        echo "<div style='text-align: center;' class='text-lg font-bold my-4'>Total Price: $" . number_format($totalPrice, 2) . "</div>";
        echo "<form action='checkout.php' method='post'>";
        echo "<input type='hidden' name='orderDetails' value='" . base64_encode(json_encode($_SESSION['cart'])) . "'>";
        echo "<div style='display: flex; justify-content: center;'><button type='submit' class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>Proceed to Checkout</button></div>";
        echo "</form>";
    } else {
        // Display a message if the cart is empty.
        echo "<p class='text-red-500 text-center'>Your cart is empty.</p>";
    }
}
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
    <!-- Include external CSS and JavaScript libraries -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // JavaScript function to submit the cart form when quantities are updated.
        function updateCart() {
            document.getElementById('cart-form').submit();
        }
    </script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Header and navigation menu -->
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <div>
                <!-- Navigation links based on user login status -->
                <a href="index.php" class="px-3 hover:text-gray-300">Home</a>
                <a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="orders.php" class="px-3 hover:text-gray-300">My Orders</a>
                    <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- Main content: Display the shopping cart -->
    <div class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <?php displayCart($conn); ?>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
    <?php $conn->close(); ?>
</body>
</html>

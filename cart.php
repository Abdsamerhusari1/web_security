<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Cart</title>

<?php
session_start();
require_once('backend/db_connect.php');

// Function to update item quantity in the cart
function updateCartQuantity($conn, $productId, $quantity) {
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]); // Remove item from cart if quantity is 0 or less
    } else {
        // Fetch product price
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

// Check if the quantity update is triggered
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        updateCartQuantity($conn, $productId, intval($quantity));
    }
}

// Function to display the cart
function displayCart($conn) {
    $totalPrice = 0;

    if (!empty($_SESSION['cart'])) {
        echo "<h2 class='text-2xl font-bold my-4'>Your Shopping Cart</h2>";
        echo "<form id='cart-form' action='cart.php' method='post' class='w-full max-w-lg'>";
        echo "<ul class='list-none'>";
        
        foreach ($_SESSION['cart'] as $productId => $details) {
            // Fetch product name
            $stmt = $conn->prepare("SELECT name FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $productName = "Unknown";
            if ($product = $result->fetch_assoc()) {
                $productName = $product['name'];
            }

            $subtotal = $details['quantity'] * $details['price'];
            $totalPrice += $subtotal;

            // Display product name and form to update quantity
            echo "<li class='flex justify-between items-center my-2'>";
            echo "<strong class='flex-1'>" . htmlspecialchars($productName) . "</strong> ";
            echo "<input type='number' name='quantities[$productId]' value='" . $details['quantity'] . "' min='0' class='mx-2 p-2 border rounded' onchange='updateCart()'> ";
            echo "<span class='flex-1'>@ $" . htmlspecialchars($details['price']) . " each</span> ";
            echo "<span class='flex-1'>Subtotal: $" . number_format($subtotal, 2) . "</span>";
            echo "</li>";
        }
        
        echo "</ul>";
        echo "<div class='text-lg font-bold my-4'>Total Price: $" . number_format($totalPrice, 2) . "</div>";
        
        // Checkout Button
        echo "<div class='text-center mt-4'>";
        echo "<a href='checkout.php' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>";
        echo "Checkout";
        echo "</a>";
        echo "</div>";
        
        echo "</form>";
    } else {
        echo "<p class='text-red-500 text-center'>Your cart is empty.</p>";
    }
}

?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
    function updateCart() {
        document.getElementById('cart-form').submit();
    }
    </script>
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

    <!-- Main Content Area -->
    <!-- Main Content Area -->
    <div class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <?php displayCart($conn); ?>
            <!-- No Checkout Button here if the cart is empty -->
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>

    <?php $conn->close(); ?>
</body>
</html>
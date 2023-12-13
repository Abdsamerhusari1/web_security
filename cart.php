<?php
session_start();
// if (empty($_SESSION['csrf_token'])) {
//     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// }
require_once('backend/db_connect.php');

// Function to update item quantity in the cart
function updateCartQuantity($conn, $productId, $quantity) {
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]); // Remove item from cart if quantity is 0 or less
    } else {
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('Invalid CSRF token');
    // }

    foreach ($_POST['quantities'] as $productId => $quantity) {
        updateCartQuantity($conn, $productId, intval($quantity));
    }
}

function displayCart($conn) {
    $totalPrice = 0;
    if (!empty($_SESSION['cart'])) {
        echo "<div class='flex justify-center'><h2 class='text-2xl font-bold my-4'>Your Shopping Cart</h2></div>";
        echo "<form id='cart-form' method='post'>";
        echo "<ul class='list-none'>";

        foreach ($_SESSION['cart'] as $productId => $details) {
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

            echo "<li class='flex justify-center items-center my-2'>";
            echo "<strong class='flex-1 text-center'>" . htmlspecialchars($productName) . "</strong> ";
            echo "<input type='number' name='quantities[$productId]' value='" . $details['quantity'] . "' min='0' class='mx-2 p-2 border rounded text-center' onchange='updateCart()'> ";
            echo "<span class='flex-1 text-center'>@ $" . htmlspecialchars($details['price']) . " each</span> ";
            echo "<span class='flex-1 text-center'>Subtotal: $" . number_format($subtotal, 2) . "</span>";
            echo "</li>";
        }

        echo "</ul>";
        echo "</form>"; // Closing the cart form

        echo "<div style='text-align: center;' class='text-lg font-bold my-4'>Total Price: $" . number_format($totalPrice, 2) . "</div>";

        echo "<form action='checkout.php' method='post'>";
        echo "<input type='hidden' name='orderDetails' value='" . base64_encode(json_encode($_SESSION['cart'])) . "'>";
        //echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
        echo "<div style='display: flex; justify-content: center;'><button type='submit' class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>Proceed to Checkout</button></div>";
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
                    <a href="orders.php" class="px-3 hover:text-gray-300">My Orders</a> <!-- Add My Orders link -->
                    <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
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

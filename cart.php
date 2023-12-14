<?php
/*if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}*/
?>

<?php
if (isset($error_message) && !empty($error_message)) {
    echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Cart</title>

<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('backend/db_connect.php');

function updateCartQuantity($conn, $productId, $quantity) {
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
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
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
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
        echo "</form>"; 

        echo "<div style='text-align: center;' class='text-lg font-bold my-4'>Total Price: $" . number_format($totalPrice, 2) . "</div>";
        echo "<form action='checkout.php' method='post'>";
        echo "<input type='hidden' name='orderDetails' value='" . base64_encode(json_encode($_SESSION['cart'])) . "'>";
        echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
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
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <div class="search-container">
                <form action="search.php" method="get" class="flex items-center justify-center">
                    <input type="text" placeholder="Search for products..." name="search" class="px-3 py-2 placeholder-gray-500 text-gray-900 rounded-l-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5">
                    <button type="submit" class="ml-3 flex-shrink-0 px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">Search</button>
                </form>
            </div>
            <div>
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
    <div class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <?php displayCart($conn); ?>
        </div>
    </div>
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
    <?php $conn->close(); ?>
</body>
</html>

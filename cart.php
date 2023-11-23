<?php
session_start();
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

function displayCart($conn) {
    $totalPrice = 0;

    if (!empty($_SESSION['cart'])) {
        echo "<h2 class='text-2xl font-bold my-4'>Your Shopping Cart</h2>";
        echo "<form id='update-cart-form' method='post'>";
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

            echo "<li class='flex justify-between items-center my-2'>";
            echo "<strong class='flex-1'>" . htmlspecialchars($productName) . "</strong> ";
            echo "<input type='number' name='quantities[$productId]' value='" . $details['quantity'] . "' min='0' class='mx-2 p-2 border rounded' onchange='updateCart()'> ";
            echo "<span class='flex-1'>@ $" . htmlspecialchars($details['price']) . " each</span> ";
            echo "<span class='flex-1'>Subtotal: $" . number_format($subtotal, 2) . "</span>";
            echo "</li>";
        }
        echo "</ul>";
        echo "<button type='submit' class='bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'>Update Cart</button>";
        echo "</form>"; // Closing the update cart form

        echo "<div class='text-lg font-bold my-4'>Total Price: $" . number_format($totalPrice, 2) . "</div>";

        echo "<form action='checkout.php' method='post'>";
        echo "<input type='hidden' name='orderDetails' value='" . base64_encode(json_encode($_SESSION['cart'])) . "'>";
        echo "<button type='submit' class='bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'>Proceed to Checkout</button>";
        echo "</form>";
    } else {
        echo "<p class='text-red-500 text-center'>Your cart is empty.</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        updateCartQuantity($conn, $productId, intval($quantity));
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
            document.getElementById('update-cart-form').submit();
        }
    </script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation and Other HTML Elements -->

    <div class="flex-grow container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <form id='cart-form' method='post'>
                <?php displayCart($conn); ?>
            </form>
        </div>
    </div>

    <!-- Footer HTML -->

    <?php $conn->close(); ?>
</body>
</html>

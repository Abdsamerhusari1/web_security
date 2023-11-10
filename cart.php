<?php
session_start();
require_once('db_connect.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}


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
        echo "<h2>Your Shopping Cart</h2>";
        echo "<form id='cart-form' action='cart.php' method='post'>";
        echo "<ul>";
        
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
            echo "<li>";
            echo "<strong>" . htmlspecialchars($productName) . "</strong> ";
            echo "<input type='number' name='quantities[$productId]' value='" . $details['quantity'] . "' min='0' onchange='updateCart()'> ";
            echo "<span>@ $" . htmlspecialchars($details['price']) . " each</span> ";
            echo "<span>Subtotal: $" . number_format($subtotal, 2) . "</span>";
            echo "</li>";
        }
        
        echo "</ul>";
        echo "<strong>Total Price: $" . number_format($totalPrice, 2) . "</strong>";
        echo "</form>";
    } else {
        echo "<p>Your cart is empty.</p>";
    }

    
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Shopping Cart</title>
    <script>
    function updateCart() {
        document.getElementById('cart-form').submit();
    }
    </script>
</head>
<body>
    <?php displayCart($conn); ?>
    <a href="products.php">Continue Shopping</a> | 
    <a href="checkout.php">Checkout</a> | 
    <a href="logout.php">Logout</a>
</body>
</html>

<?php $conn->close(); ?>

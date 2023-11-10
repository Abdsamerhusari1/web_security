<?php
session_start(); // Start the session

// Initialize shopping cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Function to add an item to the cart
function addToCart($productId, $quantity) {
    // Check if the product already exists in the cart
    if (isset($_SESSION['cart'][$productId])) {
        // If the product exists, increase the quantity
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        // If the product does not exist, add it to the cart
        $_SESSION['cart'][$productId] = $quantity;
    }
}

// Handle Add to Cart action
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id']; // Get the product ID from the POST request
    $quantity = $_POST['quantity'] ?? 1; // Get the quantity, default to 1 if not set

    addToCart($productId, $quantity); // Add the product to the cart
}

// Display the cart
echo "<h2>Your Shopping Cart</h2>";
if (!empty($_SESSION['cart'])) {
    echo "<ul>";
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        echo "<li>Product ID: $productId, Quantity: $quantity</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Your cart is empty.</p>";
}

// The HTML form for adding products to the cart
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
</head>
<body>
    <h1>Products</h1>
    <!-- Example product listing -->
    <div id="product-1">
        <h3>Product 1</h3>
        <p>Price: $100</p>
        <form method="post" action="">
            <input type="hidden" name="product_id" value="1">
            <input type="number" name="quantity" value="1" min="1">
            <input type="submit" name="add_to_cart" value="Add to Cart">
        </form>
    </div>
    <div id="product-2">
        <h3>Product 2</h3>
        <p>Price: $200</p>
        <form method="post" action="">
            <input type="hidden" name="product_id" value="2">
            <input type="number" name="quantity" value="1" min="1">
            <input type="submit" name="add_to_cart" value="Add to Cart">
        </form>
    </div>
</body>
</html>

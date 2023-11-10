<?php
session_start();
require_once('db_connect.php'); // Include your database connection

// Function to add an item to the cart
function addToCart($productId, $quantity) {
    // Start session if not already started
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = 0;
    }
    $_SESSION['cart'][$productId] += $quantity;
}

// Check if add to cart action is triggered
if (isset($_POST['add_to_cart'])) {
    // Check if the user is logged in
    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
        $productId = $_POST['product_id'];
        $quantity = 1; // This can be changed to allow specifying quantity
        addToCart($productId, $quantity);
    } else {
        // Redirect to login page if not logged in
        header("location: login.php");
        exit;
    }
}

// Fetch all products from the database
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Our Products</title>
</head>
<body>
    <h1>Our Products</h1>
    <a href="welcome.php">Go back to Welcome Page</a>|
    <a href="cart.php">Cart</a> |
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
    <?php if ($result->num_rows > 0): ?>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:100px; height:auto;">
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p>Price: $<?php echo htmlspecialchars($row['price']); ?></p>
                    <?php if ($row['stock'] > 0): ?>
                        <p>In stock</p>
                        <form method="post" action="products.php">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <input type="submit" name="add_to_cart" value="Add to Cart">
                        </form>
                    <?php else: ?>
                        <p>Out of stock</p>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>


</body>
</html>

<?php
$conn->close();
?>

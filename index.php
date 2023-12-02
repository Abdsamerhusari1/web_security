<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}
?>

<?php
global $conn;
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<?php
session_start();
require_once('backend/db_connect.php');

function addToCart($productId, $quantity, $price) {
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = ['quantity' => 0, 'price' => $price];
    }
    $_SESSION['cart'][$productId]['quantity'] += $quantity;
}

if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = 1; // You can modify to allow different quantities
    $price = $_POST['product_price']; // Should be a hidden input from the form
    addToCart($productId, $quantity, $price);
}

if (isset($_SESSION['successMessage']) && !empty($_SESSION['successMessage'])) {
    echo '<p class="text-green-600 text-center">' . $_SESSION['successMessage'] . '</p>';
    // Unset the success message after displaying it so it doesn't show again on page refresh
    unset($_SESSION['successMessage']);
}

$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Our Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
                    <a href="orders.php" class="px-3 hover:text-gray-300">My Orders</a>
                    <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Welcome Message -->
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <div class="mt-4 pl-9 mx-24">
            <span class="text-2xl text-indigo-600 font-bold">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</span>
        </div>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="container mx-auto px-4 mt-8">
        <h1 class="text-2xl font-bold text-center">Our Products</h1>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid md:grid-cols-3 gap-4 mt-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-4 shadow rounded">
                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <img src="images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="w-32 h-auto my-2">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($row['price']); ?></p>
                        <?php if ($row['stock'] > 0): ?>
                            <p class="text-green-500">In stock</p>
                            <form method="post" action="index.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                                <input type="submit" name="add_to_cart" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" value="Add to Cart">
                            </form>
                        <?php else: ?>
                            <p class="text-red-500">Out of stock</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No products found.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
	<footer class="bg-gray-800 text-white text-center p-4 mt-auto">
		© 2023 Group 2 Shop
    </footer>

    <?php $conn->close(); ?>
</body>
</html>
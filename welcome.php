<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<?php

session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
	header("location: login.php");
	exit;
}

// Placeholder for cart display logic
$cartDisplay = "";
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
	$cartDisplay = "<p>You have " . count($_SESSION['cart']) . " item(s) in your cart.</p>";
	$cartDisplay .= '<a href="cart.php">View Cart</a>';
} else {
	$cartDisplay = "<p>Your cart is empty.</p>";
}
?>

	<title>Welcome to the Web Shop</title>
	<h1>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
	<p>
		<a href="products.php">View Products</a> |
		<a href="cart.php">Show cart</a> |
		<a href="logout.php">Logout</a>
	</p>


<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<?php

session_start();
require_once('db_connect.php');

function addToCart($productId, $quantity, $price) {
	if (!isset($_SESSION['cart'][$productId])) {
		$_SESSION['cart'][$productId] = ['quantity' => 0, 'price' => $price];
	}
	$_SESSION['cart'][$productId]['quantity'] += $quantity;
}

if (isset($_POST['add_to_cart'])) {
	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
		$productId = $_POST['product_id'];
		$quantity = 1; // You can add a quantity input to the form if needed
		$price = $_POST['product_price']; // This should be sent as a hidden input from the form
		addToCart($productId, $quantity, $price);
	} else {
		header("location: login.php");
		exit;
	}
}

$query = "SELECT * FROM products";
$result = $conn->query($query);
?>

	<title>Our Products</title>
	<h1>Our Products</h1>
	<a href="welcome.php">Go back to Welcome Page</a> | 
	<a href="cart.php">Cart</a> |
	<?php echo isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true ? '<a href="logout.php">Logout</a>' : '<a href="login.php">Login</a>'; ?>
	
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
							<input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
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



<?php $conn->close(); ?>

<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}

session_start();
require_once('backend/db_connect.php');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$query = "SELECT * FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <div>
                <a href="index.php" class="px-3 hover:text-gray-300">Home</a>
                <a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
                <a href="orders.php" class="px-3 hover:text-gray-300">My Orders</a>
                <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 mt-8">
        <h1 class="text-2xl font-bold text-center">My Orders</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-4 shadow rounded">
                        <h3 class="text-lg font-semibold">Order ID: <?php echo $row['order_id']; ?></h3>
                        <p>Order Date: <?php echo $row['order_date']; ?></p>
                        <p>Total: $<?php echo $row['total']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No orders found.</p>
        <?php endif; ?>
    </div>

    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>

    <?php $conn->close(); ?>
</body>
</html>

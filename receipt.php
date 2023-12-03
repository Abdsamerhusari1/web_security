<?php session_start(); ?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <!-- Search Form -->
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
            <?php if (isset($_SESSION['orderConfirmation'])): ?>
                <?php 
                    $confirmation = $_SESSION['orderConfirmation'];
                    $order_id = $confirmation['order_id'];
                    $totalAmount = $confirmation['totalAmount'];
                    $timestamp = $confirmation['timestamp'];
                    $user_id = $_SESSION['user_id']; // Assuming the user ID is still in the session
                ?>
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold">Order Confirmation</h2>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user_id) ?></p>
                    <p><strong>Total Amount:</strong> $<?= number_format($totalAmount, 2) ?></p>
                    <p><strong>Order Number:</strong> <?= number_format($order_id) ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($timestamp) ?></p>

                    <form action="index.php" method="post" class="mt-8">
                        <input type="hidden" name="emptyCart" value="1">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back to Home</button>
                    </form>
                </div>
            <?php else: ?>
                <p class="text-red-500 text-center">Error: Order confirmation details not found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
</body>
</html>

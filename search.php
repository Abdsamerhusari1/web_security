<?php
session_start();

require_once('backend/db_connect.php');

// Check if the connection is not secure (HTTP) and redirect to HTTPS if needed.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}

$searchResults = [];

// Function to add a product to the cart.
function addToCart($productId, $quantity, $price) {
    if (!isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] = ['quantity' => 0, 'price' => $price];
    }
    $_SESSION['cart'][$productId]['quantity'] += $quantity;
}

// Check if the "Add to Cart" button was clicked.
if (isset($_POST['add_to_cart'])) {

    $productId = $_POST['product_id'];
    $quantity = 1; 
    $price = $_POST['product_price']; 
    addToCart($productId, $quantity, $price);
}

// Determine the search term from either POST or GET requests
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
} elseif (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
} else {
    $searchTerm = '';
}

if ($searchTerm) {
    $query = "SELECT * FROM products WHERE name LIKE ?";
    $stmt = $conn->prepare($query);
    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
}
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
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

    <!-- Main Content Area -->
    <div class="container mx-auto px-4 mt-8">
        <h1 class="text-2xl font-bold text-center">Search Results</h1>

        <?php if (!empty($searchResults)): ?>
            <div class="grid md:grid-cols-3 gap-4 mt-6">
                <?php foreach ($searchResults as $row): ?>
                    <div class="bg-white p-4 shadow rounded">
                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <img src="images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="w-32 h-auto my-2">
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p>Price: $<?php echo htmlspecialchars($row['price']); ?></p>
                        <?php if ($row['stock'] > 0): ?>
                            <form method="post" action="search.php">
                                <!-- Include hidden fields for the product and the current search term -->
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <input type="submit" name="add_to_cart" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" value="Add to Cart">
                            </form>
                        <?php endif; ?>                   
                    </div>
                <?php endforeach; ?>
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

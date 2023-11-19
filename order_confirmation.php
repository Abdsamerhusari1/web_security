<?php
session_start();

function displayReceipt($orderDetails) {
    $totalPrice = 0;
    echo "<div class='receipt'>";
    echo "<h2>Receipt</h2>";
    echo "<ul>";
    foreach ($orderDetails as $item) {
        $subtotal = $item['quantity'] * $item['price'];
        $totalPrice += $subtotal;
        echo "<li>" . htmlspecialchars($item['name']) . " - Quantity: " . $item['quantity'] . " - Price: $" . number_format($item['price'], 2) . " - Subtotal: $" . number_format($subtotal, 2) . "</li>";
    }
    echo "</ul>";
    echo "<strong>Total: $" . number_format($totalPrice, 2) . "</strong>";
    echo "</div>";
}

if (isset($_SESSION['order_processed']) && $_SESSION['order_processed']) {
    displayReceipt($_SESSION['receipt']);
} else {
    echo "<p>No order to display or order has already been processed.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <!-- Add any additional CSS or head elements here -->
</head>
<body>
    <!-- The receipt will be displayed by the displayReceipt function -->
    <a href="finished.php">Go Home</a> <!-- Add a link to process the order -->

</body>
</html>

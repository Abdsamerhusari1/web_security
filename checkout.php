<?php
session_start();
require_once('backend/db_connect.php'); // Ensure this path is correct

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orderDetails'])) {
    // Decode the order details
    $orderDetails = json_decode(base64_decode($_POST['orderDetails']), true);
    
    // Calculate the total amount
    $totalAmount = 0;
    foreach ($orderDetails as $item) {
        $totalAmount += $item['quantity'] * $item['price'];
    }
}
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <!-- Include your CSS files here -->
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation and Other HTML Elements -->

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto">
            <?php if(isset($orderDetails)): ?>
                <h2 class="text-2xl font-bold my-4">Checkout</h2>
                <p>Total Amount: $<?= number_format($totalAmount, 2) ?></p>
                <form action="process_payment.php" method="post">
                    <input type='hidden' name='orderDetails' value='<?= base64_encode(json_encode($orderDetails)) ?>'>
                    <label for="publicKey">Your Public Key:</label><br>
                    <input type="text" id="publicKey" name="publicKey" required class="p-2 border rounded"><br><br>
                    <label for="privateKey">Your Private Key:</label><br>
                    <input type="password" id="privateKey" name="privateKey" required class="p-2 border rounded"><br><br>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Make Payment</button>
                </form>
            <?php else: ?>
                <p class="text-red-500">Error: No order details found. Please try again.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer HTML -->

    <?php $conn->close(); ?>
</body>
</html>

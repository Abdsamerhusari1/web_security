<?php
session_start();
require_once('backend/db_connect.php'); // Adjust this path as necessary

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $publicKeyFile = $_FILES['publicKey']['tmp_name'];
    $signature = $_SESSION['signature'] ?? $_POST['signature'];
    $orderDetailsJson = $_SESSION['orderDetails'] ?? $_POST['orderDetails'];
    $userId = $_SESSION['user_id'];

    // Call the Python verification script
    exec("python3 verify_transaction.py $publicKeyFile $signature '$orderDetailsJson'", $output, $return_var);

    if ($return_var == 0) {
        // Decode JSON order details
        $orderDetails = json_decode($orderDetailsJson, true);

        // Insert order into 'orders' table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $totalPrice = array_sum(array_map(function($item) {
            return $item['quantity'] * $item['price'];
        }, $orderDetails));
        $stmt->bind_param("id", $userId, $totalPrice);
        $stmt->execute();
        $orderId = $conn->insert_id;

        foreach ($orderDetails as $productId => $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $productId, $item['quantity'], $item['price']);
            $stmt->execute();
        }

        // Set session variables for success and receipt data
        $_SESSION['order_success'] = true;
        $_SESSION['receipt'] = $orderDetails;

        // Redirect to avoid form resubmission
        header("Location: order_confirmation.php?order_success=true");
        exit();
    } else {
        echo "<p>Verification Failed! Error: " . htmlspecialchars(implode("\n", $output)) . "</p>";
    }
} 
if (isset($_GET['order_success']) && $_SESSION['order_success']) {
    // Display the receipt and clear session variables
    displayReceipt($_SESSION['receipt']);
    unset($_SESSION['order_success'], $_SESSION['receipt'], $_SESSION['cart']);
} else {
    // Display the form for uploading the public key
    $signature = $_GET['signature'] ?? '';
    $orderDetails = $_GET['orderDetails'] ?? '';
    $_SESSION['signature'] = $signature;
    $_SESSION['orderDetails'] = $orderDetails;

    echo "<form action='verify_transaction.php' method='post' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='signature' value='$signature'>";
    echo "<input type='hidden' name='orderDetails' value='$orderDetails'>";
    echo "<label>Upload Public Key:</label><br>";
    echo "<input type='file' name='publicKey' required><br><br>";
    echo "<input type='submit' value='Verify Transaction'>";
    echo "</form>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Transaction</title>
    <!-- Add any additional CSS or head elements here -->
</head>
<body>
    <!-- Your page content -->
</body>
</html>

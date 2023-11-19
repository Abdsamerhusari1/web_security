<?php
session_start();

// Check if the order has been processed
if (isset($_SESSION['order_processed']) && $_SESSION['order_processed']) {
    // The order has already been processed
    header("Location: index.php");
    exit(); // Stop further execution
}

// Unset the session variables
unset($_SESSION['order_processed']);
unset($_SESSION['receipt']);
unset($_SESSION['cart']);

// Set a flag indicating that the order has been processed
$_SESSION['order_processed'] = true;

// Redirect the user back to index.php
header("Location: index.php");
exit(); // Stop further execution
?>

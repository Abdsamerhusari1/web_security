<?php
session_start();
require_once('backend/db_connect.php'); // Ensure this path is correct

// Function to send a blockchain transaction
function sendBlockchainTransaction($sender, $recipient, $amount) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/transactions/new");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $postData = json_encode(array('sender' => $sender, 'recipient' => $recipient, 'amount' => $amount));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to mine a block
function mineBlock() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/mine");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Function to get the webshop's public key
function getWebshopPublicKey($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    return file_get_contents($filePath);
}

// Function to validate blockchain transaction
function validateBlockchainTransaction($transactionId) {
    $ch = curl_init();

    // Replace with the actual URL of your blockchain transaction validation endpoint
    // This URL is just an example and will need to be adjusted based on your blockchain setup
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/transaction/validate/" . $transactionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);

    var_dump($response);
    if(curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return false;
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    // Assuming the blockchain returns a response with a 'valid' field indicating the transaction status
    if (isset($responseData['valid']) && $responseData['valid'] === true) {
        return true;
    } else {
        return false;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and decode the order details
    $orderDetails = json_decode(base64_decode($_POST['orderDetails']), true);
    $publicKey = $_POST['publicKey'];
    $privateKey = $_POST['privateKey']; // Note: Handling private keys like this is not secure in a real-world application.

    // Calculate the total amount
    $totalAmount = 0;
    foreach ($orderDetails as $item) {
        $totalAmount += $item['quantity'] * $item['price'];
    }

    // Get webshop's public key
    $webshopPublicKey = getWebshopPublicKey('store_public_key.pem');
    if (!$webshopPublicKey) {
        die("Error: Unable to read the webshop's public key.");
    }

    // Send transaction to blockchain
// Send transaction to blockchain
    
    $transactionResponse = sendBlockchainTransaction($publicKey, $webshopPublicKey, $totalAmount);
    echo "<pre>Transaction Response: "; print_r($transactionResponse); echo "</pre>"; // Debugging line


    
    if (isset($transactionResponse['message']) && strpos($transactionResponse['message'], 'Transaction will be added to Block') !== false) {
        $miningResponse = mineBlock();

        var_dump($miningResponse);
        if (isset($miningResponse['message']) && $miningResponse['message'] == 'New Block Forged') {
            // Retrieve the transaction ID from the response
            $transactionId = $transactionResponse['transaction_id'] ?? null; // Check if the transaction ID is set
    
            echo "                  ";
            echo $transactionId;

            if ($transactionId && validateBlockchainTransaction($transactionId)) {
                echo "<h2>Payment Successful</h2>";
                echo "<p>Transaction ID: " . htmlspecialchars($transactionId) . "</p>";
                echo "<p>Total Paid: $" . htmlspecialchars($totalAmount) . "</p>";
            } else {
                echo "<p>Transaction validation failed.</p>";
            }
        } else {
            echo "<p>Block mining failed.</p>";
        }
    } else {
        echo "<p>Transaction failed.</p>";
    }
    
} else {
    echo "<p>No payment data received.</p>";
}

// ... [Rest of the script, such as HTML footer] ...
?>


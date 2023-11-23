<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once('backend/db_connect.php'); // Ensure this path is correct

function getKey($filePath) {
    if (!file_exists($filePath)) {
        die("Error: Unable to read the webshop's public key.");
    }
    return file_get_contents($filePath);
}


function signTransaction($data, $privateKeyPem) {
    echo "<pre>$privateKeyPem</pre>";
    $privateKey = openssl_pkey_get_private($privateKeyPem);
    if (!$privateKey) {
        die('Invalid private key format or private key is incorrect');
    }
    openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    return base64_encode($signature); // Encode signature in Base64
}


function sendBlockchainTransaction($sender, $recipient, $amount, $signature) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/transactions/new");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $postData = json_encode(array(
        'sender' => $sender, 
        'recipient' => $recipient, 
        'amount' => $amount, 
        'signature' => $signature
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function mineBlock() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/mine");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function validateBlockchainTransaction($transactionId) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:5002/transaction/validate/" . $transactionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return false;
    }
    curl_close($ch);
    $responseData = json_decode($response, true);
    if (isset($responseData['valid']) && $responseData['valid'] === true) {
        return true;
    } else {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderDetails = json_decode(base64_decode($_POST['orderDetails']), true);
    $webshopPublicKey = getKey('store_public_key.pem');

    if (isset($_FILES['publicKeyFile']) && isset($_FILES['privateKeyFile'])) {
        // Read the contents of the public key file
        $publicKey = file_get_contents($_FILES['publicKeyFile']['tmp_name']);
        
        // Read the contents of the private key file
        $privateKey = file_get_contents($_FILES['privateKeyFile']['tmp_name']);

        $totalAmount = 0;
        foreach ($orderDetails as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Simulate signing of the transaction
        $transactionData = json_encode($orderDetails); // Data to be signed
        $signature = signTransaction($transactionData, $privateKey); // Sign the transaction data

        echo($signature);
        $transactionResponse = sendBlockchainTransaction($publicKey, $webshopPublicKey, $totalAmount, $signature);

        if (isset($transactionResponse['message']) && strpos($transactionResponse['message'], 'Transaction will be added to Block') !== false) {
            $transactionId = $transactionResponse['transaction_id'] ?? null; // Retrieve the transaction ID from the response

            if ($transactionId) {
                $miningResponse = mineBlock();

                if (isset($miningResponse['message']) && $miningResponse['message'] == 'New Block Forged') {
                    if (validateBlockchainTransaction($transactionId)) {
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
                echo "<p>Transaction ID not found in response.</p>";
            }
        } else {
            echo "<p>Transaction failed.</p>";
        }
    } else {
        echo "<p>Error: Public or Private Key file missing.</p>";
    }
} else {
    echo "<p>No payment data received.</p>";
}
?>

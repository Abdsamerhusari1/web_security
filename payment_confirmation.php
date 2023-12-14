<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}
session_start();

if (!isset($_SESSION['orderDetails']) || !isset($_SESSION['totalAmount'])) {
    echo "<p class='text-red-500 text-center mt-8'>Error: Payment details not found.</p>";
    exit;
}

$orderDetails = $_SESSION['orderDetails'];
$totalAmount = $_SESSION['totalAmount'];
$username = $_SESSION['username'] ?? 'User'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #3498db, #2980b9); 
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 50vh; 
        }

        .logo {
            font-size: 48px; 
            font-weight: bold;
            color: #fff; 
            margin-bottom: 20px;
        }

        .card-container {
            text-align: center;
        }

        .card {
            display: inline-block;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-200">
    <script>
        function confirmPayment() {
            const paymentStatus = document.getElementById('paymentStatus');
            paymentStatus.innerText = 'Processing payment...';
            paymentStatus.classList.add('text-blue-500','animate__animated', 'animate__flash');

            setTimeout(function() {
                paymentStatus.innerText = 'Payment Successful!';
                paymentStatus.classList.remove('text-blue-500'); // Remove previous color
                paymentStatus.classList.add('text-green-500', 'animate__animated', 'animate__bounceIn');

                document.getElementById('paymentDetails').className = 'text-red-500';
                document.getElementById('paymentDetails').innerText = '<?= htmlspecialchars($username) ?> sent $<?= number_format($totalAmount, 2) ?> to Webshop';

                setTimeout(function() {
                    window.location.href = 'handling_order.php';
                }, 5000);
            }, 5000);
        }
    </script>
    <div class="logo-container">
        <div class="logo">Web Security Payment System</div>
    </div>
    <div class="card-container">
        <div class="card bg-white p-20 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold mt-2" id="paymentStatus">Awaiting Payment Confirmation...</h2>
            <p class="text-lg mt-4" id="paymentDetails"></p>
        </div>
    </div>
    <script>
        confirmPayment();
    </script>
</body>
</html>

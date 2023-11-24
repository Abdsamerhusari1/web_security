<?php
session_start();

// Check if the order details and total amount are available in the session
if (!isset($_SESSION['orderDetails']) || !isset($_SESSION['totalAmount'])) {
    echo "<p class='text-red-500 text-center mt-8'>Error: Payment details not found.</p>";
    exit;
}

$orderDetails = $_SESSION['orderDetails'];
$totalAmount = $_SESSION['totalAmount'];
$username = $_SESSION['username'] ?? 'User'; // Replace with the actual username
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Include Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Add CSS styles here */
        body {
            background: linear-gradient(to bottom, #3498db, #2980b9); /* Replace with your desired gradient colors */
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 50vh; /* Adjust the height as needed */
        }

        .logo {
            font-size: 48px; /* Adjust the font size as needed */
            font-weight: bold;
            color: #fff; /* Text color for the logo */
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
            // Animate the "Processing payment..." message
            const paymentStatus = document.getElementById('paymentStatus');
            paymentStatus.innerText = 'Processing payment...';
            paymentStatus.classList.add('text-blue-500','animate__animated', 'animate__flash');

            setTimeout(function() {
                // Update the message and animate it
                paymentStatus.innerText = 'Payment Successful!';
                paymentStatus.classList.remove('text-blue-500'); // Remove previous color
                paymentStatus.classList.add('text-green-500', 'animate__animated', 'animate__bounceIn');

                // Display payment details
                document.getElementById('paymentDetails').className = 'text-red-500';
                document.getElementById('paymentDetails').innerText = '<?= htmlspecialchars($username) ?> sent $<?= number_format($totalAmount, 2) ?> to Webshop';

                setTimeout(function() {
                    // Redirect to receipt page
                    window.location.href = 'receipt.php';
                }, 5000); // Display success message for 5 seconds before redirecting
            }, 5000); // Simulate a 10-second payment processing delay
        }
    </script>
    <!-- Logo and Card Containers -->
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
        // Call confirmPayment() when the page loads
        confirmPayment();
    </script>
</body>
</html>

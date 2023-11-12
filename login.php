<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
	
<title>Login</title>

<?php
session_start();
require_once('backend/db_connect.php'); // Adjust the path as needed

define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

$errorMessage = "";
$maxLoginAttempts = 3;
$lockoutTime = 30 * 60; // 30 minutes in seconds

// Check if redirected from another page to login and store the redirection page
$redirect = isset($_GET['from']) && $_GET['from'] === 'checkout' ? 'cart.php' : 'index.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = trim($_POST["username"]);
	$password = trim($_POST["password"]);

	// User-specific lockout check
	if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username]['count'] >= $maxLoginAttempts) {
		$timeSinceLastAttempt = time() - $_SESSION['login_attempts'][$username]['last_attempt_time'];
		if ($timeSinceLastAttempt < $lockoutTime) {
			$errorMessage = "Too many failed login attempts. Please try again after 30 minutes.";
		} else {
			// Reset attempts if the lockout time has passed
			$_SESSION['login_attempts'][$username]['count'] = 0;
		}
	}

	if (empty($errorMessage) && !empty($username) && !empty($password)) {
		$stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
		$stmt->bind_param("s", $username);
		$stmt->execute();
		$stmt->store_result();

		if ($stmt->num_rows == 1) {
			$stmt->bind_result($user_id, $password_hash);
			$stmt->fetch();

			// Verify the password with the pepper
			if (password_verify($password . PEPPER, $password_hash)) {
				// Password is correct
				// Reset the login attempts for the user
				unset($_SESSION['login_attempts'][$username]);

				// Start a new session
				session_regenerate_id();
				$_SESSION['loggedin'] = true;
				$_SESSION['user_id'] = $user_id;
				$_SESSION['username'] = $username;

				// Redirect to the previous page if the 'from' query parameter is present and equals 'checkout'
				if ($redirect === 'cart.php') {
					header("Location: cart.php");
					exit;
				} else {
					header("Location: index.php"); // Default redirect to home page
					exit;
				}
				exit;
			} else {
				// Password is not correct
				// Initialize or increment the login attempts counter
				$_SESSION['login_attempts'][$username]['count'] = ($_SESSION['login_attempts'][$username]['count'] ?? 0) + 1;
				$_SESSION['login_attempts'][$username]['last_attempt_time'] = time();

				$attemptsLeft = $maxLoginAttempts - $_SESSION['login_attempts'][$username]['count'];
				$errorMessage = "Invalid username or password. Attempts left: " . $attemptsLeft;

				// Check if the user should be locked out
				if ($_SESSION['login_attempts'][$username]['count'] >= $maxLoginAttempts) {
					$_SESSION['login_attempts'][$username]['last_attempt_time'] = time();
					$errorMessage = "Too many failed login attempts. Please try again after 30 minutes.";
				}
			}
		} else {
			$errorMessage = "Invalid username or password.";
		}

		$stmt->close();
	}

	$conn->close();
}
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex flex-col min-h-screen bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gray-800 p-4 text-white">
        <div class="container mx-auto flex justify-between">
            <div class="text-lg">Group 2 Shop</div>
            <div>
                <a href="index.php" class="px-3 hover:text-gray-300">Home</a>
                <a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
	<div class="flex-grow container mx-auto px-4 mt-8">
        <h1 class="text-2xl font-bold text-center">User Login</h1>
        
		<form action="login.php<?php echo !empty($_GET['from']) ? '?from=' . htmlspecialchars($_GET['from']) : ''; ?>" method="post" class="max-w-md mx-auto mt-4">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                
                <?php 
                if (!empty($errorMessage)) {
                    echo '<p class="text-red-500 text-sm">' . htmlspecialchars($errorMessage) . '</p>';
                }
                ?>
            </div>

            <div class="flex items-center justify-between">
                <input type="submit" value="Login" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
				<a href="register.php" class="text-md bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Register</a>
			</div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
</body>
</html>
<?php
// Check if the connection is not secure (HTTP) and redirect to HTTPS if needed.
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}

// Display an error message if it is set and not empty.
if (isset($error_message) && !empty($error_message)) {
    echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Login</title>
<?php
/*session_set_cookie_params([
    'lifetime' => 1800, // match your session timeout
    'path' => '/',
    'domain' => '',
    'secure' => true, // Send cookie only over HTTPS
    'httponly' => true, // Prevent client-side access to the cookie
    'samesite' => 'Strict', // prevents cookie from being sent by the browser with cross-site requests. Prevents CSRF-attacks
]); */
/*HTTPOnly Cookies: HTTPOnly cookies cannot be accessed through JavaScript,
which makes them more resilient to XSS attacks. This is set using the httponly flag in the setcookie function
setcookie("name", "value", ["httponly" => true]);
*/

/*
Set Cookie with Secure Flag: If your site is HTTPS only (which is recommended), ensure all cookies have the Secure flag set:
setcookie("name", "value", ["secure" => true]);
*/

//Secure cookies are only sent over HTTPS connections.
setcookie("name", "value", ["secure" => true, "httponly" => true, "samesite" => "strict"]);
// Start or resume a session
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once('backend/db_connect.php'); 

// Define a secret pepper value for password hashing.
define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

// Initialize an empty error message string
$errorMessage = "";
// Set the maximum allowed login attempts
$maxLoginAttempts = 3;
// Define lockout time duration in seconds (30 minutes)
$lockoutTime = 30 * 60;

$redirect = isset($_GET['from']) && $_GET['from'] === 'checkout' ? 'cart.php' : 'index.php';

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the CSRF token is valid.
    if (!isset($_POST['csrf_token'])) {
        die('ERROR');
    } else if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Check if the user has reached the maximum login attempts and implement a lockout mechanism.
    if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username]['count'] >= $maxLoginAttempts) {
        $timeSinceLastAttempt = time() - $_SESSION['login_attempts'][$username]['last_attempt_time'];
        if ($timeSinceLastAttempt < $lockoutTime) {
            $errorMessage = "Too many failed login attempts. Please try again after 30 minutes.";
        } else {
            $_SESSION['login_attempts'][$username]['count'] = 0;
        }
    }

    // Validate the username and password.
    if (empty($errorMessage) && !empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $password_hash);
            $stmt->fetch();

            // Verify the password with the appended pepper
            if (password_verify($password . PEPPER, $password_hash)) {
                // Reset the login attempts for the user upon successful login
                unset($_SESSION['login_attempts'][$username]);

                // Start a new session.
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;

                // Redirect to the previous page if the 'from' query parameter is present and equals 'checkout'.
                if ($redirect === 'cart.php') {
                    header("Location: cart.php");
                    exit;
                } else {
                    header("Location: index.php"); // Default redirect to home page.
                    exit;
                }
                exit;
            } else {
                // Password is not correct.
                // Initialize or increment the login attempts counter.
                $_SESSION['login_attempts'][$username]['count'] = ($_SESSION['login_attempts'][$username]['count'] ?? 0) + 1;
                $_SESSION['login_attempts'][$username]['last_attempt_time'] = time();

                $attemptsLeft = $maxLoginAttempts - $_SESSION['login_attempts'][$username]['count'];
                $errorMessage = "Invalid username or password. Attempts left: " . $attemptsLeft;

                // Check if the user should be locked out.
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
    <!-- Set character encoding for the webpage -->
    <meta charset="UTF-8">
    <!-- Set the title of the web page -->
    <title>Login</title>
    <!-- Include Tailwind CSS for styling -->
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
                    <input type='hidden' name='csrf_token' value='<?= $_SESSION['csrf_token'] ?>'>
                    <button type="submit" class="ml-3 flex-shrink-0 px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-r-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">Search</button>
                </form>
            </div>
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
                <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                <div style="display: none;"><?php echo htmlspecialchars($_POST['username']); ?></div>
            </div>

        <!-- Password input -->
        <div class="mb-6">
            <!-- Label for password -->
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <!-- Text field for password -->
            <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">

            <!-- Display error message if any -->
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                echo '<p style="color: blue;">Username Entered: ' . htmlspecialchars($username) . '</p>';
            }
            
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

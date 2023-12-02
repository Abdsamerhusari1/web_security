<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
} 
?>

<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
	
<title>Login</title>
<?php
/*HTTPOnly Cookies: HTTPOnly cookies cannot be accessed through JavaScript,
which makes them more resilient to XSS attacks. This is set using the httponly flag in the setcookie function
setcookie("name", "value", ["httponly" => true]);
*/

/*
Set Cookie with Secure Flag: If your site is HTTPS only (which is recommended), ensure all cookies have the Secure flag set:
setcookie("name", "value", ["secure" => true]);
*/

//Secure cookies are only sent over HTTPS connections.
//setcookie("name", "value", ["secure" => true, "httponly" => true, "samesite" => "strict"]);
// Start or resume a session
session_start();



// Include the database connection script
require_once('backend/db_connect.php'); // Adjust the path as needed

// Define a constant to be used as a pepper in password hashing
define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

// Initialize an empty error message string
$errorMessage = "";
// Set the maximum allowed login attempts
$maxLoginAttempts = 3;
// Define lockout time duration in seconds (30 minutes)
$lockoutTime = 30 * 60;

// Check if redirected from another page to login and store the redirection page
$redirect = isset($_GET['from']) && $_GET['from'] === 'checkout' ? 'cart.php' : 'index.php';

// Check if the form was submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and trim the username from the POST data
    $username = trim(($_POST["username"]));
    // Get and trim the password from the POST data
    $password = trim(($_POST["password"]));

    // Check if the user has exceeded the maximum login attempts
    if (isset($_SESSION['login_attempts'][$username]) && $_SESSION['login_attempts'][$username]['count'] >= $maxLoginAttempts) {
        // Calculate the time since the last login attempt
        $timeSinceLastAttempt = time() - $_SESSION['login_attempts'][$username]['last_attempt_time'];
        // Check if the user is still within the lockout time
        if ($timeSinceLastAttempt < $lockoutTime) {
            // Set the error message for too many attempts
            $errorMessage = "Too many failed login attempts. Please try again after 30 minutes.";
        } else {
            // Reset login attempts if the lockout time has passed
            $_SESSION['login_attempts'][$username]['count'] = 0;
        }
    }

    // Check if there are no errors and both username and password are provided
    if (empty($errorMessage) && !empty($username) && !empty($password)) {
        // Prepare a statement for user authentication
        $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
        // Bind the username parameter to the prepared statement
        $stmt->bind_param("s", $username);
        // Execute the prepared statement
        $stmt->execute();
        // Store the result to check the number of rows returned
        $stmt->store_result();

        // Check if exactly one user is found
        if ($stmt->num_rows == 1) {
            // Bind the result to variables
            $stmt->bind_result($user_id, $password_hash);
            // Fetch the result
            $stmt->fetch();

            // Verify the password with the appended pepper
            if (password_verify($password . PEPPER, $password_hash)) {
                // Reset the login attempts for the user upon successful login
                unset($_SESSION['login_attempts'][$username]);

                // Regenerate the session ID
                session_regenerate_id();
                // Set session variables for logged-in status, user ID, and username
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;

                // Redirect to the previous page based on 'from' query parameter
                if ($redirect === 'cart.php') {
                    // Redirect to the cart page
                    header("Location: cart.php");
                    // Terminate the script
                    exit;
                } else {
                    // Default redirect to the home page
                    header("Location: index.php");
                    // Terminate the script
                    exit;
                }
                // Terminate the script (redundant, can be removed)
                exit;
            } else {
                // Increment the login attempts counter for failed login
                $_SESSION['login_attempts'][$username]['count'] = ($_SESSION['login_attempts'][$username]['count'] ?? 0) + 1;
                $_SESSION['login_attempts'][$username]['last_attempt_time'] = time();

                // Calculate the remaining attempts
                $attemptsLeft = $maxLoginAttempts - $_SESSION['login_attempts'][$username]['count'];
                // Set the error message for invalid login
                $errorMessage = "Invalid username or password. Attempts left: " . $attemptsLeft;

                // Check if the user should be locked out
                if ($_SESSION['login_attempts'][$username]['count'] >= $maxLoginAttempts) {
                    // Set the time of the last attempt for lockout
                    $_SESSION['login_attempts'][$username]['last_attempt_time'] = time();
                    // Set the error message for too many attempts
                    $errorMessage = "Too many failed login attempts. Please try again after 30 minutes.";
                }
            }
        } else {
            // Set the error message for invalid username or password
            $errorMessage = "Invalid username or password.";
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
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
        <!-- Brand or company name -->
        <div class="text-lg">Group 2 Shop</div>
        <!-- Navigation links -->
        <div>
            <!-- Link to the home page -->
            <a href="index.php" class="px-3 hover:text-gray-300">Home</a>
            <!-- Link to the cart page -->
            <a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
            <!-- Conditional display of login/logout based on user session -->
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <!-- Link to the logout page -->
                <a href="logout.php" class="px-3 hover:text-gray-300">Logout</a>
            <?php else: ?>
                <!-- Link to the login page -->
                <a href="login.php" class="px-3 hover:text-gray-300">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Main Content Area -->
<div class="flex-grow container mx-auto px-4 mt-8">
    <!-- Page title -->
    <h1 class="text-2xl font-bold text-center">User Login</h1>

    <!-- Login form -->
    <form action="login.php<?php echo !empty($_GET['from']) ? '?from=' . ($_GET['from']) : ''; ?>" method="post" class="max-w-md mx-auto mt-4">
        <!-- Username input -->
        <div class="mb-4">
            <!-- Label for username -->
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
            <!-- Text field for username -->
            <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">

        </div>

        <!-- Password input -->
        <div class="mb-6">
            <!-- Label for password -->
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <!-- Text field for password -->
            <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">

            <!-- Display error message if any -->
            <?php
            if (!empty($errorMessage)) {
                echo '<p class="text-red-500 text-sm">' . ($errorMessage) . '</p>';
            }

            ?>
            </div>

            <div class="flex items-center justify-between">
                <input type="submit" value="Login" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
				<a href="register.php" class="text-md bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Register</a>
			</div>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            echo '<p class="text-gray-700 mt-2">Username Entered: ' . ($username) . '</p>';
        }
        ?>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        © 2023 Group 2 Shop
    </footer>
</body>
</html>





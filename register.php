<?php
// Redirect to HTTPS if the request is not secure
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
}
?>

<?php
// Display an error message if it is set
if (isset($error_message) && !empty($error_message)) {
    echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<title>Register</title>

<?php
// Start the session
session_start();

// Include database connection script
require_once('backend/db_connect.php');

// Define a constant for the pepper used in password hashing
define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

// Function to hash the password with a pepper
function pepperedHash($password) {
    return password_hash($password . PEPPER, PASSWORD_DEFAULT);
}

// Function to check if the password is strong enough
function isPasswordStrongEnough($password, $username, $address) {
    // Check for minimum length
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }

    // Check for character diversity
    if (!preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W]/', $password)) {
        return "Password must include uppercase and lowercase letters, numbers, and special characters.";
    }

    // Check for personal information in password
    if (strpos($password, $username) !== false || strpos($password, $address) !== false) {
        return "Password should not contain your username or address.";
    }

    // Check for sequential characters
    $length = strlen($password);
    for ($i = 0; $i < $length - 2; $i++) {
        // Numerical sequence check
        if (is_numeric($password[$i]) &&
            is_numeric($password[$i + 1]) &&
            is_numeric($password[$i + 2])) {
            if (($password[$i + 1] == $password[$i] + 1) &&
                ($password[$i + 2] == $password[$i] + 2)) {
                return "Password should not contain sequential numerical characters.";
            }
        }

        // Alphabetical sequence check
        if (ctype_alpha($password[$i]) &&
            ctype_alpha($password[$i + 1]) &&
            ctype_alpha($password[$i + 2])) {
            if ((ord(strtolower($password[$i + 1])) == ord(strtolower($password[$i])) + 1) &&
                (ord(strtolower($password[$i + 2])) == ord(strtolower($password[$i])) + 2)) {
                return "Password should not contain sequential alphabetical characters.";
            }
        }
    }

    // Check for repetitive characters
    for ($i = 0; $i < $length - 2; $i++) {
        if ($password[$i] == $password[$i + 1] && $password[$i] == $password[$i + 2]) {
            return "Password should not contain repetitive characters.";
        }
    }

    // Load a blacklist of passwords
    $blacklist = file('blacklist/password-blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if the password is in the blacklist
    foreach ($blacklist as $blacklistedWord) {
        if (strpos($password, $blacklistedWord) !== false) {
            return "The password cannot contain common words or patterns (e.g., 'password').";
        }
    }

    // Return true if the password passes all checks
    return true;
}

// Initialize error and success message variables
$errorMessage = "";
$successMessage = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture and trim the user input
    $username = trim(htmlspecialchars($_POST["username"]));
    $password = trim(htmlspecialchars($_POST["password"]));
    $address = trim(htmlspecialchars($_POST["address"]));

    // Check if all fields are filled
    if (empty($username) || empty($password) || empty($address)) {
        $errorMessage = "Please fill in all fields.";
    } else {
        // Check password strength
        $passwordStrengthCheck = isPasswordStrongEnough($password, $username, $address);
        if ($passwordStrengthCheck !== true) {
            $errorMessage = $passwordStrengthCheck;
        } else {
            // Check if the username already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            // If the username exists, show an error message
            if ($stmt->num_rows > 0) {
                $errorMessage = "Username already taken.";
            } else {
                // Hash the password
                $password_hash = pepperedHash($password);

                // Insert the new user into the database
                $insert = $conn->prepare("INSERT INTO users (username, password_hash, address) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $username, $password_hash, $address);

                // Execute the insertion and check for success
                if ($insert->execute()) {
                    // Set session variables and redirect to the homepage
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $conn->insert_id; // Get the new user's ID
                    $_SESSION['username'] = $username;

                    // Store a success message in a session variable
                    $_SESSION['successMessage'] = "You have registered successfully and are now logged in.";
                    header("Location: index.php");
                    exit;
                } else {
                    // Display an error message in case of a database error
                    $errorMessage = "Error: " . $conn->error;
                }

                // Close the statement
                $insert->close();
            }

            // Close the statement
            $stmt->close();
        }
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html class="h-full">
	<head>
		<meta charset="UTF-8">
		<title>Register</title>
		<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	</head>
	<body class="bg-gray-100 flex flex-col min-h-screen">
		<!-- Navigation Bar -->
		<nav class="bg-gray-800 p-4 text-white">
			<div class="container mx-auto flex justify-between">
				<div class="text-lg">Group 2 Shop</div>
				<div>
					<a href="index.php" class="px-3 hover:text-gray-300">Home</a>
					<a href="cart.php" class="px-3 hover:text-gray-300">Cart</a>
					<a href="login.php" class="px-3 hover:text-gray-300">Login</a>
				</div>
			</div>
		</nav>

		<!-- Main Content Area -->
		<div class="flex-grow container mx-auto px-4 py-8">
			<div class="max-w-md mx-auto bg-white p-6 shadow-md">
				<h1 class="text-2xl font-bold text-center mb-4">User Registration</h1>

				<?php 
				if (!empty($errorMessage)) {
					echo '<p class="text-red-500 text-center">' . htmlspecialchars($errorMessage) . '</p>';
				}
				if (!empty($successMessage)) {
					echo '<p class="text-green-500 text-center">' . htmlspecialchars($successMessage) . '</p>';
				}
				?>

				<form action="register.php" method="post" class="space-y-4">
					<div>
						<label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
						<input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
					</div>

					<div>
						<label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
						<input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
					</div>

					<div>
						<label for="address" class="block text-gray-700 text-sm font-bold mb-2">Home Address:</label>
						<textarea id="address" name="address" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
					</div>

					<div class="flex items-center justify-between">
						<input type="submit" value="Register" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
						<a href="login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">Already have an account?</a>
					</div>
                    <?php
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        echo '<p class="text-gray-700 mt-2">Username Entered: ' . htmlspecialchars($username) . '</p>';
                        echo '<p class="text-gray-700">Home Address Entered: ' . htmlspecialchars($address) . '</p>';
                    }
                    ?>
				</form>

			<!-- Password Criteria -->
			<div class="mt-6">
				<div class="mt-6 p-4 bg-white rounded shadow">
					<h3 class="text-md font-semibold mb-2">Password Requirements:</h3>
					<ul class="list-disc list-inside">
						<li>Minimum 8 characters long</li>
						<li>Must include uppercase and lowercase letters, numbers, and special characters</li>
						<li>Should not contain your username or address</li>
						<li>Should not contain sequential or repetitive characters (like '123' or 'aaa')</li>
						<li>Should not contain any common words or patterns (e.g., 'password')</li>
					</ul>
				</div>
			</div>
	</body>
</html>
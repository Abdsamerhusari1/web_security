<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
	<title>Register</title>
	<?php

	require_once('backend/db_connect.php');
	require_once 'receipt/KeyGenerator.php'; 


	define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

	// salting by PASSWORD_DEFAULT which also use (default hashing algorithm bcrypt) and pepper
	function pepperedHash($password) {
		return password_hash($password . PEPPER, PASSWORD_DEFAULT);
	}

	function isPasswordStrongEnough($password, $username, $address) {
		// Minimum Length Check
		if (strlen($password) < 8) {
			return "Password must be at least 8 characters long.";
		}

		// Character Diversity Check
		if (!preg_match('/[A-Z]/', $password) ||
			!preg_match('/[a-z]/', $password) ||
			!preg_match('/[0-9]/', $password) ||
			!preg_match('/[\W]/', $password)) {
			return "Password must include uppercase and lowercase letters, numbers, and special characters.";
		}

		// Check for Personal Information
		if (strpos($password, $username) !== false || strpos($password, $address) !== false) {
			return "Password should not contain your username or address.";
		}

		// Sequential Characters Check
		$length = strlen($password);
		for ($i = 0; $i < $length - 2; $i++) {
			// Numerical sequence
			if (is_numeric($password[$i]) && 
				is_numeric($password[$i + 1]) && 
				is_numeric($password[$i + 2])) {
				if (($password[$i + 1] == $password[$i] + 1) && 
					($password[$i + 2] == $password[$i] + 2)) {
					return "Password should not contain sequential numerical characters.";
				}
			}

			// Alphabetical sequence
			if (ctype_alpha($password[$i]) && 
				ctype_alpha($password[$i + 1]) && 
				ctype_alpha($password[$i + 2])) {
				if ((ord(strtolower($password[$i + 1])) == ord(strtolower($password[$i])) + 1) && 
					(ord(strtolower($password[$i + 2])) == ord(strtolower($password[$i])) + 2)) {
					return "Password should not contain sequential alphabetical characters.";
				}
			}
		}

		// Repetitive Characters Check
		for ($i = 0; $i < $length - 2; $i++) {
			if ($password[$i] == $password[$i + 1] && $password[$i] == $password[$i + 2]) {
				return "Password should not contain repetitive characters.";
			}
		}

		// Load password blacklist
		$blacklist = file('blacklist/password-blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		// Check if the password contains any word from the blacklist
		foreach ($blacklist as $blacklistedWord) {
			if (strpos($password, $blacklistedWord) !== false) {
				return "The password cannot contain common words or patterns (e.g., 'password').";
			}
		}

		return true;
	}

	$errorMessage = "";
	$successMessage = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$username = trim($_POST["username"]);
		$password = trim($_POST["password"]);
		$address = trim($_POST["address"]);

		if (empty($username) || empty($password) || empty($address)) {
			$errorMessage = "Please fill in all fields.";
		} else {
			// Validate password strength
			$passwordStrengthCheck = isPasswordStrongEnough($password, $username, $address);
			if ($passwordStrengthCheck !== true) {
				$errorMessage = $passwordStrengthCheck;
			} else {
				// Check if username already exists
				$stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
				$stmt->bind_param("s", $username);
				$stmt->execute();
				$stmt->store_result();

				if ($stmt->num_rows > 0) {
					$errorMessage = "Username already taken.";
				} else {
					// Hash the password with the pepper
					$password_hash = pepperedHash($password);

					$insert = $conn->prepare("INSERT INTO users (username, password_hash, address) VALUES (?, ?, ?)");
					$insert->bind_param("sss", $username, $password_hash, $address);
					
					if ($insert->execute()) {
						$userId = $conn->insert_id;
					
						// Generate keys
						$keys = KeyGenerator::generateKeys();
					
						// Save public key in users table
						$update = $conn->prepare("UPDATE users SET public_key = ? WHERE user_id = ?");
						$update->bind_param("si", $keys['publicKey'], $userId);
						$update->execute();
						$update->close();
					
						// Save private key in user_private_keys table
						$insertKey = $conn->prepare("INSERT INTO user_private_keys (user_id, private_key) VALUES (?, ?)");
						$insertKey->bind_param("is", $userId, $keys['privateKey']);
						$insertKey->execute();
						$insertKey->close();
					
						$successMessage = "User registered successfully.";
					} else {
						$errorMessage = "Error: " . $conn->error;
					}

					$insert->close();
				}

				$stmt->close();
			}
		}

		$conn->close();
	}
	?>

	<nav>
		<a href="products.php">View Products</a> | 
		<a href="register.php">Register</a> | 
		<a href="login.php">Login</a>
	</nav>


	<h1>User Registration</h1>

	<div>
		<?php 
		if (!empty($errorMessage)) {
			echo '<p style="color: red;">' . htmlspecialchars($errorMessage) . '</p>';
		}
		if (!empty($successMessage)) {
			echo '<p style="color: green;">' . htmlspecialchars($successMessage) . '</p>';
			echo '<form action="login.php" method="get">';
			echo '<input type="submit" value="Go to Login" />';
			echo '</form>';
		}
		?>
	</div>

	<form action="register.php" method="post">
		<label for="username">Username:</label><br>
		<input type="text" id="username" name="username" required><br>

		<label for="password">Password:</label><br>
		<input type="password" id="password" name="password" required><br>

		<label for="address">Home Address:</label><br>
		<textarea id="address" name="address" required></textarea><br>

		<input type="submit" value="Register">
	</form>

	<!-- Password Criteria -->
	<div>
		<h3>Password Requirements</h3>
		<ul>
			<li>Minimum 8 characters long</li>
			<li>Must include uppercase and lowercase letters, numbers, and special characters</li>
			<li>Should not contain your username or address</li>
			<li>Should not contain sequential or repetitive characters (like '123' or 'aaa')</li>
			<li>Should not contain any commen word (like 'password')</li>
		</ul>
	</div>


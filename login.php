<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <?php
    session_start();
    require_once('db_connect.php'); // Adjust the path as needed

    define('PEPPER', 'kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX');

    $errorMessage = "";
    $maxLoginAttempts = 3;
    $lockoutTime = 30 * 60; // 30 minutes in seconds

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

                    header("location: welcome.php"); // Redirect to welcome page
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

    <nav>
        <a href="products.php">View Products</a> | 
        <a href="register.php">Register</a> | 
        <a href="login.php">Login</a>
    </nav>

    <h1>User Login</h1>

    <?php 
    if (!empty($errorMessage)) {
        echo '<p style="color: red;">' . htmlspecialchars($errorMessage) . '</p>';
    }
    ?>

    <form action="login.php" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <?php
    session_start();
    require_once('db_connect.php'); // Adjust the path as needed

    $errorMessage = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);

        if (empty($username) || empty($password)) {
            $errorMessage = "Please enter both username and password.";
        } else {
            $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($user_id, $password_hash);
                $stmt->fetch();

                if (password_verify($password, $password_hash)) {
                    // Password is correct, start a new session
                    session_regenerate_id();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;

                    header("location: welcome.php"); // Redirect to welcome page
                    exit;
                } else {
                    $errorMessage = "Invalid username or password.";
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
        <a href="index.php">Home</a> | 
        <a href="register.php">Register</a>
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

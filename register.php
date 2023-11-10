<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <?php
    require_once('db_connect.php'); // Adjust this path as needed

    $errorMessage = "";
    $successMessage = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]);
        $address = trim($_POST["address"]);

        if (empty($username) || empty($password) || empty($address)) {
            $errorMessage = "Please fill in all fields.";
        } else {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = "Username already taken.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $conn->prepare("INSERT INTO users (username, password_hash, address) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $username, $password_hash, $address);
                
                if ($insert->execute()) {
                    $successMessage = "User registered successfully. <a href='login.php'>Login here</a>";
                } else {
                    $errorMessage = "Error: " . $conn->error;
                }

                $insert->close();
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

    <h1>User Registration</h1>

    <?php 
    if (!empty($errorMessage)) {
        echo '<p style="color: red;">' . htmlspecialchars($errorMessage) . '</p>';
    }
    if (!empty($successMessage)) {
        echo '<p style="color: green;">' . htmlspecialchars($successMessage) . '</p>';
    }
    ?>

    <form action="register.php" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>

        <label for="address">Home Address:</label><br>
        <textarea id="address" name="address" required></textarea><br>

        <input type="submit" value="Register">
    </form>
</body>
</html>

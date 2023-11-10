<?php
session_start();
require_once('db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        // Check credentials
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

                header("location: welcome.php"); // Redirect to a welcome page
            } else {
                echo "Invalid username or password.";
            }
        } else {
            echo "Invalid username or password.";
        }

        $stmt->close();
    } else {
        echo "Please enter username and password.";
    }
}

$conn->close();
?>

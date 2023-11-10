<?php
require_once('db_connect.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $address = trim($_POST["address"]);

    if (!empty($username) && !empty($password) && !empty($address)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, address) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password_hash, $address);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "User registered successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Please fill in all fields.";
    }
}

$conn->close();
?>

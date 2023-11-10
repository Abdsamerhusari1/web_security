<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.html");
    exit;
}

// You can access user's information from the session, like username
$username = $_SESSION["username"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    <p>This is the user dashboard page.</p>
    <!-- Add more user-specific content here -->

    <p><a href="logout.php">Logout</a></p>
</body>
</html>

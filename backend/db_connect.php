<?php
if (isset($error_message) && !empty($error_message)) {
	echo '<p style="color: red;">' . $error_message . '</p>';
}
?>
<?php


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webshop_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully";
?>

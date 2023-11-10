<?php
$password = 'hashed_password2'; // Replace with the actual password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo $hashed_password;
?>

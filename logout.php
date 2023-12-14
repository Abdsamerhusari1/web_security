<?php
/*if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $redirectURL = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $redirectURL");
    exit;
} */
?>

<?php
if (isset($error_message) && !empty($error_message)) {
    echo '<p style="color: red;">' . $error_message . '</p>';
}
?>

<title>Logout</title>

<?php
session_start();
$logoutSuccess = false;

if (isset($_SESSION)) {
    $_SESSION = array();

    session_destroy();

    $logoutSuccess = true;
}

header("Refresh: 3;url=login.php");
?>

<!DOCTYPE html>
<html class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <?php if ($logoutSuccess): ?>
            <p class="text-green-500 text-center">You have been successfully logged out. Redirecting to the login page...</p>
        <?php endif; ?>
    </div>
</body>
</html>

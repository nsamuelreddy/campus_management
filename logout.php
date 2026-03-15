<?php
session_start();

// 1. Verify CSRF token to ensure the request is intentional
if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    die("Invalid logout attempt.");
}

// 2. Unset all session variables
$_SESSION = [];

// 3. Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session
session_destroy();
header("Location: login.php");
exit();
?>
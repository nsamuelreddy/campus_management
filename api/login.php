<?php
session_start();
require_once 'config.php';

// If already logged in, go to dashboard
if (isset($_SESSION['user_token'])) {
    header("Location: ../dashboard.php");
    exit();
}

// Redirect the user to Google
$authUrl = $client->createAuthUrl();
header("Location: " . $authUrl);
exit();
?>
<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_token'])) {
    header("Location: ../dashboard.php");
    exit();
}


$authUrl = $client->createAuthUrl();
header("Location: " . $authUrl);
exit();
?>
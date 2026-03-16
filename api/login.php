<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_token'])) {
    header("Location: ../dashboard.php");
    exit();
}

// Generate Auth URL
$authUrl = $client->createAuthUrl();
?>

<div style="text-align: center; margin-top: 50px;">
    <h2>Campus Management System</h2>
    <a href="<?php echo $authUrl; ?>" style="padding: 15px 25px; background: #4285F4; color: white; text-decoration: none; border-radius: 5px;">
        Sign in with Google
    </a>
</div>
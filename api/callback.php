<?php
session_start();
require_once 'config.php';

// 1. Exchange authorization code for access token
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // Check if the token fetch was successful
    if (isset($token['error'])) {
        die("Error exchanging code for token: " . $token['error']);
    }

    $client->setAccessToken($token['access_token']);

    // 2. Fetch User Profile from Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $userEmail = $google_account_info->email;

    // 3. Database Check (Ensure $pdo is available)
    // You should include a db_connect.php here if $pdo isn't already globally available
    // require_once 'db_connect.php'; 

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $userEmail]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists: Grant access
        $_SESSION['user_token'] = $token['access_token'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        
        header("Location: ../dashboard.php");
        exit();
    } else {
        // Option: Register the user automatically, or show Access Denied
        die("Access Denied: Your email ($userEmail) is not registered in our database.");
    }
} else {
    // No code received from Google
    header("Location: login.php");
    exit();
}
?>
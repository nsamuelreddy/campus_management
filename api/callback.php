<?php
session_start();
require_once 'config.php';
include "../db.php";   // 

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (isset($token['error'])) {
        die("Error exchanging code for token: " . $token['error']);
    }

    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    
    $userEmail = $google_account_info->email;

    // (Keeping your DB logic same style, only compatible with mysqli)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['user_token'] = $token['access_token'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        
        // ✅ FIX: correct redirect path
        header("Location: dashboard.php");
        exit();
    } else {
        die("Access Denied: Your email ($userEmail) is not registered in our database.");
    }
} else {
    header("Location: login.php");
    exit();
}
?>
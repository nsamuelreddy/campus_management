<?php
require_once 'config.php';

if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token['access_token']);

  // Get user profile info
  $google_oauth = new Google_Service_Oauth2($client);
  $google_account_info = $google_oauth->userinfo->get();
  
  // Now you have: $google_account_info->email, name, id, picture
  $_SESSION['user_token'] = $token['access_token'];
  $_SESSION['user_email'] = $google_account_info->email;


// ... [Initial steps: session_start(), include library, verify state] ...

// 4. Fetch User Data from Google (Example)
$google_oauth = new Google_Service_Oauth2($client);
$google_account_info = $google_oauth->userinfo->get();
$userEmail = $google_account_info->email;

// --- INSERT YOUR CODE HERE ---
// Ensure $pdo is defined (either here or via included db.php)
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $userEmail]);
$user = $stmt->fetch();

if ($user) {
    // User exists! Grant access and set session roles
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_email'] = $user['email'];
    header("Location: dashboard.php");
    exit();
} else {
    // User not found in your database
    die("Access Denied: You are not registered in this system.");
}
// --- END OF INSERTION ---

  
  // Logic: Check if user exists in your campus_management DB, else register them.
  header("Location: dashboard.php");
}
?>
<?php
// 1. Only call session_start() once.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false, // Change to false for localhost development
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// 2. Regenerate ID safely
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

require_once 'vendor/autoload.php';

// 3. Insert your actual credentials
$clientID = '189352772372-vpqgvof6b6r3oo14fb5vhnlchedjogb3.apps.googleusercontent.com';
$clientSecret = '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';

// IMPORTANT: This URL must match what you put in Google Cloud Console exactly
$redirectUri = 'http://localhost:8000/api/callback.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
?>
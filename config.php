<?php
// Set secure session parameters BEFORE calling session_start()
session_set_cookie_params([
    'lifetime' => 0,              // Session lasts until browser closes
    'path' => '/',
    'domain' => '',               // Set to your domain if needed
    'secure' => true,             // Only send cookie over HTTPS
    'httponly' => true,           // Prevent JavaScript access
    'samesite' => 'Lax'           // Protect against CSRF
]);

session_start();

// Regenerate ID after login to prevent session fixation
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}
?>


<?php
require_once 'vendor/autoload.php';

session_start();

$clientID = 'YOUR_GOOGLT_CLIENT_ID';
$clientSecret = 'YOUR_GOOGLE_CLIENT_SECRET';
$redirectUri = 'http://localhost/campus_management/callback.php';

// Create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
?>
<?php
// Force errors to appear on the screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

// Try creating the client
$client = new Google_Client();
echo "Client created successfully.<br>";

$client->setClientId('189352772372-vpqgvof6b6r3oo14fb5vhnlchedjogb3.apps.googleusercontent.com'); 
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost:8000/api/callback.php');

echo "Configuration applied successfully.<br>";
exit(); // Stops here so we don't load other files
?>
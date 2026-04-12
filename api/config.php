<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';

$client = new Google_Client();
echo "Client created successfully.<br>";

$env = parse_ini_file(__DIR__.'/.env');
putenv("GOOGLE_CLIENT_ID=".$env['GOOGLE_CLIENT_ID']);
putenv("GOOGLE_CLIENT_SECRET=".$env['GOOGLE_CLIENT_SECRET']);

$client->setClientId(getenv('GOOGLE_CLIENT_ID')); 
$client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
$client->setRedirectUri('http://localhost/campus_management/api/callback.php');
 
/*  ADD THIS HERE */
$client->setScopes([
    "email",
    "profile"
]);


?>


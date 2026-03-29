<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing environment...<br>";

if (file_exists('vendor/autoload.php')) {
    echo "1. Vendor folder found. Loading...<br>";
    require_once 'vendor/autoload.php';
    echo "2. Autoload loaded successfully.<br>";
} else {
    echo "ERROR: 'vendor/autoload.php' not found! You must run 'composer install' in the api/ folder.<br>";
    exit();
}

if (class_exists('Google_Client')) {
    echo "3. Google_Client class exists.<br>";
} else {
    echo "ERROR: Google_Client class not found. The library is not installed correctly.<br>";
    exit();
}

echo "Environment test passed! Your config.php is the likely culprit.";
?>
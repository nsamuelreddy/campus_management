<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    // Proceed with processing form data...
}
?>
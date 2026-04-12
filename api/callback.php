<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once 'config.php';
include "../db.php";

/* =========================
   CHECK GOOGLE CODE
========================= */
if (!isset($_GET['code'])) {
    die("No code received from Google. Check Redirect URI in Google Console.");
}

/* =========================
   GET ACCESS TOKEN
========================= */
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die("Google Error: " . $token['error']);
}

$client->setAccessToken($token['access_token']);

/* =========================
   GET USER INFO FROM GOOGLE
========================= */
$google_oauth = new Google_Service_Oauth2($client);
$google_account_info = $google_oauth->userinfo->get();

$userEmail = $google_account_info->email;
$userName  = $google_account_info->name;

/* =========================
   CHECK USER IN DATABASE
========================= */
$stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // USER EXISTS
    $user = $result->fetch_assoc();

} else {

    // NEW USER → AUTO REGISTER
    $password = 'google_auth';
    $role = "Student";

    $stmt = $conn->prepare("INSERT INTO Users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $userName, $userEmail, $password, $role);

    if (!$stmt->execute()) {
        die("Insert failed (callback.php): " . $stmt->error);
    }

    $user_id = $stmt->insert_id;

    $user = [
        "user_id" => $user_id,
        "full_name" => $userName,
        "email" => $userEmail,
        "role" => $role
    ];
}

/* =========================
   SET SESSION
========================= */
$_SESSION['user'] = $user;
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];

/* =========================
   REDIRECT BASED ON ROLE
========================= */
$roleCheck = strtolower($user['role']);

if ($roleCheck === 'admin') {
    header("Location: ../admin-dashboard.php");
} elseif ($roleCheck === 'faculty') {
    header("Location: ../faculty-dashboard.php");
} else {
    header("Location: ../dashboard.php");
}

exit();
?>
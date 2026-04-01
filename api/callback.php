
<?php
session_start();
require_once 'config.php';
include "../db.php";

if (isset($_GET['code'])) {

    // Get token from Google
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        die("Error: " . $token['error']);
    }

    $client->setAccessToken($token['access_token']);

    // Get user info from Google
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $userEmail = $google_account_info->email;
    $userName  = $google_account_info->name;

    //  Check if user exists in DB
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        //  Existing user → login
        $user = $result->fetch_assoc();
    } else {
        //  New Google user → insert into DB
        $stmt = $conn->prepare("INSERT INTO Users (full_name, email, password_hash, role) VALUES (?, ?, 'google_auth', 'Student')");
        $stmt->bind_param("ss", $userName, $userEmail);
        $stmt->execute();

        $user_id = $stmt->insert_id;

        $user = [
            "user_id" => $user_id,
            "full_name" => $userName,
            "email" => $userEmail,
            "role" => "Student"
        ];
    }

    //  Set session (VERY IMPORTANT)
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    //  Redirect to dashboard
    header("Location: ../dashboard.php");
    exit();

} else {
    // If no code → go back to login
    header("Location: ../index.php");
    exit();
}
?>
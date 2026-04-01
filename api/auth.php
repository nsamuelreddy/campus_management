<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'login') {

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = ucfirst($data['role'] ?? 'Student'); // match DB (Admin, Faculty, Student)

    if (!$email || !$password) {
        echo json_encode(["success" => false, "message" => "Email & Password required"]);
        exit;
    }

    // Check user
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }

    $user = $result->fetch_assoc();

    // Check password
    if ($password !== $user['password_hash']) {
        echo json_encode(["success" => false, "message" => "Wrong password"]);
        exit;
    }

    // Check role match
    if ($user['role'] !== $role) {
        echo json_encode(["success" => false, "message" => "Role mismatch"]);
        exit;
    }

    // Set session
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    echo json_encode([
        "success" => true,
        "user" => $user
    ]);
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
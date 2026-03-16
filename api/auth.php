<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'login') {
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? 'student';

    $_SESSION['user'] = [
        'email' => $email,
        'role' => $role
    ];

    echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
    exit;
}
if ($action === 'logout') {
    unset($_SESSION['user']);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action not found']);
?>
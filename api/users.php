<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();

// 2. GET Request: Send all users to the frontend
if ($method === 'GET') {
    $stmt = $pdo->query('SELECT user_id AS id, full_name AS name, email, role FROM Users ORDER BY user_id ASC');
    $rows = $stmt->fetchAll();
    $users = array_map(function ($row) {
        return [
            'id' => (int)$row['id'],
            'name' => (string)$row['name'],
            'email' => (string)$row['email'],
            'role' => (string)$row['role'],
            'status' => 'Active',
        ];
    }, $rows);

    jsonResponse(['success' => true, 'users' => $users]);
}

// 3. POST Request: Update an existing user
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'update') {
        $idToUpdate = (int)($data['id'] ?? 0);
        if ($idToUpdate <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid user id']);
        }

        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $roleRaw = trim((string)($data['role'] ?? 'Student'));

        if ($name === '' || $email === '') {
            jsonResponse(['success' => false, 'message' => 'Name and email are required']);
        }

        $roleDb = in_array($roleRaw, ['Admin', 'Faculty', 'Student'], true) ? $roleRaw : 'Student';

        try {
            $stmt = $pdo->prepare('UPDATE Users SET full_name = ?, email = ?, role = ? WHERE user_id = ?');
            $stmt->execute([$name, $email, $roleDb, $idToUpdate]);
        } catch (Throwable $e) {
            jsonResponse(['success' => false, 'message' => 'Unable to update user. Email may already exist.']);
        }

        jsonResponse(['success' => true, 'message' => 'User updated successfully!']);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
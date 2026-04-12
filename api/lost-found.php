<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = db();
$sessionUserId = isset($_SESSION['user']) ? userIdFromSession($pdo) : null;

$method = $_SERVER['REQUEST_METHOD'];

// 2. GET Request: Send all items to the frontend
if ($method === 'GET') {
    $sql = "
        SELECT
            item_id AS id,
            item_type AS status,
            item_name AS title,
            COALESCE(description, '') AS description,
            CONCAT('📍 ', COALESCE(location, 'Campus')) AS location,
            DATE_FORMAT(created_at, '%b %e') AS date
        FROM LostFound
        ORDER BY created_at DESC
    ";
    $items = $pdo->query($sql)->fetchAll();
    jsonResponse(['success' => true, 'items' => $items]);
}

// 3. POST Request: Save a newly reported item
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Capitalize the first letter of status just in case they type "lost" instead of "Lost"
    $status = ucfirst(strtolower($data['status'] ?? 'Lost')); 

    $title = trim((string)($data['title'] ?? 'Unknown Item'));
    $description = trim((string)($data['description'] ?? ''));
    $location = trim((string)($data['location'] ?? 'Campus'));

    $stmt = $pdo->prepare('INSERT INTO LostFound (item_type, item_name, description, location, reporter_id) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$status, $title, $description, $location, $sessionUserId]);

    jsonResponse(['success' => true, 'message' => 'Item reported successfully!']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
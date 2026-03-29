<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];


// =========================
// GET ITEMS FROM DATABASE
// =========================
if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM LostFound ORDER BY created_at DESC");

    $items = [];

    while ($row = $result->fetch_assoc()) {

        $items[] = [
            "id" => $row['item_id'],
            "status" => $row['item_type'],
            "title" => $row['item_name'],
            "description" => $row['description'],
            "location" => "📍 " . $row['location'],
            "date" => date("M j", strtotime($row['created_at']))
        ];
    }

    echo json_encode([
        "success" => true,
        "items" => $items
    ]);

    exit;
}


// =========================
// SAVE NEW ITEM
// =========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    $status = ucfirst(strtolower($data['status'] ?? 'Lost'));
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $location = $data['location'] ?? '';

    // Dummy reporter ID
    $reporter_id = 2;

    $stmt = $conn->prepare(
        "INSERT INTO LostFound (item_type, item_name, description, location, reporter_id)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param("ssssi", $status, $title, $description, $location, $reporter_id);

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Item reported successfully!"
    ]);

    exit;
}
?>
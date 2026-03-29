<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];


// =========================
// GET NOTICES FROM DATABASE
// =========================
if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");

    $notices = [];

    while ($row = $result->fetch_assoc()) {

        $notices[] = [
            "id" => $row['notice_id'],
            "title" => $row['title'],
            "category" => "academic", // keeping same structure as your JS
            "content" => $row['content'],
            "date" => date("M j", strtotime($row['created_at'])),
            "urgent" => false
        ];
    }

    echo json_encode([
        "success" => true,
        "notices" => $notices
    ]);

    exit;
}


// =========================
// POST (CREATE / DELETE)
// =========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    // -------- DELETE --------
    if (isset($data['action']) && $data['action'] === 'delete') {

        $id = $data['id'];

        $stmt = $conn->prepare("DELETE FROM notices WHERE notice_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Notice deleted successfully!"
        ]);
        exit;
    }

    // -------- CREATE --------
    else {

        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $author_id = 1; // admin

        $stmt = $conn->prepare(
            "INSERT INTO notices (title, content, author_id) VALUES (?, ?, ?)"
        );

        $stmt->bind_param("ssi", $title, $content, $author_id);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Notice created successfully!"
        ]);
        exit;
    }
}
?>
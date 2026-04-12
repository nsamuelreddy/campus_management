<?php
session_start();
header('Content-Type: application/json');
include "../db.php";


// AUTH CHECK


if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user']['role'];
$userId = $_SESSION['user']['user_id'];
$method = $_SERVER['REQUEST_METHOD'];



// GET NOTICES (ALL USERS)

if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");

    $notices = [];

    while ($row = $result->fetch_assoc()) {
        $notices[] = [
            "id" => $row['notice_id'],
            "title" => $row['title'],
            "category" => $row['category'],
            "content" => $row['content'],
            "date" => date("M j", strtotime($row['created_at'])),
            "urgent" => ($row['category'] === 'urgent')
        ];
    }

    echo json_encode([
        "success" => true,
        "notices" => $notices
    ]);
    exit;
}


// POST (CREATE / DELETE)

// POST (CREATE / DELETE)
if ($method === 'POST') {

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    // 🔥 DEBUG (important)
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'No data received',
            'raw' => $raw
        ]);
        exit;
    }

    // Students cannot manage notices
    if ($userRole === 'student') {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied'
        ]);
        exit;
    }

    // DELETE
    if (isset($data['action']) && $data['action'] === 'delete') {

        $id = $data['id'];

        $stmt = $conn->prepare("DELETE FROM notices WHERE notice_id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

        exit;
    }

    // CREATE
    $title = trim($data['title'] ?? '');
    $category = $data['category'] ?? 'general';
    $content = trim($data['content'] ?? '');

    if ($title === '' || $content === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Title and content required'
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO notices (title, category, content, author_id)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("sssi", $title, $category, $content, $userId);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Notice created'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $conn->error
        ]);
    }

    exit;
}

?>

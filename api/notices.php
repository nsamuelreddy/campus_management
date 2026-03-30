<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

// ==========================
// AUTH CHECK
// ==========================
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user_role'];
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];


// ==========================
// GET NOTICES (ALL USERS)
// ==========================
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


// ==========================
// POST (CREATE / DELETE)
// ==========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    // ❌ Students cannot manage notices
    if ($userRole === 'student') {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied'
        ]);
        exit;
    }

    // ======================
    // DELETE NOTICE
    // ======================
    if (isset($data['action']) && $data['action'] === 'delete') {

        $id = $data['id'];

        $sql = "DELETE FROM notices WHERE notice_id='$id'";

        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Delete failed'
            ]);
        }

        exit;
    }


    // ======================
    // CREATE NOTICE
    // ======================
    else {

        $title = $data['title'] ?? '';
        $category = $data['category'] ?? 'general';
        $content = $data['content'] ?? '';

        
        $sql = "INSERT INTO notices (title, category, content, author_id)
        VALUES ('$title', '$category', '$content', '$userId')";

        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Notice created'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Insert failed'
            ]);
        }

        exit;
    }
}
?>
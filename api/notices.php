<?php
session_start();
header('Content-Type: application/json');

// SECURITY CHECK: Ensure user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user']['role']; // 'student', 'faculty', or 'admin'
$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION['notices'])) {
    $_SESSION['notices'] = [];
}

// GET Request: Everyone (Students, Faculty, Admin) is allowed to READ notices
if ($method === 'GET') {
    echo json_encode(['success' => true, 'notices' => $_SESSION['notices']]);
    exit;
}

// POST Request: Create or Delete notices
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // RBAC CHECK: Block Students from creating or deleting notices
    if ($userRole === 'student') {
        echo json_encode(['success' => false, 'message' => 'Access Denied: Students cannot manage notices.']);
        exit;
    }

    // --- DELETE ACTION (Faculty/Admin Only) ---
    if (isset($data['action']) && $data['action'] === 'delete') {
        $idToDelete = $data['id'];
        foreach ($_SESSION['notices'] as $key => $notice) {
            if ($notice['id'] == $idToDelete) {
                unset($_SESSION['notices'][$key]);
                $_SESSION['notices'] = array_values($_SESSION['notices']); // Re-index array
                break;
            }
        }
        echo json_encode(['success' => true, 'message' => 'Notice deleted successfully!']);
        exit;
    } 
    
    // --- CREATE ACTION (Faculty/Admin Only) ---
    else {
        $newNotice = [
            'id' => time(),
            'title' => $data['title'] ?? '',
            'category' => $data['category'] ?? 'general',
            'content' => $data['content'] ?? '',
            'date' => date('M j'),
            'urgent' => ($data['category'] === 'urgent')
        ];
        array_unshift($_SESSION['notices'], $newNotice);
        echo json_encode(['success' => true, 'message' => 'Notice created!']);
        exit;
    }
}
?>
<?php
session_start();
header('Content-Type: application/json');

// SECURITY CHECK: Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit;
}

$userRole = $_SESSION['user']['role']; // 'student', 'faculty', or 'admin'
$method = $_SERVER['REQUEST_METHOD'];

// Ensure complaints array exists
if (!isset($_SESSION['complaints'])) {
    $_SESSION['complaints'] = [];
}

// GET Request: Send complaints
if ($method === 'GET') {
    // If Student: Only send them THEIR complaints (For now, we send all as mock, but this is where the logic goes)
    // If Faculty/Admin: Send them ALL complaints
    echo json_encode(['success' => true, 'complaints' => $_SESSION['complaints'], 'role' => $userRole]);
    exit;
}

// POST Request
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // ACTION: Update Status (ONLY Faculty or Admin can do this)
    if (isset($data['action']) && $data['action'] === 'update_status') {
        
        // RBAC CHECK
        if ($userRole === 'student') {
            echo json_encode(['success' => false, 'message' => 'Access Denied: Students cannot update status.']);
            exit;
        }

        foreach ($_SESSION['complaints'] as &$complaint) {
            if ($complaint['id'] == $data['id']) {
                $complaint['status'] = $data['status']; 
                break;
            }
        }
        echo json_encode(['success' => true, 'message' => 'Status successfully updated!']);
        exit;
    } 
    
    // ACTION: Create Complaint (Students can do this)
    else {
        $newComplaint = [
            'id' => time(),
            'type' => $data['type'] ?? 'other',
            'subject' => $data['subject'] ?? 'No Subject',
            'description' => $data['description'] ?? '',
            'status' => 'Pending',
            'date' => date('M j, Y')
        ];
        array_unshift($_SESSION['complaints'], $newComplaint);
        echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully!']);
        exit;
    }
}
?>
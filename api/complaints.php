<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();


// HEADERS

header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");


// DB

include "../db.php";


// AUTH CHECK


if (!isset($_SESSION['user'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$userId = $_SESSION['user']['user_id'];
$userRole = strtolower(trim($_SESSION['user']['role']));
$method = $_SERVER['REQUEST_METHOD'];



// GET COMPLAINTS

if ($method === 'GET') {

    if ($userRole === 'student') {
        $stmt = $conn->prepare("SELECT * FROM Complaints WHERE user_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
    } else {
        $stmt = $conn->prepare("SELECT * FROM Complaints ORDER BY created_at DESC");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $complaints = [];

    while ($row = $result->fetch_assoc()) {

        //  FIX mapping INSIDE loop
         $row['id'] = $row['complaint_id'];
         $row['date'] = $row['created_at'];

         $complaints[] = $row;
    }

    echo json_encode([
        'success' => true,
        'complaints' => $complaints,
        'role' => $userRole
    ]);
    exit;
}



// POST REQUEST

if ($method === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid data'
        ]);
        exit;
    }

    
    // UPDATE STATUS
    
    if (isset($data['action']) && $data['action'] === 'update_status') {

        if ($userRole === 'student') {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]);
            exit;
        }

        $id = $data['id'] ?? '';
        $status = $data['status'] ?? '';

        if (!$id || !$status) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input'
            ]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE Complaints SET status=? WHERE complaint_id=?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Update failed'
            ]);
        }

        exit;
    }

    
    // CREATE COMPLAINT

    if ($userRole !== 'student') {
        echo json_encode([
            'success' => false,
            'message' => 'Only students can create complaints'
        ]);
        exit;
    }

    $type = $data['type'] ?? 'general';
    $subject = trim($data['subject'] ?? '');
    $description = trim($data['description'] ?? '');

    if ($subject === '' || $description === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Subject and description are required'
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO Complaints (user_id, type, subject, description, status) 
        VALUES (?, ?, ?, ?, 'Pending')
    ");

    $stmt->bind_param("isss", $userId, $type, $subject, $description);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Complaint submitted successfully'
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
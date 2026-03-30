<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

// ==========================
// AUTH CHECK
// ==========================
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$method = $_SERVER['REQUEST_METHOD'];


// ==========================
// GET COMPLAINTS
// ==========================
if ($method === 'GET') {

    // STUDENT → only their complaints
    if ($userRole === 'student') {
        $sql = "SELECT * FROM Complaints WHERE user_id='$userId' ORDER BY created_at DESC";
    } 
    // FACULTY / ADMIN → all complaints
    else {
        $sql = "SELECT * FROM Complaints ORDER BY created_at DESC";
    }

    $result = mysqli_query($conn, $sql);

    $complaints = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $complaints[] = $row;
    }

    echo json_encode([
        'success' => true,
        'complaints' => $complaints,
        'role' => $userRole
    ]);
    exit;
}


// ==========================
// POST REQUEST
// ==========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    // ======================
    // UPDATE STATUS (ONLY FACULTY/ADMIN)
    // ======================
    if (isset($data['action']) && $data['action'] === 'update_status') {

        if ($userRole === 'student') {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied'
            ]);
            exit;
        }

        $id = $data['id'];
        $status = $data['status'];

        $sql = "UPDATE Complaints SET status='$status' WHERE complaint_id='$id'";

        if (mysqli_query($conn, $sql)) {
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

    // ======================
    // CREATE COMPLAINT (ONLY STUDENT)
    // ======================
    else {

        if ($userRole !== 'student') {
            echo json_encode([
                'success' => false,
                'message' => 'Only students can create complaints'
            ]);
            exit;
        }

        $type = $data['type'] ?? 'other';
        $subject = $data['subject'] ?? '';
        $description = $data['description'] ?? '';

        $sql = "INSERT INTO Complaints (user_id, type, subject, description, status)
                VALUES ('$userId', '$type', '$subject', '$description', 'Pending')";

        if (mysqli_query($conn, $sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Complaint submitted successfully'
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
<?php
session_start();
header('Content-Type: application/json'); 
include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];


// ==========================
// GET ALL COMPLAINTS (FROM DB)
// ==========================
if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM Complaints ORDER BY created_at DESC");

    $complaints = [];

    while ($row = $result->fetch_assoc()) {

        $complaints[] = [
            "id" => $row['complaint_id'],
            "type" => "general", // keeping same format for JS
            "subject" => $row['subject'],
            "description" => $row['description'],
            "status" => $row['status'],
            "date" => date("M j, Y", strtotime($row['created_at']))
        ];
    }

    echo json_encode([
        "success" => true,
        "complaints" => $complaints
    ]);

    exit;
}


// ==========================
// POST (CREATE / UPDATE STATUS)
// ==========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    // ======================
    // UPDATE STATUS (FACULTY)
    // ======================
    if (isset($data['action']) && $data['action'] === 'update_status') {

        $id = $data['id'];
        $status = $data['status'];

        $stmt = $conn->prepare("UPDATE Complaints SET status=? WHERE complaint_id=?");
        $stmt->bind_param("si", $status, $id);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Status updated to " . $status
        ]);
        exit;
    }

    // ======================
    // CREATE NEW COMPLAINT
    // ======================
    else {

        $type = $data['type'] ?? 'other';
        $subject = $data['subject'] ?? '';
        $description = $data['description'] ?? '';
        $user_id = 1; // default user

        $stmt = $conn->prepare(
            "INSERT INTO Complaints (user_id, subject, description, status) VALUES (?, ?, ?, 'Pending')"
        );

        $stmt->bind_param("iss", $user_id, $subject, $description);
        $stmt->execute();

        echo json_encode([
            "success" => true,
            "message" => "Complaint submitted successfully!"
        ]);
        exit;
    }
}
?>
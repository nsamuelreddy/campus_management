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

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];


// ==========================
// STUDENT VIEW
// ==========================
if ($userRole === 'student') {

    // only this student's complaints
    $result = $conn->query("SELECT COUNT(*) as total,
        SUM(status='Resolved') as resolved
        FROM Complaints WHERE user_id='$userId'");

    $row = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'stats' => [
            'myComplaints' => (int)$row['total'],
            'myResolved' => (int)$row['resolved']
        ]
    ]);
    exit;
}


// ==========================
// ADMIN / FACULTY VIEW
// ==========================

// Total Complaints
$total = $conn->query("SELECT COUNT(*) as total FROM Complaints")->fetch_assoc()['total'];

// Pending
$pending = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Pending'")->fetch_assoc()['total'];

// Resolved
$resolved = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Resolved'")->fetch_assoc()['total'];

// Users
$users = $conn->query("SELECT COUNT(*) as total FROM Users")->fetch_assoc()['total'];


// ==========================
// RESPONSE
// ==========================
echo json_encode([
    'success' => true,
    'stats' => [
        'totalComplaints' => (int)$total,
        'pendingIssues' => (int)$pending,
        'resolvedIssues' => (int)$resolved,
        'users' => (int)$users
    ]
]);
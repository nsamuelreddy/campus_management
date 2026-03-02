<?php
session_start();
header('Content-Type: application/json');

// 1. Set up some dummy complaints if the session is empty
if (!isset($_SESSION['complaints'])) {
    $_SESSION['complaints'] = [
        [
            'id' => 1,
            'type' => 'wifi',
            'subject' => 'Internet not working in Block A',
            'description' => 'No connectivity since morning',
            'status' => 'Pending',
            'date' => 'Mar 8, 2026'
        ],
        [
            'id' => 2,
            'type' => 'hostel',
            'subject' => 'AC not working in Room 204',
            'description' => 'AC making noise and not cooling',
            'status' => 'In Progress',
            'date' => 'Mar 6, 2026'
        ]
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// 2. GET Request: Send all complaints to the frontend
if ($method === 'GET') {
    echo json_encode(['success' => true, 'complaints' => $_SESSION['complaints']]);
    exit;
}

// 3. POST Request: Save a new complaint
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $newComplaint = [
        'id' => time(),
        'type' => $data['type'] ?? 'other',
        'subject' => $data['subject'] ?? 'No Subject',
        'description' => $data['description'] ?? '',
        'status' => 'Pending',
        'date' => date('M j, Y') // e.g., Mar 10, 2026
    ];

    // Add the new complaint to the top of the list
    array_unshift($_SESSION['complaints'], $newComplaint);

    echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully!']);
    exit;
}
?>
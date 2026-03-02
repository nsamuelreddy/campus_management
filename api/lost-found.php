<?php
session_start();
header('Content-Type: application/json');

// 1. Set up some dummy items if the session is empty
if (!isset($_SESSION['lost_found'])) {
    $_SESSION['lost_found'] = [
        [
            'id' => 1, 
            'status' => 'Found', 
            'title' => 'Student ID Card - Rahul S.', 
            'description' => 'Found near canteen 3', 
            'location' => '📍 Canteen', 
            'date' => 'Mar 7'
        ],
        [
            'id' => 2, 
            'status' => 'Lost', 
            'title' => 'Calculator (Casio FX-991)', 
            'description' => 'Scientific calculator, has name written on back', 
            'location' => '📍 Exam Hall 2', 
            'date' => 'Mar 5'
        ]
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// 2. GET Request: Send all items to the frontend
if ($method === 'GET') {
    echo json_encode(['success' => true, 'items' => $_SESSION['lost_found']]);
    exit;
}

// 3. POST Request: Save a newly reported item
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Capitalize the first letter of status just in case they type "lost" instead of "Lost"
    $status = ucfirst(strtolower($data['status'] ?? 'Lost')); 

    $newItem = [
        'id' => time(),
        'status' => $status,
        'title' => $data['title'] ?? 'Unknown Item',
        'description' => $data['description'] ?? '',
        'location' => '📍 ' . ($data['location'] ?? 'Campus'),
        'date' => date('Mar j')
    ];

    // Add the new item to the top of the list
    array_unshift($_SESSION['lost_found'], $newItem);

    echo json_encode(['success' => true, 'message' => 'Item reported successfully!']);
    exit;
}
?>
<?php
session_start();
header('Content-Type: application/json');

// 1. Set up some dummy users if the session is empty
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        ['id' => 1, 'name' => 'Arjun Sharma', 'email' => 'arjun@campus.edu', 'role' => 'Student', 'status' => 'Active'],
        ['id' => 2, 'name' => 'Dr. Priya Mehta', 'email' => 'priya@campus.edu', 'role' => 'Faculty', 'status' => 'Active'],
        ['id' => 3, 'name' => 'Sneha R.', 'email' => 'sneha@campus.edu', 'role' => 'Student', 'status' => 'Active'],
        ['id' => 4, 'name' => 'Rohit K.', 'email' => 'rohit@campus.edu', 'role' => 'Student', 'status' => 'Inactive'],
        ['id' => 5, 'name' => 'Rajesh Kumar', 'email' => 'admin@campus.edu', 'role' => 'Admin', 'status' => 'Active']
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// 2. GET Request: Send all users to the frontend
if ($method === 'GET') {
    echo json_encode(['success' => true, 'users' => $_SESSION['users']]);
    exit;
}

// 3. POST Request: Update an existing user
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'update') {
        $idToUpdate = (int)$data['id'];
        
        // Find the user and update their details
        foreach ($_SESSION['users'] as $key => $user) {
            if ($user['id'] === $idToUpdate) {
                $_SESSION['users'][$key]['name'] = $data['name'];
                $_SESSION['users'][$key]['email'] = $data['email'];
                $_SESSION['users'][$key]['role'] = $data['role'];
                $_SESSION['users'][$key]['status'] = $data['status'];
                break;
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'User updated successfully!']);
        exit;
    }
}
?>
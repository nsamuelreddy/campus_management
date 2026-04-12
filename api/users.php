<?php
session_start();
header('Content-Type: application/json');
include "../db.php";


$method = $_SERVER['REQUEST_METHOD'];


// GET USERS


// GET USERS FROM DATABASE

if ($method === 'GET') {

    $result = $conn->query("SELECT user_id, full_name, email, role FROM Users");

    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['user_id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'status' => 'Active' // you don’t have status column yet
        ];
    }

    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    exit;
}


// UPDATE USER

if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'update') {

        $idToUpdate = (int)$data['id'];

        foreach ($_SESSION['users'] as $key => $user) {
            if ($user['id'] === $idToUpdate) {

                $_SESSION['users'][$key]['name'] = $data['name'];
                $_SESSION['users'][$key]['email'] = $data['email'];
                $_SESSION['users'][$key]['role'] = $data['role'];
                $_SESSION['users'][$key]['status'] = $data['status'];

                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully!'
                ]);
                exit;
            }
        }

        // If user not found
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
}




// FALLBACK RESPONSE (IMPORTANT FIX)

echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
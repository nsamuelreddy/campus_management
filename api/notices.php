<?php
session_start();
header('Content-Type: application/json');

// Create some default notices if the session is empty
if (!isset($_SESSION['notices'])) {
    $_SESSION['notices'] = [
        [
            'id' => 1,
            'title' => 'Mid-semester exams scheduled',
            'category' => 'academic',
            'content' => 'Please check the portal for your seat allocations.',
            'date' => 'Mar 8',
            'urgent' => true
        ]
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// GET request: Send all notices back to the frontend
if ($method === 'GET') {
    echo json_encode(['success' => true, 'notices' => $_SESSION['notices']]);
    exit;
}

// POST request: Handle both Creating and Deleting notices
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // --- DELETE ACTION ---
    if (isset($data['action']) && $data['action'] === 'delete') {
        $idToDelete = $data['id']; // Get the ID from JavaScript
        
        // Loop through the notices to find the one to delete
        foreach ($_SESSION['notices'] as $key => $notice) {
            if ($notice['id'] == $idToDelete) {
                unset($_SESSION['notices'][$key]); // Remove it
                
                // Re-organize the array list so it stays neat
                $_SESSION['notices'] = array_values($_SESSION['notices']);
                break;
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Notice deleted successfully!']);
        exit;
    } 
    
    // --- CREATE ACTION ---
    else {
        $newNotice = [
            'id' => time(),
            'title' => $data['title'] ?? '',
            'category' => $data['category'] ?? 'general',
            'content' => $data['content'] ?? '',
            'date' => date('M j'),
            'urgent' => ($data['category'] === 'urgent')
        ];

        // Add the new notice to the beginning of the array
        array_unshift($_SESSION['notices'], $newNotice);

        echo json_encode(['success' => true, 'message' => 'Notice created!']);
        exit;
    }
}
?>
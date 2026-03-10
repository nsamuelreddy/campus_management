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

// 3. POST Request: Handle both Creating and Updating complaints
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // FACULTY ACTION: Check if Faculty is clicking "In Progress" or "Resolve"
    if (isset($data['action']) && $data['action'] === 'update_status') {
        $found = false;
        foreach ($_SESSION['complaints'] as &$complaint) {
            // Find the exact complaint by ID and update its status
            if ($complaint['id'] == $data['id']) {
                $complaint['status'] = $data['status']; 
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo json_encode(['success' => true, 'message' => 'Status successfully updated to ' . $data['status']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Complaint not found']);
        }
        exit;
    } 
    
    // STUDENT ACTION: Otherwise, a Student is submitting a brand new complaint
    else {
        $newComplaint = [
            'id' => time(),
            'type' => $data['type'] ?? 'other',
            'subject' => $data['subject'] ?? 'No Subject',
            'description' => $data['description'] ?? '',
            'status' => 'Pending',
            'date' => date('M j, Y')
        ];

        // Add the new complaint to the top of the list
        array_unshift($_SESSION['complaints'], $newComplaint);

        echo json_encode(['success' => true, 'message' => 'Complaint submitted successfully!']);
        exit;
    }
}
?>
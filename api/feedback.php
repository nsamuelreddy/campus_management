<?php
session_start();
header('Content-Type: application/json');

// Initialize the feedback array in the session if it doesn't exist
if (!isset($_SESSION['feedback'])) {
    $_SESSION['feedback'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

// GET Request: (Optional) If you later want to display all feedback to an Admin/Faculty
if ($method === 'GET') {
    echo json_encode(['success' => true, 'feedback' => $_SESSION['feedback']]);
    exit;
}

// POST Request: Save new feedback submitted by a student
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $newFeedback = [
        'id' => time(),
        'semester' => $data['semester'] ?? '',
        'department' => $data['department'] ?? '',
        'faculty' => $data['faculty'] ?? '',
        'subject' => $data['subject'] ?? '',
        'ratings' => $data['ratings'] ?? [],
        'date' => date('Y-m-d H:i:s')
    ];

    // Add the new feedback to our session storage
    array_unshift($_SESSION['feedback'], $newFeedback);

    echo json_encode(['success' => true, 'message' => 'Thank you! Your feedback has been submitted successfully.']);
    exit;
}
?>
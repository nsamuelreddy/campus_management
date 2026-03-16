<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['feedback'])) {
    $_SESSION['feedback'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(['success' => true, 'feedback' => $_SESSION['feedback']]);
    exit;
}


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


    array_unshift($_SESSION['feedback'], $newFeedback);

    echo json_encode(['success' => true, 'message' => 'Thank you! Your feedback has been submitted successfully.']);
    exit;
}
?>
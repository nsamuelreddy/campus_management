<?php
session_start();
header('Content-Type: application/json');

// 1. Initialize default settings if they don't exist in the session yet
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'institutionName' => 'SmartCampus University',
        'adminEmail' => 'admin@smartcampus.edu',
        'emailNotifications' => true,
        'smsAlerts' => false,
        'weeklyReports' => true
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

// 2. GET Request: Send settings to the frontend when the page loads
if ($method === 'GET') {
    echo json_encode(['success' => true, 'settings' => $_SESSION['settings']]);
    exit;
}

// 3. POST Request: Save new settings from the form
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Update the session array with the new data
    $_SESSION['settings'] = [
        'institutionName' => $data['institutionName'] ?? '',
        'adminEmail' => $data['adminEmail'] ?? '',
        'emailNotifications' => $data['emailNotifications'] ?? false,
        'smsAlerts' => $data['smsAlerts'] ?? false,
        'weeklyReports' => $data['weeklyReports'] ?? false
    ];

    echo json_encode(['success' => true, 'message' => 'Settings saved successfully!']);
    exit;
}
?>
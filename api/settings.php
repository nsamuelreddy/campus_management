<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];


// ==========================
// GET SETTINGS FROM DATABASE
// ==========================
if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM settings WHERE id = 1");
    $row = $result->fetch_assoc();

    $settings = [
        "institutionName" => $row['institutionName'],
        "adminEmail" => $row['adminEmail'],
        "emailNotifications" => (bool)$row['emailNotifications'],
        "smsAlerts" => (bool)$row['smsAlerts'],
        "weeklyReports" => (bool)$row['weeklyReports']
    ];

    echo json_encode([
        "success" => true,
        "settings" => $settings
    ]);

    exit;
}


// ==========================
// SAVE SETTINGS TO DATABASE
// ==========================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    $institutionName = $data['institutionName'] ?? '';
    $adminEmail = $data['adminEmail'] ?? '';
    $emailNotifications = $data['emailNotifications'] ? 1 : 0;
    $smsAlerts = $data['smsAlerts'] ? 1 : 0;
    $weeklyReports = $data['weeklyReports'] ? 1 : 0;

    $stmt = $conn->prepare("
        UPDATE settings 
        SET institutionName=?, adminEmail=?, emailNotifications=?, smsAlerts=?, weeklyReports=? 
        WHERE id=1
    ");

    $stmt->bind_param(
        "ssiii",
        $institutionName,
        $adminEmail,
        $emailNotifications,
        $smsAlerts,
        $weeklyReports
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Settings saved successfully!"
    ]);

    exit;
}
?>
<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();

function fetchSettings(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT institutionName, adminEmail, emailNotifications, smsAlerts, weeklyReports FROM settings WHERE id = 1 LIMIT 1');
    $row = $stmt->fetch();

    if (!$row) {
        $default = [
            'institutionName' => 'SmartCampus University',
            'adminEmail' => 'admin@smartcampus.edu',
            'emailNotifications' => true,
            'smsAlerts' => false,
            'weeklyReports' => true,
        ];

        $insert = $pdo->prepare('INSERT INTO settings (id, institutionName, adminEmail, emailNotifications, smsAlerts, weeklyReports) VALUES (1, ?, ?, ?, ?, ?)');
        $insert->execute([
            $default['institutionName'],
            $default['adminEmail'],
            $default['emailNotifications'] ? 1 : 0,
            $default['smsAlerts'] ? 1 : 0,
            $default['weeklyReports'] ? 1 : 0,
        ]);

        return $default;
    }

    return [
        'institutionName' => (string)$row['institutionName'],
        'adminEmail' => (string)$row['adminEmail'],
        'emailNotifications' => (bool)$row['emailNotifications'],
        'smsAlerts' => (bool)$row['smsAlerts'],
        'weeklyReports' => (bool)$row['weeklyReports'],
    ];
}

// 2. GET Request: Send settings to the frontend when the page loads
if ($method === 'GET') {
    jsonResponse(['success' => true, 'settings' => fetchSettings($pdo)]);
}

// 3. POST Request: Save new settings from the form
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $institutionName = trim((string)($data['institutionName'] ?? 'SmartCampus University'));
    $adminEmail = trim((string)($data['adminEmail'] ?? 'admin@smartcampus.edu'));
    $emailNotifications = !empty($data['emailNotifications']) ? 1 : 0;
    $smsAlerts = !empty($data['smsAlerts']) ? 1 : 0;
    $weeklyReports = !empty($data['weeklyReports']) ? 1 : 0;

    $stmt = $pdo->prepare(
        'INSERT INTO settings (id, institutionName, adminEmail, emailNotifications, smsAlerts, weeklyReports)
         VALUES (1, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE institutionName = VALUES(institutionName), adminEmail = VALUES(adminEmail),
                                 emailNotifications = VALUES(emailNotifications), smsAlerts = VALUES(smsAlerts),
                                 weeklyReports = VALUES(weeklyReports)'
    );
    $stmt->execute([$institutionName, $adminEmail, $emailNotifications, $smsAlerts, $weeklyReports]);

    jsonResponse(['success' => true, 'message' => 'Settings saved successfully!']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = db();

$sql = "
    SELECT
        DATE_FORMAT(created_at, '%b') AS month_name,
        DATE_FORMAT(created_at, '%Y-%m') AS month_key,
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved
    FROM Complaints
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month_key, month_name
    ORDER BY month_key ASC
";

$rows = $pdo->query($sql)->fetchAll();
$monthlyData = [];

foreach ($rows as $row) {
    $monthlyData[] = [
        'month' => (string)$row['month_name'],
        'total' => (int)$row['total'],
        'resolved' => (int)$row['resolved'],
    ];
}

if (empty($monthlyData)) {
    $monthlyData = [
        ['month' => 'Jan', 'total' => 0, 'resolved' => 0],
        ['month' => 'Feb', 'total' => 0, 'resolved' => 0],
        ['month' => 'Mar', 'total' => 0, 'resolved' => 0],
        ['month' => 'Apr', 'total' => 0, 'resolved' => 0],
    ];
}

jsonResponse([
    'success' => true,
    'chartData' => $monthlyData
]);
?>
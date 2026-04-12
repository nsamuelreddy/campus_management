<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

// Fixed role check
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role']) !== 'admin') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}


// 1. Complaints Chart

$chartData = [];

$query = "
SELECT 
    DATE_FORMAT(created_at, '%b') AS month,
    MONTH(created_at) as month_num,
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved
FROM Complaints
GROUP BY month, month_num
ORDER BY month_num
";

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $chartData[] = [
        "month" => $row['month'],
        "total" => (int)$row['total'],
        "resolved" => (int)$row['resolved']
    ];
}


// 2. Feedback Chart

$feedbackData = [];

$query2 = "
SELECT 
    DATE_FORMAT(submitted_at, '%b') AS month,
    MONTH(submitted_at) as month_num,
    ROUND(AVG(rating), 2) AS avg_rating
FROM Feedback
GROUP BY month, month_num
ORDER BY month_num
";

$result2 = $conn->query($query2);

while ($row = $result2->fetch_assoc()) {
    $feedbackData[] = [
        "month" => $row['month'],
        "rating" => (float)$row['avg_rating']
    ];
}


// 3. Stats

$stats = [];

$stats['users'] = $conn->query("SELECT COUNT(*) as total FROM Users")->fetch_assoc()['total'];
$stats['complaints'] = $conn->query("SELECT COUNT(*) as total FROM Complaints")->fetch_assoc()['total'];
$stats['resolved'] = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Resolved'")->fetch_assoc()['total'];
$stats['pending'] = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Pending'")->fetch_assoc()['total'];

echo json_encode([
    "success" => true,
    "chartData" => $chartData,
    "feedbackData" => $feedbackData,
    "stats" => $stats
]);
?>
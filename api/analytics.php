
<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

//  Optional: Admin Access Control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

// =====================================
// 1. Complaints Monthly Data (Bar Chart)
// =====================================
$chartData = [];

$complaintsQuery = "
SELECT 
    DATE_FORMAT(created_at, '%b') AS month,
    MONTH(created_at) as month_num,
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolved
FROM Complaints
GROUP BY month, month_num
ORDER BY month_num
";

$result = $conn->query($complaintsQuery);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $chartData[] = [
            "month" => $row['month'],
            "total" => (int)$row['total'],
            "resolved" => (int)$row['resolved']
        ];
    }
}

// =====================================
// 2. Feedback Monthly Avg (Line Chart)
// =====================================
$feedbackData = [];

$feedbackQuery = "
SELECT 
    DATE_FORMAT(submitted_at, '%b') AS month,
    MONTH(submitted_at) as month_num,
    ROUND(AVG(rating), 2) AS avg_rating
FROM Feedback
GROUP BY month, month_num
ORDER BY month_num
";

$result2 = $conn->query($feedbackQuery);

if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        $feedbackData[] = [
            "month" => $row['month'],
            "rating" => (float)$row['avg_rating']
        ];
    }
}

// =====================================
// 3. Dashboard Stats
// =====================================
$stats = [];

// Total Users
$res = $conn->query("SELECT COUNT(*) as total FROM Users");
$stats['users'] = (int)$res->fetch_assoc()['total'];

// Total Complaints
$res = $conn->query("SELECT COUNT(*) as total FROM Complaints");
$stats['complaints'] = (int)$res->fetch_assoc()['total'];

// Resolved Complaints
$res = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Resolved'");
$stats['resolved'] = (int)$res->fetch_assoc()['total'];

// Pending Complaints
$res = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Pending'");
$stats['pending'] = (int)$res->fetch_assoc()['total'];

// =====================================
// Final JSON Response
// =====================================
echo json_encode([
    "success" => true,
    "chartData" => $chartData,
    "feedbackData" => $feedbackData,
    "stats" => $stats
]);
?>

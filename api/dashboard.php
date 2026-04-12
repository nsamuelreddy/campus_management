
<?php

header("Content-Type: application/json");
session_start();

include "../db.php";


// AUTH CHECK

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['user_id'];
$userRole = strtolower($_SESSION['user']['role']);


// STUDENT VIEW 

if ($userRole === 'student') {

    // stats
    $result = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN LOWER(status)='resolved' THEN 1 ELSE 0 END) as resolved
        FROM Complaints 
        WHERE user_id='$userId'
    ");

    $row = $result->fetch_assoc();

    // RECENT NOTICES (FIXED)
    $noticesResult = $conn->query("
        SELECT title, created_at 
        FROM notices 
        ORDER BY created_at DESC 
        LIMIT 5
    ");

    $notices = $noticesResult ? $noticesResult->fetch_all(MYSQLI_ASSOC) : [];

    // RECENT COMPLAINTS (FIXED COLUMN NAME)
    $recentResult = $conn->query("
        SELECT subject, status, created_at 
        FROM Complaints 
        WHERE user_id='$userId'
        ORDER BY created_at DESC 
        LIMIT 5
    ");

    $recent = $recentResult ? $recentResult->fetch_all(MYSQLI_ASSOC) : [];

    echo json_encode([
        'success' => true,
        'stats' => [
            'totalComplaints' => (int)$row['total'],
            'resolved' => (int)$row['resolved'],
            'totalNotices' => count($notices)
        ],
        'notices' => $notices,
        'recentComplaints' => $recent
    ]);

    exit;
}


// ADMIN / FACULTY VIEW


$total = $conn->query("SELECT COUNT(*) as total FROM Complaints")
    ->fetch_assoc()['total'];

$pending = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE LOWER(status)='pending'")
    ->fetch_assoc()['total'];

$resolved = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE LOWER(status)='resolved'")
    ->fetch_assoc()['total'];

$users = $conn->query("SELECT COUNT(*) as total FROM Users")
    ->fetch_assoc()['total'];

// FIX: correct table name
$noticesCount = $conn->query("SELECT COUNT(*) as total FROM notices")
    ->fetch_assoc()['total'];

// =========================
// CHART DATA (ADD HERE)
// =========================
$complaintChart = [];

$months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

foreach ($months as $m) {
    $complaintChart[$m] = [
        "month" => $m,
        "count" => 0
    ];
}

$res = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b') as month, COUNT(*) as total
    FROM Complaints
    GROUP BY month
");

while ($row = $res->fetch_assoc()) {
    $m = $row['month'];
    if (isset($complaintChart[$m])) {
        $complaintChart[$m]['count'] = (int)$row['total'];
    }
}

$complaintChart = array_values($complaintChart);
$excellent = $conn->query("SELECT COUNT(*) as c FROM Feedback WHERE rating >= 4.5")->fetch_assoc()['c'];
$good = $conn->query("SELECT COUNT(*) as c FROM Feedback WHERE rating >= 3.5 AND rating < 4.5")->fetch_assoc()['c'];
$average = $conn->query("SELECT COUNT(*) as c FROM Feedback WHERE rating >= 2.5 AND rating < 3.5")->fetch_assoc()['c'];
$poor = $conn->query("SELECT COUNT(*) as c FROM Feedback WHERE rating < 2.5")->fetch_assoc()['c'];


echo json_encode([
    'success' => true,

    'stats' => [
        'totalComplaints' => (int)$total,
        'pending' => (int)$pending,
        'resolved' => (int)$resolved,
        'activeUsers' => (int)$users,
        'totalNotices' => (int)$noticesCount
    ],

    //  ADD THIS BELOW stats
    'complaintChart' => $complaintChart,

    'feedbackPie' => [
        'excellent' => (int)$excellent,
        'good' => (int)$good,
        'average' => (int)$average,
        'poor' => (int)$poor
    ]
]);



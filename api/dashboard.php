<?php
session_start();
header('Content-Type: application/json');


// Check login
//if (!isset($_SESSION['user_email'])) {
 //   http_response_code(403);
 //   echo json_encode(['success' => false, 'message' => 'Unauthorized']);
 //   exit();
//}

include "../db.php";
include "auth_check.php";

// -------------------
// DATABASE QUERIES
// -------------------

// Total Complaints
$totalResult = $conn->query("SELECT COUNT(*) as total FROM Complaints");
$totalComplaints = $totalResult->fetch_assoc()['total'];

// Pending
$pendingResult = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Pending'");
$pendingIssues = $pendingResult->fetch_assoc()['total'];

// Resolved
$resolvedResult = $conn->query("SELECT COUNT(*) as total FROM Complaints WHERE status='Resolved'");
$resolvedIssues = $resolvedResult->fetch_assoc()['total'];

// Users
$userResult = $conn->query("SELECT COUNT(*) as total FROM Users");
$activeUsers = $userResult->fetch_assoc()['total'];

// -------------------
// FEEDBACK
// -------------------

$excellent = 0; $good = 0; $average = 0; $poor = 0;
$totalRatings = 0;

$result = $conn->query("SELECT rating FROM Feedback");

while ($row = $result->fetch_assoc()) {
    $r = (int)$row['rating'];

    if ($r == 5) $excellent++;
    elseif ($r == 4) $good++;
    elseif ($r == 3) $average++;
    else $poor++;

    $totalRatings++;
}

if ($totalRatings > 0) {
    $excellent = round(($excellent/$totalRatings)*100);
    $good = round(($good/$totalRatings)*100);
    $average = round(($average/$totalRatings)*100);
    $poor = 100 - ($excellent + $good + $average);
}

// -------------------
// TREND
// -------------------
$trendData = [40, 65, 45, 80, 55, 70];

// -------------------
// RESPONSE
// -------------------

echo json_encode([
    'success' => true,
    'stats' => [
        'totalComplaints' => $totalComplaints,
        'pendingIssues' => $pendingIssues,
        'resolvedIssues' => $resolvedIssues,
        'users' => $activeUsers
    ],
    'charts' => [
        'trends' => $trendData,
        'feedback' => [
            'excellent' => $excellent,
            'good' => $good,
            'average' => $average,
            'poor' => $poor
        ]
    ]
]);
?>
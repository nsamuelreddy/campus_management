<?php
session_start();
header('Content-Type: application/json');

$totalComplaints = 0;
$pendingIssues = 0;
$resolvedIssues = 0;

// 1. Calculate Complaint Statistics
if (isset($_SESSION['complaints'])) {
    $totalComplaints = count($_SESSION['complaints']);
    foreach ($_SESSION['complaints'] as $complaint) {
        if ($complaint['status'] === 'Resolved') {
            $resolvedIssues++;
        } else {
            $pendingIssues++;
        }
    }
}

// 2. Calculate Total Users
$activeUsers = isset($_SESSION['users']) ? count($_SESSION['users']) : 0;

// 3. Calculate Trend Chart Data (6 bars)
// We will simulate past months, but make the current month (last bar) react to real complaints!
$currentMonthTrend = min(100, 30 + ($totalComplaints * 10)); // Caps at 100%
$trendData = [40, 65, 45, 80, 55, $currentMonthTrend]; 

// 4. Calculate Feedback Donut Chart Data
$excellent = 45; $good = 30; $average = 15; $poor = 10; // Default percentages if empty

if (isset($_SESSION['feedback']) && count($_SESSION['feedback']) > 0) {
    $totalRatings = 0; $exCount = 0; $gdCount = 0; $avCount = 0; $prCount = 0;
    
    // Read every star rating submitted by students
    foreach ($_SESSION['feedback'] as $fb) {
        if (isset($fb['ratings'])) {
            foreach ($fb['ratings'] as $rating) {
                $val = (int)$rating;
                if ($val === 5) $exCount++;
                elseif ($val === 4) $gdCount++;
                elseif ($val === 3) $avCount++;
                elseif ($val > 0 && $val <= 2) $prCount++;
                if ($val > 0) $totalRatings++;
            }
        }
    }
    
    // Convert to percentages that equal exactly 100
    if ($totalRatings > 0) {
        $excellent = round(($exCount / $totalRatings) * 100);
        $good = round(($gdCount / $totalRatings) * 100);
        $average = round(($avCount / $totalRatings) * 100);
        $poor = 100 - ($excellent + $good + $average); 
    }
}

// Send everything to the frontend
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
exit;
?>
<?php
session_start();
header('Content-Type: application/json');

// SECURITY CHECK: Ensure user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userRole = $_SESSION['user']['role'];

// Base calculation variables
$totalComplaints = 0;
$pendingIssues = 0;
$resolvedIssues = 0;
$activeNotices = isset($_SESSION['notices']) ? count($_SESSION['notices']) : 0;

// Calculate Complaint Statistics
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

// --- RBAC: Serve data based on role ---

// If STUDENT is asking, give them limited, student-focused data
if ($userRole === 'student') {
    echo json_encode([
        'success' => true,
        'stats' => [
            'activeNotices' => $activeNotices,
            // In a real DB, this would only count complaints submitted by THIS specific student
            'myComplaints' => $totalComplaints, 
            'myResolved' => $resolvedIssues
        ]
    ]);
    exit;
}

// If ADMIN or FACULTY is asking, give them the full campus-wide data and charts
if ($userRole === 'admin' || $userRole === 'faculty') {
    
    $activeUsers = isset($_SESSION['users']) ? count($_SESSION['users']) : 0;
    $currentMonthTrend = min(100, 30 + ($totalComplaints * 10)); 
    $trendData = [40, 65, 45, 80, 55, $currentMonthTrend]; 

    $excellent = 45; $good = 30; $average = 15; $poor = 10; 
    // ... (Your existing feedback calculation logic goes here to update those percentages) ...

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
}
?>
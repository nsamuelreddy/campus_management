<?php
session_start();
header('Content-Type: application/json');

// Calculate Complaints Data for the Bar Chart
$resolvedCount = 0;
$pendingCount = 0;

if (isset($_SESSION['complaints'])) {
    foreach ($_SESSION['complaints'] as $complaint) {
        if ($complaint['status'] === 'Resolved') {
            $resolvedCount++;
        } else {
            $pendingCount++;
        }
    }
}

// Generate some mock historical data, but make the current month reflect real data
$monthlyData = [
    ['month' => 'Jan', 'total' => 15, 'resolved' => 12],
    ['month' => 'Feb', 'total' => 22, 'resolved' => 18],
    ['month' => 'Mar', 'total' => 18, 'resolved' => 15],
    ['month' => 'Apr', 'total' => ($resolvedCount + $pendingCount + 5), 'resolved' => $resolvedCount] // Current real data
];

echo json_encode([
    'success' => true,
    'chartData' => $monthlyData
]);
exit;
?>
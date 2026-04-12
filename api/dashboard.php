<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

sessionUserOrFail();

$userRole = normalizeRoleLower($_SESSION['user']['role'] ?? 'student');
$pdo = db();
$sessionUserId = userIdFromSession($pdo);

// Base statistics
$activeNotices = (int)$pdo->query('SELECT COUNT(*) FROM notices')->fetchColumn();
$totalComplaints = (int)$pdo->query('SELECT COUNT(*) FROM Complaints')->fetchColumn();
$pendingIssues = (int)$pdo->query("SELECT COUNT(*) FROM Complaints WHERE status <> 'Resolved'")->fetchColumn();
$resolvedIssues = (int)$pdo->query("SELECT COUNT(*) FROM Complaints WHERE status = 'Resolved'")->fetchColumn();

function feedbackDistribution(PDO $pdo): array
{
    $rows = $pdo->query('SELECT rating FROM Feedback WHERE rating IS NOT NULL')->fetchAll();
    if (!$rows) {
        return ['excellent' => 45, 'good' => 30, 'average' => 15, 'poor' => 10];
    }

    $buckets = ['excellent' => 0, 'good' => 0, 'average' => 0, 'poor' => 0];
    foreach ($rows as $row) {
        $rating = (float)$row['rating'];
        if ($rating >= 4.5) $buckets['excellent']++;
        elseif ($rating >= 3.5) $buckets['good']++;
        elseif ($rating >= 2.5) $buckets['average']++;
        else $buckets['poor']++;
    }

    $total = max(1, count($rows));
    return [
        'excellent' => round(($buckets['excellent'] / $total) * 100, 2),
        'good' => round(($buckets['good'] / $total) * 100, 2),
        'average' => round(($buckets['average'] / $total) * 100, 2),
        'poor' => round(($buckets['poor'] / $total) * 100, 2),
    ];
}

function complaintTrendPercentages(PDO $pdo): array
{
    $sql = "
        SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) cnt
        FROM Complaints
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY ym
        ORDER BY ym ASC
    ";
    $rows = $pdo->query($sql)->fetchAll();
    $counts = array_map(fn($r) => (int)$r['cnt'], $rows);
    if (empty($counts)) {
        return [40, 65, 45, 80, 55, 70];
    }
    $max = max(1, max($counts));
    $out = array_map(fn($c) => max(15, (int)round(($c / $max) * 100)), $counts);
    while (count($out) < 6) {
        array_unshift($out, 20);
    }
    return array_slice($out, -6);
}

// --- RBAC: Serve data based on role ---

// If STUDENT is asking, give them limited, student-focused data
if ($userRole === 'student') {
    $myComplaints = 0;
    $myResolved = 0;
    if ($sessionUserId) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM Complaints WHERE user_id = ?');
        $stmt->execute([(int)$sessionUserId]);
        $myComplaints = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Complaints WHERE user_id = ? AND status = 'Resolved'");
        $stmt->execute([(int)$sessionUserId]);
        $myResolved = (int)$stmt->fetchColumn();
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'activeNotices' => $activeNotices,
            'totalComplaints' => $totalComplaints,
            'pendingIssues' => $pendingIssues,
            'resolvedIssues' => $resolvedIssues,
            'myComplaints' => $myComplaints,
            'myResolved' => $myResolved
        ]
    ]);
    exit;
}

// If ADMIN or FACULTY is asking, give them the full campus-wide data and charts
if ($userRole === 'admin' || $userRole === 'faculty') {
    $activeUsers = (int)$pdo->query('SELECT COUNT(*) FROM Users')->fetchColumn();
    $trendData = complaintTrendPercentages($pdo);
    $feedback = feedbackDistribution($pdo);

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
            'feedback' => $feedback
        ]
    ]);
    exit;
}

jsonResponse(['success' => false, 'message' => 'Unsupported role'], 403);
?>
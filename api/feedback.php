<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// AUTH CHECK 
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['user_id'];
$method = $_SERVER['REQUEST_METHOD'];


// ===============================
// GET → FETCH FROM DATABASE
// ===============================
if ($method === 'GET') {

    $result = $conn->query("
        SELECT rating,
               teaching_clarity,
               subject_knowledge,
               interaction,
               punctuality,
               material_quality
        FROM Feedback
    ");

    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => $conn->error
        ]);
        exit;
    }

    $sum = 0;
    $count = 0;

    $r1 = $r2 = $r3 = $r4 = $r5 = 0;

    // CATEGORY SUMS
    $tc = $sk = $in = $pu = $mq = 0;

    while ($row = $result->fetch_assoc()) {

        $rating = (int)$row['rating'];

        $sum += $rating;
        $count++;

        if ($rating == 1) $r1++;
        if ($rating == 2) $r2++;
        if ($rating == 3) $r3++;
        if ($rating == 4) $r4++;
        if ($rating == 5) $r5++;

        // CATEGORY SUM (ADDED)
        $tc += (int)$row['teaching_clarity'];
        $sk += (int)$row['subject_knowledge'];
        $in += (int)$row['interaction'];
        $pu += (int)$row['punctuality'];
        $mq += (int)$row['material_quality'];
    }

    $pct = fn($v) => $count ? round(($v / $count) * 100) : 0;

    echo json_encode([
        'success' => true,
        'total' => $count,
        'overall' => $count ? round($sum / $count, 1) : 0,

        'rating_breakdown' => [
            1 => $pct($r1),
            2 => $pct($r2),
            3 => $pct($r3),
            4 => $pct($r4),
            5 => $pct($r5)
        ],

        // ===============================
        // CATEGORY RESPONSE (ADDED)
        // ===============================
        'categories' => [
            'teaching_clarity' => $count ? round($tc / $count, 1) : 0,
            'subject_knowledge' => $count ? round($sk / $count, 1) : 0,
            'interaction' => $count ? round($in / $count, 1) : 0,
            'punctuality' => $count ? round($pu / $count, 1) : 0,
            'material_quality' => $count ? round($mq / $count, 1) : 0
        ]
    ]);

    exit;
}


// ===============================
// POST → SAVE TO DATABASE
// ===============================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !is_array($data)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $ratings = $data['ratings'] ?? [];
    

    $sum = 0;
    $count = 0;

    foreach ($ratings as $val) {
        $v = is_numeric($val) ? (int)$val : 0;
        if ($v > 0) {
            $sum += $v;
            $count++;
        }
    }

    $avgRating = $count > 0 ? round($sum / $count, 1) : 0;

    // CATEGORY VALUES (SAFE + STRICT VALIDATION)
    $teaching = isset($data['teaching_clarity']) && is_numeric($data['teaching_clarity'])
        ? (int)$data['teaching_clarity'] : 0;

    $subject = isset($data['subject_knowledge']) && is_numeric($data['subject_knowledge'])
        ? (int)$data['subject_knowledge'] : 0;

    $interaction = isset($data['interaction']) && is_numeric($data['interaction'])
        ? (int)$data['interaction'] : 0;

    $punctuality = isset($data['punctuality']) && is_numeric($data['punctuality'])
        ? (int)$data['punctuality'] : 0;

    $material = isset($data['material_quality']) && is_numeric($data['material_quality'])
        ? (int)$data['material_quality'] : 0;

    $stmt = $conn->prepare("
        INSERT INTO Feedback 
        (user_id, rating,teaching_clarity,subject_knowledge,interaction,punctuality,material_quality) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => $conn->error]);
        exit;
    }

    $stmt->bind_param(
        "idiiiii",
        $userId,
        $avgRating,
        $teaching,
        $subject,
        $interaction,
        $punctuality,
        $material
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Feedback saved'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $stmt->error
        ]);
    }

    exit;
}
?>
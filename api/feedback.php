<?php
session_start();
header('Content-Type: application/json');
include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];


// =======================
// GET → FETCH FROM DATABASE
// =======================
if ($method === 'GET') {

    $result = $conn->query("SELECT * FROM Feedback ORDER BY submitted_at DESC");

    $feedback = [];

    while ($row = $result->fetch_assoc()) {
        $feedback[] = [
            'ratings' => json_decode($row['comments'], true) // storing ratings as JSON in comments
        ];
    }

    echo json_encode(['success' => true, 'feedback' => $feedback]);
    exit;
}


// =======================
// POST → SAVE TO DATABASE
// =======================
if ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    $ratings = $data['ratings'] ?? [];

    // Convert ratings array to JSON string
    $ratingsJson = json_encode($ratings);

    // Dummy user_id (you can replace with session user later)
    $user_id = 2;

    $stmt = $conn->prepare("INSERT INTO Feedback (user_id, rating, comments) VALUES (?, ?, ?)");

    // Calculate average rating for DB column
    $sum = 0;
    $count = 0;

    foreach ($ratings as $val) {
        $v = (int)$val;
        if ($v > 0) {
            $sum += $v;
            $count++;
        }
    }

    $avgRating = $count > 0 ? round($sum / $count) : 0;

    $stmt->bind_param("iis", $user_id, $avgRating, $ratingsJson);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your feedback has been submitted successfully.'
    ]);
    exit;
}
?>
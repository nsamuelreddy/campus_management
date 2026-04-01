<?php
session_start();
header('Content-Type: application/json');

require '../db.php';

if (!isset($_SESSION['user_email'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$email = $_SESSION['user_email'];

/* -------------------------
   MARK AS READ
------------------------- */
if (isset($_POST['action']) && $_POST['action'] === 'mark_read') {

    $id = $_POST['id'];

    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_email = ?");
    $stmt->execute([$id, $email]);

    echo json_encode(["success" => true, "message" => "Marked as read"]);
    exit;
}

/* -------------------------
   DELETE NOTIFICATION
------------------------- */
if (isset($_POST['action']) && $_POST['action'] === 'delete') {

    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_email = ?");
    $stmt->execute([$id, $email]);

    echo json_encode(["success" => true, "message" => "Deleted"]);
    exit;
}

/* -------------------------
   FETCH NOTIFICATIONS
------------------------- */
$stmt = $pdo->prepare("
    SELECT * 
    FROM notifications 
    WHERE user_email = ? 
    ORDER BY created_at DESC
");

$stmt->execute([$email]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $data
]);
?> 
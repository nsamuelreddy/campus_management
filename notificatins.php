<?php
session_start();
require 'db.php'; // Your PDO connection

$email = $_SESSION['user_email'];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_email = ? ORDER BY created_at DESC");
$stmt->execute([$email]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
?>
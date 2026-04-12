<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

sessionUserOrFail();

$role = normalizeRoleLower($_SESSION['user']['role'] ?? 'student');
$email = strtolower(trim((string)($_SESSION['user']['email'] ?? '')));
$method = $_SERVER['REQUEST_METHOD'];

if ($email === '') {
    jsonResponse(['success' => false, 'message' => 'Invalid session user'], 401);
}

$pdo = db();

function decodeNotificationRow(array $row): array
{
    $decoded = json_decode((string)$row['message'], true);
    $title = 'Notification';
    $message = (string)$row['message'];
    $type = 'info';
    $source = 'system';

    if (is_array($decoded)) {
        $title = (string)($decoded['title'] ?? $title);
        $message = (string)($decoded['message'] ?? $message);
        $type = strtolower(trim((string)($decoded['type'] ?? 'info')));
        if (!in_array($type, ['info', 'success', 'warning', 'error'], true)) {
            $type = 'info';
        }
        $source = (string)($decoded['source'] ?? 'system');
    }

    return [
        'id' => (string)$row['id'],
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'source' => $source,
        'read' => (bool)$row['is_read'],
        'createdAt' => date('c', strtotime((string)$row['created_at'])),
    ];
}

function unreadCountForUser(PDO $pdo, string $email): int
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_email = ? AND is_read = 0');
    $stmt->execute([$email]);
    return (int)$stmt->fetchColumn();
}

function ensureSeedNotifications(PDO $pdo, string $email, string $role): void
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_email = ?');
    $stmt->execute([$email]);
    if ((int)$stmt->fetchColumn() > 0) {
        return;
    }

    $noticesCount = (int)$pdo->query('SELECT COUNT(*) FROM notices')->fetchColumn();
    $complaintsCount = (int)$pdo->query('SELECT COUNT(*) FROM Complaints')->fetchColumn();

    if ($role === 'student') {
        addNotificationForEmail($pdo, $email, 'Welcome back', 'Your student portal notifications are now active.', 'success', 'system');
        addNotificationForEmail($pdo, $email, 'Notices updated', 'There are ' . $noticesCount . ' notices available on campus.', 'info', 'notices');
        return;
    }

    if ($role === 'faculty') {
        addNotificationForEmail($pdo, $email, 'Faculty workspace ready', 'Manage notices and complaints from one place.', 'success', 'system');
        addNotificationForEmail($pdo, $email, 'Complaints overview', 'Current total complaints: ' . $complaintsCount . '.', 'warning', 'complaints');
        return;
    }

    addNotificationForEmail($pdo, $email, 'Admin control center', 'System notifications are enabled for your account.', 'success', 'system');
    addNotificationForEmail($pdo, $email, 'Campus snapshot', 'Users can now receive live notice and complaint updates.', 'info', 'dashboard');
}

ensureSeedNotifications($pdo, $email, $role);

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT id, message, is_read, created_at FROM notifications WHERE user_email = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$email]);
    $rows = $stmt->fetchAll();
    $notifications = array_map('decodeNotificationRow', $rows);

    jsonResponse([
        'success' => true,
        'notifications' => $notifications,
        'unread' => unreadCountForUser($pdo, $email)
    ]);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'mark_read') {
        $id = (int)($data['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_email = ?');
            $stmt->execute([$id, $email]);
        }
        jsonResponse(['success' => true, 'unread' => unreadCountForUser($pdo, $email)]);
    }

    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_email = ?');
        $stmt->execute([$email]);
        jsonResponse(['success' => true, 'unread' => 0]);
    }

    jsonResponse(['success' => false, 'message' => 'Invalid action']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

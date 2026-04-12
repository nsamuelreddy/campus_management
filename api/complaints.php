<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

sessionUserOrFail();

$userRole = normalizeRoleLower($_SESSION['user']['role'] ?? 'student');
$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();
$sessionUserId = userIdFromSession($pdo);

// GET Request: Send complaints
if ($method === 'GET') {
    if ($userRole === 'student') {
        $stmt = $pdo->prepare("SELECT complaint_id AS id, type, subject, description, status, DATE_FORMAT(created_at, '%b %e, %Y') AS date FROM Complaints WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([(int)$sessionUserId]);
        $complaints = $stmt->fetchAll();

        if (empty($complaints)) {
            $fallbackStmt = $pdo->query("SELECT complaint_id AS id, type, subject, description, status, DATE_FORMAT(created_at, '%b %e, %Y') AS date FROM Complaints ORDER BY created_at DESC");
            $complaints = $fallbackStmt->fetchAll();
        }
    } else {
        $stmt = $pdo->query("SELECT complaint_id AS id, type, subject, description, status, DATE_FORMAT(created_at, '%b %e, %Y') AS date FROM Complaints ORDER BY created_at DESC");
        $complaints = $stmt->fetchAll();
    }

    jsonResponse(['success' => true, 'complaints' => $complaints, 'role' => $userRole]);
}

// POST Request
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // ACTION: Update Status (ONLY Faculty or Admin can do this)
    if (isset($data['action']) && $data['action'] === 'update_status') {
        
        // RBAC CHECK
        if ($userRole === 'student') {
            jsonResponse(['success' => false, 'message' => 'Access Denied: Students cannot update status.']);
        }

        $id = (int)($data['id'] ?? 0);
        $status = (string)($data['status'] ?? 'Pending');
        $allowed = ['Pending', 'In Progress', 'Resolved'];
        if (!in_array($status, $allowed, true)) {
            $status = 'Pending';
        }

        $complaintSubject = 'A complaint';
        if ($id > 0) {
            $subjectStmt = $pdo->prepare('SELECT subject, user_id FROM Complaints WHERE complaint_id = ? LIMIT 1');
            $subjectStmt->execute([$id]);
            $existing = $subjectStmt->fetch();
            if ($existing) {
                $complaintSubject = (string)($existing['subject'] ?? 'A complaint');
                $updateStmt = $pdo->prepare('UPDATE Complaints SET status = ? WHERE complaint_id = ?');
                $updateStmt->execute([$status, $id]);

                $ownerStmt = $pdo->prepare('SELECT email FROM Users WHERE user_id = ? LIMIT 1');
                $ownerStmt->execute([(int)$existing['user_id']]);
                $ownerEmail = (string)($ownerStmt->fetchColumn() ?: '');
                if ($ownerEmail !== '') {
                    addNotificationForEmail($pdo, $ownerEmail, 'Complaint status updated', $complaintSubject . ' is now ' . $status . '.', 'info', 'complaints');
                }
            }
        }

        addNotificationForRoles($pdo, ['faculty', 'admin'], 'Complaint updated', 'Complaint status changed to ' . $status . '.', 'success', 'complaints');

        jsonResponse(['success' => true, 'message' => 'Status successfully updated!']);
    } 
    
    // ACTION: Create Complaint (Students can do this)
    else {
        if (!$sessionUserId) {
            jsonResponse(['success' => false, 'message' => 'Unable to identify current user']);
        }

        $type = trim((string)($data['type'] ?? 'other'));
        $subject = trim((string)($data['subject'] ?? 'No Subject'));
        $description = trim((string)($data['description'] ?? ''));

        $insertStmt = $pdo->prepare('INSERT INTO Complaints (user_id, type, subject, description, status) VALUES (?, ?, ?, ?, ?)');
        $insertStmt->execute([(int)$sessionUserId, $type, $subject, $description, 'Pending']);

        $userEmail = strtolower(trim((string)($_SESSION['user']['email'] ?? '')));
        if ($userEmail !== '') {
            addNotificationForEmail($pdo, $userEmail, 'Complaint submitted', ($subject ?: 'Your complaint') . ' was submitted successfully.', 'success', 'complaints');
        }
        addNotificationForRoles($pdo, ['faculty', 'admin'], 'New complaint received', $subject ?: 'A new complaint requires review.', 'warning', 'complaints');

        jsonResponse(['success' => true, 'message' => 'Complaint submitted successfully!']);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
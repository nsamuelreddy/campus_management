<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

sessionUserOrFail();

$userRole = normalizeRoleLower($_SESSION['user']['role'] ?? 'student');
$method = $_SERVER['REQUEST_METHOD'];
$pdo = db();

function noticesHasCategoryColumn(PDO $pdo): bool
{
    static $hasCategory = null;

    if ($hasCategory !== null) {
        return $hasCategory;
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM notices LIKE 'category'");
        $hasCategory = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $hasCategory = false;
    }

    return $hasCategory;
}

function ensureNoticesCategoryColumn(PDO $pdo): void
{
    if (noticesHasCategoryColumn($pdo)) {
        return;
    }

    try {
        $pdo->exec("ALTER TABLE notices ADD COLUMN category VARCHAR(50) DEFAULT 'general'");
    } catch (Throwable $e) {
        // Keep API available even if schema change is blocked.
    }
}

ensureNoticesCategoryColumn($pdo);

// GET Request: Everyone (Students, Faculty, Admin) is allowed to READ notices
if ($method === 'GET') {
    if (noticesHasCategoryColumn($pdo)) {
        $sql = "
            SELECT
                notice_id AS id,
                title,
                CASE
                    WHEN LOWER(TRIM(COALESCE(category, ''))) <> '' THEN LOWER(TRIM(category))
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'urgent|immediate|asap|alert|emergency' THEN 'urgent'
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'exam|semester|class|assignment|academic|course' THEN 'academic'
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'event|sports|festival|celebration|workshop|seminar' THEN 'events'
                    ELSE 'general'
                END AS category,
                content,
                DATE_FORMAT(created_at, '%b %e') AS date,
                CASE
                    WHEN LOWER(TRIM(COALESCE(category, ''))) = 'urgent' THEN 1
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'urgent|immediate|asap|alert|emergency' THEN 1
                    ELSE 0
                END AS urgent
            FROM notices
            ORDER BY created_at DESC
        ";
    } else {
        $sql = "
            SELECT
                notice_id AS id,
                title,
                CASE
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'urgent|immediate|asap|alert|emergency' THEN 'urgent'
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'exam|semester|class|assignment|academic|course' THEN 'academic'
                    WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'event|sports|festival|celebration|workshop|seminar' THEN 'events'
                    ELSE 'general'
                END AS category,
                content,
                DATE_FORMAT(created_at, '%b %e') AS date,
                CASE WHEN LOWER(CONCAT(title, ' ', content)) REGEXP 'urgent|immediate|asap|alert|emergency' THEN 1 ELSE 0 END AS urgent
            FROM notices
            ORDER BY created_at DESC
        ";
    }

    $rows = $pdo->query($sql)->fetchAll();
    $notices = array_map(function ($row) {
        $row['urgent'] = (bool)$row['urgent'];
        return $row;
    }, $rows);

    jsonResponse(['success' => true, 'notices' => $notices]);
}

// POST Request: Create or Delete notices
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // RBAC CHECK: Block Students from creating or deleting notices
    if ($userRole === 'student') {
        jsonResponse(['success' => false, 'message' => 'Access Denied: Students cannot manage notices.']);
    }

    // --- DELETE ACTION (Faculty/Admin Only) ---
    if (isset($data['action']) && $data['action'] === 'delete') {
        $idToDelete = (int)($data['id'] ?? 0);
        $deletedTitle = 'A notice';

        if ($idToDelete > 0) {
            $titleStmt = $pdo->prepare('SELECT title FROM notices WHERE notice_id = ?');
            $titleStmt->execute([$idToDelete]);
            $deletedTitle = (string)($titleStmt->fetchColumn() ?: 'A notice');

            $delStmt = $pdo->prepare('DELETE FROM notices WHERE notice_id = ?');
            $delStmt->execute([$idToDelete]);
        }

        addNotificationForRoles($pdo, ['student'], 'Notice removed', $deletedTitle . ' was removed by ' . ucfirst($userRole) . '.', 'warning', 'notices');
        addNotificationForRoles($pdo, ['faculty', 'admin'], 'Notice removed', 'A notice was removed from the board.', 'warning', 'notices');

        jsonResponse(['success' => true, 'message' => 'Notice deleted successfully!']);
    } 
    
    // --- CREATE ACTION (Faculty/Admin Only) ---
    else {
        $title = trim((string)($data['title'] ?? ''));
        $category = strtolower(trim((string)($data['category'] ?? 'general')));
        $content = trim((string)($data['content'] ?? ''));

        if ($title === '' || $content === '') {
            jsonResponse(['success' => false, 'message' => 'Title and content are required']);
        }

        $authorId = userIdFromSession($pdo);
        if (noticesHasCategoryColumn($pdo)) {
            $insertStmt = $pdo->prepare('INSERT INTO notices (title, content, author_id, category) VALUES (?, ?, ?, ?)');
            $insertStmt->execute([$title, $content, $authorId, $category]);
        } else {
            $insertStmt = $pdo->prepare('INSERT INTO notices (title, content, author_id) VALUES (?, ?, ?)');
            $insertStmt->execute([$title, $content, $authorId]);
        }

        $createdTitle = $title;
        $isUrgent = ($category === 'urgent');
        addNotificationForRoles($pdo, ['student'], 'New campus notice', $createdTitle, $isUrgent ? 'warning' : 'info', 'notices');
        addNotificationForRoles($pdo, ['faculty', 'admin'], 'Notice posted', $createdTitle . ' was posted to students.', 'success', 'notices');

        jsonResponse(['success' => true, 'message' => 'Notice created!']);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
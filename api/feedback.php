<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = db();
$sessionUserId = isset($_SESSION['user']) ? userIdFromSession($pdo) : null;
$sessionRole = normalizeRoleLower($_SESSION['user']['role'] ?? 'student');
$sessionName = trim((string)($_SESSION['user']['name'] ?? ''));
$sessionEmail = strtolower(trim((string)($_SESSION['user']['email'] ?? '')));

$method = $_SERVER['REQUEST_METHOD'];

function feedbackRowToClient(array $row): array
{
    $teaching = isset($row['teaching_clarity']) ? (int)$row['teaching_clarity'] : (int)($row['rating'] ?? 0);
    $knowledge = isset($row['subject_knowledge']) ? (int)$row['subject_knowledge'] : (int)($row['rating'] ?? 0);
    $interaction = isset($row['interaction']) ? (int)$row['interaction'] : (int)($row['rating'] ?? 0);
    $punctuality = isset($row['punctuality']) ? (int)$row['punctuality'] : (int)($row['rating'] ?? 0);
    $material = isset($row['material_quality']) ? (int)$row['material_quality'] : (int)($row['rating'] ?? 0);

    return [
        'id' => (int)$row['feedback_id'],
        'semester' => (string)($row['semester_label'] ?? ''),
        'department' => '',
        'faculty' => (string)($row['faculty_name'] ?? ''),
        'facultyEmail' => (string)($row['faculty_email'] ?? ''),
        'subject' => (string)($row['subject_name'] ?? ''),
        'ratings' => [
            'Teaching Clarity:' => (string)$teaching,
            'Subject Knowledge:' => (string)$knowledge,
            'Interaction with Students:' => (string)$interaction,
            'Punctuality:' => (string)$punctuality,
            'Course Material Quality:' => (string)$material,
        ],
        'date' => (string)$row['submitted_at'],
    ];
}

function hasDetailedFeedbackColumns(PDO $pdo): bool
{
    static $hasDetailed = null;

    if ($hasDetailed !== null) {
        return $hasDetailed;
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM Feedback LIKE 'teaching_clarity'");
        $hasDetailed = (bool)$stmt->fetch();
    } catch (Throwable $e) {
        $hasDetailed = false;
    }

    return $hasDetailed;
}

function feedbackHasMetaColumns(PDO $pdo): bool
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM Feedback LIKE 'faculty_name'");
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function feedbackHasFacultyEmailColumn(PDO $pdo): bool
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM Feedback LIKE 'faculty_email'");
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function ensureFeedbackMetaColumns(PDO $pdo): void
{
    if (feedbackHasMetaColumns($pdo) && feedbackHasFacultyEmailColumn($pdo)) {
        return;
    }

    try {
        if (!feedbackHasMetaColumns($pdo)) {
            $pdo->exec("ALTER TABLE Feedback ADD COLUMN faculty_name VARCHAR(150) NULL, ADD COLUMN subject_name VARCHAR(150) NULL, ADD COLUMN semester_label VARCHAR(30) NULL");
        }
        if (!feedbackHasFacultyEmailColumn($pdo)) {
            $pdo->exec("ALTER TABLE Feedback ADD COLUMN faculty_email VARCHAR(150) NULL");
        }
    } catch (Throwable $e) {
        // Keep endpoint available if schema mutation is blocked.
    }
}

ensureFeedbackMetaColumns($pdo);

function pickRatingValue(array $ratings, array $keys): int
{
    foreach ($keys as $key) {
        if (isset($ratings[$key])) {
            $value = (int)$ratings[$key];
            if ($value >= 1 && $value <= 5) {
                return $value;
            }
        }
    }
    return 0;
}

// GET Request: (Optional) If you later want to display all feedback to an Admin/Faculty
if ($method === 'GET') {
    $where = '';
    $params = [];

    if ($sessionRole === 'faculty' && feedbackHasMetaColumns($pdo)) {
        if ($sessionEmail !== '' && feedbackHasFacultyEmailColumn($pdo)) {
            $where = " WHERE (LOWER(TRIM(COALESCE(faculty_email, ''))) = ? OR LOWER(TRIM(COALESCE(faculty_name, ''))) = ?) ";
            $normalizedName = strtolower($sessionName);
            if ($normalizedName === '') {
                $localName = explode('@', $sessionEmail)[0] ?? '';
                $normalizedName = strtolower(str_replace(['.', '_', '-'], ' ', trim($localName)));
            }
            $params[] = strtolower($sessionEmail);
            $params[] = $normalizedName;
        } else {
            $where = " WHERE LOWER(TRIM(COALESCE(faculty_name, ''))) = ? ";
            $params[] = strtolower($sessionName);

            if ($params[0] === '' && $sessionEmail !== '') {
                $localName = explode('@', $sessionEmail)[0] ?? '';
                $params[0] = strtolower(str_replace(['.', '_', '-'], ' ', trim($localName)));
            }
        }
    }

    if (hasDetailedFeedbackColumns($pdo)) {
        $sql = 'SELECT feedback_id, rating, submitted_at, teaching_clarity, subject_knowledge, interaction, punctuality, material_quality';
        if (feedbackHasMetaColumns($pdo)) {
            $sql .= ', faculty_name, subject_name, semester_label';
            if (feedbackHasFacultyEmailColumn($pdo)) {
                $sql .= ', faculty_email';
            }
        }
        $sql .= ' FROM Feedback' . $where . ' ORDER BY submitted_at DESC';
    } else {
        $sql = 'SELECT feedback_id, rating, submitted_at';
        if (feedbackHasMetaColumns($pdo)) {
            $sql .= ', faculty_name, subject_name, semester_label';
            if (feedbackHasFacultyEmailColumn($pdo)) {
                $sql .= ', faculty_email';
            }
        }
        $sql .= ' FROM Feedback' . $where . ' ORDER BY submitted_at DESC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $feedback = array_map('feedbackRowToClient', $rows);
    jsonResponse(['success' => true, 'feedback' => $feedback]);
}

// POST Request: Save new feedback submitted by a student
if ($method === 'POST') {
    if (!$sessionUserId) {
        jsonResponse(['success' => false, 'message' => 'Please login again and submit feedback.'], 401);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $ratings = is_array($data['ratings'] ?? null) ? $data['ratings'] : [];
    $facultyName = trim((string)($data['faculty'] ?? ''));
    $facultyEmail = strtolower(trim((string)($data['facultyEmail'] ?? '')));
    $subjectName = trim((string)($data['subject'] ?? ''));
    $semesterLabel = trim((string)($data['semester'] ?? ''));

    if ($facultyEmail === '' && $facultyName !== '') {
        try {
            $resolveStmt = $pdo->prepare("SELECT email FROM Users WHERE full_name = ? AND role = 'Faculty' LIMIT 1");
            $resolveStmt->execute([$facultyName]);
            $facultyEmail = strtolower(trim((string)($resolveStmt->fetchColumn() ?: '')));
        } catch (Throwable $e) {
            $facultyEmail = '';
        }
    }

    $teaching = pickRatingValue($ratings, ['Teaching Clarity:', 'teaching']);
    $knowledge = pickRatingValue($ratings, ['Subject Knowledge:', 'knowledge']);
    $interaction = pickRatingValue($ratings, ['Interaction with Students:', 'interaction']);
    $punctuality = pickRatingValue($ratings, ['Punctuality:', 'punctuality']);
    $material = pickRatingValue($ratings, ['Course Material Quality:', 'material']);

    $values = array_filter([$teaching, $knowledge, $interaction, $punctuality, $material], fn($v) => $v > 0);
    $overall = !empty($values) ? round(array_sum($values) / count($values), 1) : 0;

    try {
        if (hasDetailedFeedbackColumns($pdo)) {
            if (feedbackHasMetaColumns($pdo)) {
                if (feedbackHasFacultyEmailColumn($pdo)) {
                    $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating, teaching_clarity, subject_knowledge, interaction, punctuality, material_quality, faculty_name, faculty_email, subject_name, semester_label) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $sessionUserId,
                        $overall,
                        $teaching,
                        $knowledge,
                        $interaction,
                        $punctuality,
                        $material,
                        $facultyName,
                        $facultyEmail,
                        $subjectName,
                        $semesterLabel,
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating, teaching_clarity, subject_knowledge, interaction, punctuality, material_quality, faculty_name, subject_name, semester_label) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $sessionUserId,
                        $overall,
                        $teaching,
                        $knowledge,
                        $interaction,
                        $punctuality,
                        $material,
                        $facultyName,
                        $subjectName,
                        $semesterLabel,
                    ]);
                }
            } else {
                $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating, teaching_clarity, subject_knowledge, interaction, punctuality, material_quality) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $sessionUserId,
                    $overall,
                    $teaching,
                    $knowledge,
                    $interaction,
                    $punctuality,
                    $material,
                ]);
            }
        } else {
            $fallbackRating = max(1, min(5, (int)round($overall ?: 3)));
            if (feedbackHasMetaColumns($pdo)) {
                if (feedbackHasFacultyEmailColumn($pdo)) {
                    $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating, faculty_name, faculty_email, subject_name, semester_label) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $sessionUserId,
                        $fallbackRating,
                        $facultyName,
                        $facultyEmail,
                        $subjectName,
                        $semesterLabel,
                    ]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating, faculty_name, subject_name, semester_label) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $sessionUserId,
                        $fallbackRating,
                        $facultyName,
                        $subjectName,
                        $semesterLabel,
                    ]);
                }
            } else {
                $stmt = $pdo->prepare('INSERT INTO Feedback (user_id, rating) VALUES (?, ?)');
                $stmt->execute([
                    $sessionUserId,
                    $fallbackRating,
                ]);
            }
        }
    } catch (Throwable $e) {
        jsonResponse(['success' => false, 'message' => 'Unable to submit feedback right now. Please try again.'], 500);
    }

    jsonResponse(['success' => true, 'message' => 'Thank you! Your feedback has been submitted successfully.']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
?>
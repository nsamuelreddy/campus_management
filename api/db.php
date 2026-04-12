<?php

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'project';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function normalizeRoleLower(string $role): string
{
    $role = strtolower(trim($role));
    if ($role === 'admin' || $role === 'faculty' || $role === 'student') {
        return $role;
    }
    return 'student';
}

function roleToDbEnum(string $role): string
{
    $role = normalizeRoleLower($role);
    if ($role === 'admin') return 'Admin';
    if ($role === 'faculty') return 'Faculty';
    return 'Student';
}

function roleFromDbEnum(string $role): string
{
    return normalizeRoleLower($role);
}

function sessionUserOrFail(): array
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    return $_SESSION['user'];
}

function getUserByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, role FROM Users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function localPartToName(string $email): string
{
    $local = explode('@', $email)[0] ?? 'User';
    $local = str_replace(['.', '_', '-'], ' ', $local);
    $parts = preg_split('/\s+/', trim($local)) ?: ['User'];
    $parts = array_map(fn($p) => ucfirst(strtolower($p)), $parts);
    return trim(implode(' ', $parts));
}

function ensureUser(PDO $pdo, string $email, string $roleLower, string $fullName = ''): array
{
    $email = strtolower(trim($email));
    $roleDb = roleToDbEnum($roleLower);

    $existing = getUserByEmail($pdo, $email);
    if ($existing) {
        $nameToSave = $fullName !== '' ? $fullName : ($existing['full_name'] ?: localPartToName($email));
        $stmt = $pdo->prepare('UPDATE Users SET full_name = ?, role = ? WHERE user_id = ?');
        $stmt->execute([$nameToSave, $roleDb, $existing['user_id']]);

        return [
            'user_id' => (int)$existing['user_id'],
            'full_name' => $nameToSave,
            'email' => $email,
            'role' => roleFromDbEnum($roleDb),
        ];
    }

    $nameToSave = $fullName !== '' ? $fullName : localPartToName($email);
    $passwordHash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO Users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nameToSave, $email, $passwordHash, $roleDb]);

    return [
        'user_id' => (int)$pdo->lastInsertId(),
        'full_name' => $nameToSave,
        'email' => $email,
        'role' => roleFromDbEnum($roleDb),
    ];
}

function userIdFromSession(PDO $pdo): ?int
{
    if (isset($_SESSION['user']['user_id'])) {
        return (int)$_SESSION['user']['user_id'];
    }

    $email = strtolower(trim($_SESSION['user']['email'] ?? ''));
    if ($email === '') return null;

    $user = getUserByEmail($pdo, $email);
    if (!$user) return null;

    $_SESSION['user']['user_id'] = (int)$user['user_id'];
    return (int)$user['user_id'];
}

function addNotificationForEmail(PDO $pdo, string $email, string $title, string $message, string $type = 'info', string $source = 'system'): void
{
    $payload = json_encode([
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'source' => $source,
    ]);

    $stmt = $pdo->prepare('INSERT INTO notifications (user_email, message, is_read) VALUES (?, ?, 0)');
    $stmt->execute([strtolower(trim($email)), $payload]);
}

function addNotificationForRoles(PDO $pdo, array $rolesLower, string $title, string $message, string $type = 'info', string $source = 'system'): void
{
    $rolesDb = array_map('roleToDbEnum', $rolesLower);
    $rolesDb = array_values(array_unique($rolesDb));

    if (empty($rolesDb)) return;

    $placeholders = implode(',', array_fill(0, count($rolesDb), '?'));
    $stmt = $pdo->prepare("SELECT email FROM Users WHERE role IN ({$placeholders})");
    $stmt->execute($rolesDb);
    $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($emails as $email) {
        addNotificationForEmail($pdo, (string)$email, $title, $message, $type, $source);
    }
}

<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

const ALLOWED_ROLES = ['student', 'faculty', 'admin'];
const DEFAULT_GOOGLE_CLIENT_ID = '624797960394-5jua4vjq39v027c129qp9l9ntrvivobi.apps.googleusercontent.com';
const DEMO_LOGIN_CREDENTIALS = [
    'student' => ['email' => 'student@project.com', 'password' => 'student123'],
    'faculty' => ['email' => 'faculty@project.com', 'password' => 'faculty123'],
    'admin' => ['email' => 'admin@campus.edu', 'password' => 'admin123'],
];
const ROLE_EMAIL_ALIASES = [
    'student' => ['student@project.com', 'r220745@rguktrkv.ac.in'],
    'faculty' => ['faculty@project.com', 'nsamuelreddy@gmail.com'],
    'admin' => ['admin@campus.edu', 'yuvikomera@gmail.com'],
];
const GOOGLE_EMAIL_ROLE_MAP = [
    'r220745@rguktrkv.ac.in' => 'student',
    'nsamuelreddy@gmail.com' => 'faculty',
    'yuvikomera@gmail.com' => 'admin'
];

function sendJson($payload)
{
    jsonResponse($payload);
}

function normalizeRole($role)
{
    return normalizeRoleLower((string)$role);
}

function getMappedGoogleRole($email)
{
    $normalizedEmail = strtolower(trim((string)$email));
    return GOOGLE_EMAIL_ROLE_MAP[$normalizedEmail] ?? null;
}

function verifyGoogleIdToken($idToken, $expectedClientId = '')
{
    if (!$idToken) {
        return [false, 'Missing Google token'];
    }

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    $rawResponse = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $rawResponse = curl_exec($ch);
        curl_close($ch);
    }

    if ($rawResponse === false) {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET'
            ]
        ]);
        $rawResponse = @file_get_contents($url, false, $ctx);
    }

    if ($rawResponse === false) {
        return [false, 'Unable to verify Google token'];
    }

    $tokenData = json_decode($rawResponse, true);
    if (!is_array($tokenData) || isset($tokenData['error_description'])) {
        return [false, 'Invalid Google token'];
    }

    $issuer = $tokenData['iss'] ?? '';
    if ($issuer !== 'accounts.google.com' && $issuer !== 'https://accounts.google.com') {
        return [false, 'Invalid Google token issuer'];
    }

    $expiresAt = isset($tokenData['exp']) ? (int)$tokenData['exp'] : 0;
    if ($expiresAt > 0 && $expiresAt < time()) {
        return [false, 'Google token expired'];
    }

    if ($expectedClientId !== '' && ($tokenData['aud'] ?? '') !== $expectedClientId) {
        return [false, 'Google token client mismatch'];
    }

    if (($tokenData['email_verified'] ?? 'false') !== 'true') {
        return [false, 'Google email is not verified'];
    }

    if (empty($tokenData['email'])) {
        return [false, 'Google account email missing'];
    }

    return [true, $tokenData];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'me') {
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            sendJson(['success' => false, 'message' => 'Unauthorized']);
        }

        $sessionUser = $_SESSION['user'];
        $email = strtolower(trim((string)($sessionUser['email'] ?? '')));

        if ($email !== '') {
            try {
                $dbUser = getUserByEmail(db(), $email);
                if ($dbUser) {
                    $_SESSION['user']['user_id'] = (int)$dbUser['user_id'];
                    $_SESSION['user']['name'] = (string)($dbUser['full_name'] ?? $sessionUser['name'] ?? '');
                    $_SESSION['user']['role'] = roleFromDbEnum((string)($dbUser['role'] ?? $sessionUser['role'] ?? 'student'));
                }
            } catch (Throwable $e) {
                // If DB refresh fails, return existing session data to avoid blocking UI.
            }
        }

        sendJson(['success' => true, 'user' => $_SESSION['user']]);
    }

    sendJson(['success' => false, 'message' => 'Action not found']);
}

// Get the data sent from JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'login') {
    $email = strtolower(trim((string)($data['email'] ?? '')));
    $requestedRole = normalizeRole($data['role'] ?? 'student');
    $password = (string)($data['password'] ?? '');

    if ($email === '') {
        sendJson(['success' => false, 'message' => 'Email is required']);
    }

    $expectedCredentials = DEMO_LOGIN_CREDENTIALS[$requestedRole] ?? null;
    if (!$expectedCredentials) {
        sendJson(['success' => false, 'message' => 'Invalid role selected']);
    }

    $allowedEmails = ROLE_EMAIL_ALIASES[$requestedRole] ?? [];
    if (!in_array($email, array_map('strtolower', $allowedEmails), true) || $password !== $expectedCredentials['password']) {
        sendJson(['success' => false, 'message' => 'Invalid email or password for the selected role']);
    }

    try {
        $pdo = db();
        $existing = getUserByEmail($pdo, $email);

        if ($existing) {
            $user = [
                'user_id' => (int)$existing['user_id'],
                'email' => $email,
                'role' => $requestedRole,
                'full_name' => (string)($existing['full_name'] ?? ''),
            ];
            $stmt = $pdo->prepare('UPDATE Users SET role = ? WHERE user_id = ?');
            $stmt->execute([roleToDbEnum($requestedRole), $user['user_id']]);
        } else {
            $user = ensureUser($pdo, $email, $requestedRole);
        }
    } catch (Throwable $e) {
        sendJson(['success' => false, 'message' => 'Database error while logging in']);
    }

    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'name' => $user['full_name'],
        'authProvider' => 'password'
    ];

    sendJson(['success' => true, 'user' => $_SESSION['user']]);
}

if ($action === 'google_login') {
    $idToken = $data['idToken'] ?? '';
    $requestedRole = normalizeRole($data['role'] ?? 'student');
    $expectedClientId = getenv('GOOGLE_CLIENT_ID') ?: DEFAULT_GOOGLE_CLIENT_ID;

    [$valid, $tokenDataOrMessage] = verifyGoogleIdToken($idToken, $expectedClientId);
    if (!$valid) {
        sendJson(['success' => false, 'message' => $tokenDataOrMessage]);
    }

    $tokenData = $tokenDataOrMessage;
    $email = strtolower(trim((string)$tokenData['email']));
    $mappedRole = getMappedGoogleRole($email);
    $finalRole = $mappedRole ?: $requestedRole;

    try {
        $user = ensureUser(
            db(),
            $email,
            $finalRole,
            (string)($tokenData['name'] ?? '')
        );
    } catch (Throwable $e) {
        sendJson(['success' => false, 'message' => 'Database error while logging in with Google']);
    }

    $_SESSION['user'] = [
        'user_id' => $user['user_id'],
        'email' => $email,
        'role' => $user['role'],
        'name' => $user['full_name'],
        'picture' => $tokenData['picture'] ?? '',
        'authProvider' => 'google',
        'googleSub' => $tokenData['sub'] ?? ''
    ];

    sendJson(['success' => true, 'user' => $_SESSION['user']]);
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    sendJson(['success' => true]);
}

sendJson(['success' => false, 'message' => 'Action not found']);
?>
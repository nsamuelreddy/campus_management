

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();


include "../db.php";

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'login') {

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = strtolower($data['role'] ?? 'student');

    if (!$email || !$password) {
        echo json_encode(["success" => false, "message" => "Email & Password required"]);
        exit;
    }

    // CHECK USER
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // ============================
    //  IF USER NOT EXISTS → AUTO REGISTER
    // ============================
    if ($result->num_rows === 0) {

        $full_name = explode('@', $email)[0]; // simple name
        $roleFormatted = ucfirst($role);

        $insert = $conn->prepare("INSERT INTO Users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $full_name, $email, $password, $roleFormatted);

        if (!$insert->execute()) {
            echo json_encode(["success" => false, "message" => "Registration failed"]);
            exit;
        }

        $user_id = $insert->insert_id;

        $user = [
            "user_id" => $user_id,
            "full_name" => $full_name,
            "email" => $email,
            "role" => $roleFormatted
        ];

    } else {

        // ============================
        //  USER EXISTS → LOGIN
        // ============================
        $user = $result->fetch_assoc();

        if ($password !== $user['password_hash']) {
            echo json_encode(["success" => false, "message" => "Wrong password"]);
            exit;
        }
    }

    // ============================
    //  SESSION
    // ============================
    $_SESSION['user'] = [
        "user_id" => $user['user_id'],
        "full_name" => $user['full_name'],
        "email" => $user['email'],
        "role" => $user['role']
    ];

    echo json_encode([
        "success" => true,
        "user" => $_SESSION['user']
    ]);
    exit;
}
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
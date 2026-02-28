<?php
/**
 * api/login.php
 * POST { username, password } → validate against admin_users table
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$pdo  = getDB();
$stmt = $pdo->prepare("SELECT password FROM admin_users WHERE username = ? LIMIT 1");
$stmt->execute([trim($input['username'])]);
$row  = $stmt->fetch();

if (!$row) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

// Support both plain-text (legacy) and bcrypt passwords
$storedPass  = $row['password'];
$inputPass   = $input['password'];
$passwordOk  = ($storedPass === $inputPass) || password_verify($inputPass, $storedPass);

if ($passwordOk) {
    echo json_encode(['success' => true, 'token' => 'php-session-token-' . bin2hex(random_bytes(16))]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}

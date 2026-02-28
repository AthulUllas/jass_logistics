<?php
/**
 * api/admin_settings.php
 * GET  → return current admin username
 * POST { username, password } → update admin credentials
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../database.php';

$pdo = getDB();

// ── GET ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT username FROM admin_users LIMIT 1");
    $row  = $stmt->fetch();
    echo json_encode(['username' => $row['username'] ?? 'admin']);
    exit;
}

// ── POST ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['username']) || empty($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit;
    }

    $username = trim($input['username']);
    $password = $input['password']; // stored plain-text to match legacy behaviour

    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = 1");
    $stmt->execute([$username, $password]);

    echo json_encode(['success' => true, 'message' => 'Admin settings updated successfully']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

<?php
/**
 * JASS Logistics - Admin Settings v2
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CSRF.php';

if (empty($_SESSION['admin_logged_in'])) {
    sendResponse(false, 'Unauthorized', [], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    sendResponse(true, 'Data fetched', ['username' => $_SESSION['admin_username']]);
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::checkOrDie();
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($username)) sendResponse(false, 'Username required', [], 400);

    try {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $hash, $_SESSION['admin_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $_SESSION['admin_id']]);
        }
        
        $_SESSION['admin_username'] = $username;
        sendResponse(true, 'Settings updated successfully');
    } catch (Exception $e) {
        sendResponse(false, 'Database error', [], 500);
    }
}

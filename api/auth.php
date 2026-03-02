<?php
/**
 * JASS Logistics - Auth Module v2
 * Unified endpoint for Login, Logout, and Session Status.
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CSRF.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'status') {
        if (!empty($_SESSION['admin_logged_in'])) {
            sendResponse(true, 'Authorized', [
                'username' => $_SESSION['admin_username'],
                'user_id' => $_SESSION['admin_id']
            ]);
        } else {
            sendResponse(false, 'Unauthorized', [], 401);
        }
    } 
    elseif ($action === 'logout') {
        Logger::log("User logged out: " . ($_SESSION['admin_username'] ?? 'Unknown'), 'INFO');
        
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        sendResponse(true, 'Logged out successfully');
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            sendResponse(false, 'Username and password are required', [], 400);
        }

        $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Setup session
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Update last login
            $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?")->execute([$user['id']]);
            
            Logger::log("Login successful: $username", 'INFO');
            sendResponse(true, 'Login successful', ['username' => $username]);
        } else {
            Logger::log("Failed login attempt: $username", 'WARNING');
            sendResponse(false, 'Invalid credentials', [], 401);
        }
    }
}

sendResponse(false, 'Invalid action or method', [], 404);

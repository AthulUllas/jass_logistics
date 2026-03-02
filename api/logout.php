<?php
/**
 * JASS Logistics - Logout logic
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Logger.php';

// Log out action
if (isset($_SESSION['admin_username'])) {
    Logger::log("User logged out: " . $_SESSION['admin_username'], 'INFO');
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session entirely
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
?>

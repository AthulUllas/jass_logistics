<?php
/**
 * JASS Logistics - Admin Settings (Production)
 * Requires Session Auth & CSRF token to update credentials safely.
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/CSRF.php';

// Authentication Check
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    Logger::log("Unauthorized admin settings access attempt.", 'WARNING');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT username FROM admin_users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode(['username' => htmlspecialchars($user['username'])]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Admin user not found']);
        }
    } catch(PDOException $e) {
        Logger::log("Fetch Admin Error: " . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CSRF Protection
    CSRF::checkOrDie();

    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    $newUsername = trim($input['username'] ?? '');
    $newPassword = trim($input['password'] ?? '');

    if (empty($newUsername) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password cannot be empty.']);
        exit;
    }

    // High cost bcrypt hashing for production
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    try {
        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$newUsername, $hashedPassword, $_SESSION['admin_id']]);

        // Update session tracking
        $_SESSION['admin_username'] = $newUsername;
        
        Logger::log("Admin credentials updated for ID: " . $_SESSION['admin_id'], 'INFO');
        echo json_encode(['success' => true, 'message' => 'Credentials updated successfully.']);

    } catch(PDOException $e) {
        Logger::log("Update Admin Error: " . $e->getMessage(), 'CRITICAL');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
    }
}
?>

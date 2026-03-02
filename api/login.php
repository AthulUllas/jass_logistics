<?php
/**
 * JASS Logistics - Secure Login Endpoint
 * Uses password hashing, PDO prepared statements, and regenerates sessions to prevent fixation.
 */
require_once __DIR__ . '/../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}


$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input || empty($input['username']) || empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

$username = trim($input['username']);
$password = trim($input['password']);

try {
    $stmt = $pdo->prepare("SELECT id, username, password FROM admin_users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        $isValid = false;
        
        // Check hash first
        if (password_verify($password, $user['password'])) {
            $isValid = true;
        } 
        // Fallback for default seed 'admin123' if it wasn't hashed properly during setup
        elseif ($password === $user['password']) {
            $isValid = true;
            
            // Auto-upgrade string to hash for security
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updStmt = $pdo->prepare("UPDATE admin_users SET password = :password WHERE id = :id");
            $updStmt->execute([':password' => $newHash, ':id' => $user['id']]);
        }

        if ($isValid) {
            // Prevent Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Update last login timestamp
            $updLogin = $pdo->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
            $updLogin->execute([':id' => $user['id']]);

            Logger::log("Successful login for user: {$username}", 'INFO');

            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'username' => $username
            ]);
        } else {
            Logger::log("Failed login attempt for user: {$username} (Invalid Password)", 'WARNING');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    } else {
        Logger::log("Failed login attempt (User Not Found): {$username}", 'WARNING');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }

} catch(PDOException $e) {
    Logger::log("Database error during login: " . $e->getMessage(), 'CRITICAL');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error.']);
}
?>

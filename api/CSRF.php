<?php
/**
 * JASS Logistics - CSRF Protection Utility
 * Prevents Cross-Site Request Forgery attacks.
 */

class CSRF {
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function checkOrDie() {
        // Collect token from header or POST body
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : $_SERVER;
        
        // Sometimes headers are capitalized differently
        $token = '';
        if (isset($headers['X-CSRF-Token'])) $token = $headers['X-CSRF-Token'];
        elseif (isset($headers['x-csrf-token'])) $token = $headers['x-csrf-token'];
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        
        if (empty($token) && isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        }

        if (!self::validateToken($token)) {
            http_response_code(403);
            require_once __DIR__ . '/Logger.php';
            Logger::log('CSRF Token Validation Failed.');
            echo json_encode(['success' => false, 'message' => 'CSRF Token Validation Failed']);
            exit;
        }
    }
}
?>

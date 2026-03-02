<?php
/**
 * JASS Logistics - API Bootstrap v2
 * Robust foundation for modular API architecture.
 */

// 1. Environment Loading
function loadEnv($path) {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}
loadEnv(__DIR__ . '/../.env');

// BLOCK LOCALHOST ACCESS
$host = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Access Restricted: This application is not available on localhost.']));
}


// 2. Load Config & Logger
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Logger.php';

// 3. CORS Management (Production Secure)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$isAllowed = in_array($origin, ALLOWED_ORIGINS);

if ($isAllowed) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
    header("Access-Control-Allow-Credentials: true");
}

// Preflight handle
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 4. Global Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Content-Type: application/json; charset=UTF-8");

// 5. Session Management (Cross-Domain Compatible)
// HTTPS is required for SameSite=None
$isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || APP_MODE === 'production';

session_set_cookie_params([
    'lifetime' => 0, // Session cookie
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => $isSecure ? 'None' : 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 6. Global Response Helper
function sendResponse($success, $message, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// 7. Atomic Transactional DB Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    Logger::log("DB Connection Failed: " . $e->getMessage(), 'CRITICAL');
    sendResponse(false, 'Database connection failed.', [], 500);
}

// 8. Custom Error Handling
set_exception_handler(function($e) {
    Logger::log("Exception: " . $e->getMessage(), 'ERROR');
    sendResponse(false, "Internal Server Error during execution.", [], 500);
});

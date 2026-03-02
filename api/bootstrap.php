<?php
/**
 * JASS Logistics - API Bootstrap
 * Handles CORS, Sessions, Error Logging and Environment for all API endpoints.
 */

// 1. Load Environment Variables (Helper for Hostinger/WAMP)
function loadEnv($path) {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}

loadEnv(__DIR__ . '/../.env');

// 2. Global CORS Configuration
$allowedOrigins = explode(',', getenv('ALLOWED_ORIGINS') ?: '*');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
    header("Access-Control-Allow-Credentials: true");
}

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");

// 4. Session Configuration for Cross-Domain support if needed (SameSite=None with Secure)
$domain = getenv('COOKIE_DOMAIN') ?: '';
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $domain,
    'secure' => true, 
    'httponly' => true,
    'samesite' => 'None' // Required for cross-domain cookies (Vercel -> Hostinger)
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Error & Exception Handling
require_once __DIR__ . '/Logger.php';

set_exception_handler(function($e) {
    Logger::log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine(), 'CRITICAL');
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal Server Error']);
    exit();
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    Logger::log("PHP Error ($errno): $errstr in $errfile on line $errline", 'ERROR');
    return true;
});

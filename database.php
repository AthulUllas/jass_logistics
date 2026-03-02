<?php
/**
 * JASS Logistics - Production Database Connection
 * Features: PDO, Exception Handling, Prepared Statement Optimization
 */

require_once __DIR__ . '/api/bootstrap.php';

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'logistics_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    // Advanced PDO configuration for production
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // True prepared statements for security against SQLi
        PDO::ATTR_PERSISTENT         => false                   // Persistent connections can cause issues on shared hosting
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);

} catch(PDOException $e) {
    // Log the error securely without exposing details to the user
    Logger::log("Database Connection Failed: " . $e->getMessage(), 'CRITICAL');
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal Server Error: Database connection failed. Please contact administrator.'
    ]);
    exit();
}
?>

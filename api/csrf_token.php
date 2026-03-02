<?php
/**
 * JASS Logistics - CSRF Token Endpoint
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CSRF.php';

echo json_encode([
    'success' => true,
    'csrf_token' => CSRF::generateToken()
]);
?>

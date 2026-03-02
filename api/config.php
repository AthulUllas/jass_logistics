<?php
/**
 * JASS Logistics - API Config
 * Centralized settings for the application.
 */

// Deployment Mode: 'local' or 'production'
define('APP_MODE', getenv('APP_MODE') ?: 'production');

// Database Settings
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'logistics_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Security
define('AUTH_SESSION_KEY', 'jass_admin_session');
define('CSRF_SESSION_KEY', 'csrf_token');

// Allowed Origins (Explicit list for CORS)
$rawOrigins = getenv('ALLOWED_ORIGINS') ?: '';
define('ALLOWED_ORIGINS', array_filter(array_map('trim', explode(',', $rawOrigins))));

// Directories
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('LOG_DIR', __DIR__ . '/../logs/');

// Limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

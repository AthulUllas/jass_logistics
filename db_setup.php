<?php
// Initialize HTML response for a better user experience
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - JASS Logistics</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; bg-color: #f8fafc; color: #0f172a; padding: 2rem; max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        h1 { color: #f59e0b; margin-top: 0; }
        .success { color: #16a34a; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .step { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .warning-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 1rem; margin-top: 2rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚀 JASS Logistics Database Setup</h1>
';

function outputStep($msg, $isSuccess = true) {
    $class = $isSuccess ? 'success' : 'error';
    $icon = $isSuccess ? '✅' : '❌';
    echo "<div class='step'><span class='$class'>$icon $msg</span></div>";
}

try {
    // 1. Load env and connect (we duplicate logic slightly here so we can catch connection errors elegantly)
    $envPath = __DIR__ . '/.env';
    if (!file_exists($envPath)) {
        throw new Exception("The <b>.env</b> file was not found. Please ensure it exists in the root directory.");
    }
    
    // Quick load env
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
    
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_NAME') ?: 'logistics_db';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    // Attempt connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    outputStep("Connected to MySQL Database: <b>$dbname</b>");

    // 2. Read setup.sql
    $sqlPath = __DIR__ . '/setup.sql';
    if (!file_exists($sqlPath)) {
        throw new Exception("The <b>setup.sql</b> file was not found.");
    }

    $sql = file_get_contents($sqlPath);
    
    // Execute SQL queries
    // We execute it raw. PDO can handle multiple statements if configured, but to be truly portable Hostinger, 
    // we should prepare it or run it directly.
    try {
        $pdo->exec($sql);
        outputStep("<b>setup.sql</b> executed successfully. Tables created and initial safe seeded if empty.");
    } catch (PDOException $e) {
        throw new Exception("Error executing SQL script: " . $e->getMessage());
    }

    echo '<div class="warning-box">
            <h3>⚠️ Critical Security Step</h3>
            <p>The database has been configured. To protect your site from malicious attacks, you <b>MUST DELETE</b> this <code>db_setup.php</code> file from your hosting server immediately.</p>
            <p>Your default admin credentials are:</p>
            <ul>
                <li>Username: <b>admin</b></li>
                <li>Password: <b>password</b></li>
            </ul>
            <p><a href="admin/" style="color: #2563eb; font-weight: bold; text-decoration: none;">&rarr; Go to Admin Panel</a> | <a href="index.html" style="color: #2563eb; font-weight: bold; text-decoration: none;">&rarr; View Homepage</a></p>
          </div>';

} catch (Exception $e) {
    outputStep($e->getMessage(), false);
    echo "<p>Please fix the errors above and refresh this page to try again.</p>";
}

echo '
    </div>
</body>
</html>';
?>

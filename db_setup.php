<?php
/**
 * db_setup.php — One-time database initializer
 * Visit this URL once after uploading to Hostinger: yourdomain.com/db_setup.php
 * DELETE this file afterwards for security!
 */
require_once __DIR__ . '/database.php';

header('Content-Type: text/html; charset=utf-8');

$errors = [];
$messages = [];

try {
    $pdo = getDB();

    // ── Create Tables ─────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `site_content` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `content_key` VARCHAR(100) NOT NULL UNIQUE,
            `content_val` LONGTEXT NOT NULL,
            `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $messages[] = "✅ Table <code>site_content</code> created (or already exists).";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_users` (
            `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username`   VARCHAR(100) NOT NULL UNIQUE,
            `password`   VARCHAR(255) NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $messages[] = "✅ Table <code>admin_users</code> created (or already exists).";

    // ── Seed Admin ─────────────────────────────────────────────
    $pdo->exec("
        INSERT INTO `admin_users` (`username`, `password`) VALUES ('admin', 'admin123')
        ON DUPLICATE KEY UPDATE `username` = `username`;
    ");
    $messages[] = "✅ Admin user seeded (admin / admin123).";

    // ── Load current data.json ─────────────────────────────────
    $dataFile = __DIR__ . '/data.json';
    if (file_exists($dataFile)) {
        $dataJson = file_get_contents($dataFile);
        $data = json_decode($dataJson, true);

        $keys = ['heroSlides', 'services', 'about', 'features', 'stats', 'contact', 'website'];
        $stmt = $pdo->prepare("
            INSERT INTO `site_content` (`content_key`, `content_val`) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE `content_val` = VALUES(`content_val`)
        ");
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $stmt->execute([$key, json_encode($data[$key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
                $messages[] = "✅ Seeded content key: <code>{$key}</code>";
            }
        }
        $messages[] = "✅ All content migrated from <code>data.json</code>.";
    } else {
        // Insert defaults if no data.json
        $defaults = [
            'heroSlides' => [],
            'services'   => [],
            'about'      => ['whoWeAre' => '', 'mission' => '', 'vision' => ''],
            'features'   => [],
            'stats'      => ['countries' => '150+', 'clients' => '12K+', 'delivery' => '99.2%', 'support' => '24/7'],
            'contact'    => ['address' => '', 'phone1' => '', 'phone2' => '', 'email1' => '', 'email2' => '', 'social' => []],
            'website'    => ['logo' => '', 'favicon' => '', 'mapEmbed' => ''],
        ];
        $stmt = $pdo->prepare("
            INSERT INTO `site_content` (`content_key`, `content_val`) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE `content_val` = VALUES(`content_val`)
        ");
        foreach ($defaults as $key => $val) {
            $stmt->execute([$key, json_encode($val)]);
        }
        $messages[] = "⚠️ <code>data.json</code> not found — inserted empty defaults.";
    }

    // ── Admin credentials from admin.json ─────────────────────
    $adminFile = __DIR__ . '/admin.json';
    if (file_exists($adminFile)) {
        $admin = json_decode(file_get_contents($adminFile), true);
        if (!empty($admin['username']) && !empty($admin['password'])) {
            $pdo->prepare("
                INSERT INTO `admin_users` (`username`, `password`) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `username` = ?, `password` = ?
            ")->execute([$admin['username'], $admin['password'], $admin['username'], $admin['password']]);
            $messages[] = "✅ Admin credentials loaded from <code>admin.json</code>.";
        }
    }

} catch (Exception $e) {
    $errors[] = "❌ Error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>DB Setup — JASS Logistics</title>
<style>
  body { font-family: monospace; max-width: 700px; margin: 40px auto; padding: 20px; background: #0f172a; color: #e2e8f0; }
  h1   { color: #f59e0b; }
  li   { margin: 6px 0; font-size: 15px; }
  .err { color: #f87171; }
  .box { background: #1e293b; padding: 20px; border-radius: 8px; margin-top: 20px; }
  code { background: #334155; padding: 2px 6px; border-radius: 4px; }
  .warn { color: #fbbf24; font-weight: bold; margin-top: 20px; display: block; }
</style>
</head>
<body>
<h1>🚀 JASS Logistics — DB Setup</h1>
<div class="box">
<ul>
<?php foreach ($messages as $msg) echo "<li>{$msg}</li>"; ?>
<?php foreach ($errors as $err) echo "<li class='err'>{$err}</li>"; ?>
</ul>
</div>
<?php if (empty($errors)): ?>
<span class="warn">⚠️ IMPORTANT: Delete this file (db_setup.php) immediately after setup for security!</span>
<p>✅ Database is ready. <a href="/" style="color:#f59e0b">← Go to website</a></p>
<?php else: ?>
<p class="err">Setup encountered errors. Check your <code>.env</code> credentials and try again.</p>
<?php endif; ?>
</body>
</html>

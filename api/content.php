<?php
/**
 * api/content.php
 * GET  → returns all site content as JSON
 * POST → updates all site content
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../database.php';

$pdo = getDB();
$keys = ['heroSlides', 'services', 'about', 'features', 'stats', 'contact', 'website'];

// ── GET ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = [];
    $stmt = $pdo->prepare("SELECT content_key, content_val FROM site_content WHERE content_key = ?");
    foreach ($keys as $key) {
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if ($row) {
            $decoded = json_decode($row['content_val'], true);
            $result[$key] = ($decoded !== null) ? $decoded : $row['content_val'];
        }
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── POST ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO site_content (content_key, content_val)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE content_val = VALUES(content_val)
    ");

    foreach ($keys as $key) {
        if (isset($input[$key])) {
            $val = is_string($input[$key]) ? $input[$key] : json_encode($input[$key], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stmt->execute([$key, $val]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Content updated successfully']);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

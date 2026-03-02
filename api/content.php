<?php
/**
 * JASS Logistics - Content API v2
 * Atomically handles site content with strict validation.
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/CSRF.php';

// -------------------------------------------------------------
// GET: Fetch All Web Content
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = [
        'about' => [], 'stats' => [], 'contact' => ['social' => []],
        'website' => [], 'heroSlides' => [], 'services' => [], 'features' => []
    ];

    // Fetch Site Settings (Key-Value)
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch()) {
        $key = $row['setting_key'];
        $val = $row['setting_value'];
        
        if (strpos($key, 'about_') === 0) $data['about'][str_replace('about_', '', $key)] = $val;
        elseif (strpos($key, 'stats_') === 0) $data['stats'][str_replace('stats_', '', $key)] = $val;
        elseif (strpos($key, 'contact_') === 0) $data['contact'][str_replace('contact_', '', $key)] = $val;
        elseif (strpos($key, 'social_') === 0) $data['contact']['social'][str_replace('social_', '', $key)] = $val;
        elseif (strpos($key, 'website_') === 0) $data['website'][str_replace('website_', '', $key)] = $val;
    }

    // Fetch Lists
    $data['heroSlides'] = $pdo->query("SELECT subtitle, title_line1 AS titleLine1, title_highlight AS titleHighlight, description, background_image AS backgroundImage FROM hero_slides ORDER BY display_order ASC")->fetchAll();
    $data['services'] = $pdo->query("SELECT icon, title, description FROM services ORDER BY display_order ASC")->fetchAll();
    $data['features'] = $pdo->query("SELECT icon, title, description FROM features ORDER BY display_order ASC")->fetchAll();

    sendResponse(true, 'Data fetched', $data);
}

// -------------------------------------------------------------
// POST: Update Site Content (Admin Only)
// -------------------------------------------------------------
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['admin_logged_in'])) {
        sendResponse(false, 'Unauthorized', [], 401);
    }

    CSRF::checkOrDie();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) sendResponse(false, 'Invalid JSON payload', [], 400);

    try {
        $pdo->beginTransaction();

        // 1. Process site_settings
        $settings = [];
        if (isset($input['about'])) foreach ($input['about'] as $k => $v) $settings["about_$k"] = $v;
        if (isset($input['stats'])) foreach ($input['stats'] as $k => $v) $settings["stats_$k"] = $v;
        if (isset($input['website'])) foreach ($input['website'] as $k => $v) $settings["website_$k"] = $v;
        if (isset($input['contact'])) {
            foreach ($input['contact'] as $k => $v) {
                if ($k === 'social') {
                    foreach ($v as $sk => $sv) $settings["social_$sk"] = $sv;
                } else {
                    $settings["contact_$k"] = $v;
                }
            }
        }

        if (!empty($settings)) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
            }
        }

        // 2. Relational Lists (Total overwrite for order & deletion support)
        if (isset($input['heroSlides'])) {
            $pdo->exec("DELETE FROM hero_slides");
            $stmt = $pdo->prepare("INSERT INTO hero_slides (subtitle, title_line1, title_highlight, description, background_image, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($input['heroSlides'] as $idx => $s) {
                $stmt->execute([$s['subtitle'] ?? '', $s['titleLine1'] ?? '', $s['titleHighlight'] ?? '', $s['description'] ?? '', $s['backgroundImage'] ?? '', $idx + 1]);
            }
        }

        if (isset($input['services'])) {
            $pdo->exec("DELETE FROM services");
            $stmt = $pdo->prepare("INSERT INTO services (icon, title, description, display_order) VALUES (?, ?, ?, ?)");
            foreach ($input['services'] as $idx => $s) {
                $stmt->execute([$s['icon'] ?? '', $s['title'] ?? '', $s['description'] ?? '', $idx + 1]);
            }
        }

        if (isset($input['features'])) {
            $pdo->exec("DELETE FROM features");
            $stmt = $pdo->prepare("INSERT INTO features (icon, title, description, display_order) VALUES (?, ?, ?, ?)");
            foreach ($input['features'] as $idx => $f) {
                $stmt->execute([$f['icon'] ?? '', $f['title'] ?? '', $f['description'] ?? '', $idx + 1]);
            }
        }

        $pdo->commit();
        Logger::log("Content updated by Admin ID " . $_SESSION['admin_id'], 'INFO');
        sendResponse(true, 'Content saved successfully');

    } catch (Exception $e) {
        $pdo->rollBack();
        Logger::log("Update Transaction Failed: " . $e->getMessage(), 'CRITICAL');
        sendResponse(false, 'Internal server error. Changes rolled back.', [], 500);
    }
}

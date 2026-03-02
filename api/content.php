<?php
/**
 * JASS Logistics - Content API (Production)
 * Handles fetching public content and securely saving admin updates using Transactions.
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/CSRF.php';

// -------------------------------------------------------------
// GET REQUEST: Fetch Public Content
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $data = [
            'about' => [],
            'stats' => [],
            'contact' => ['social' => []],
            'website' => [],
            'heroSlides' => [],
            'services' => [],
            'features' => []
        ];

        // 1. Fetch Key/Value Settings
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

        // 2. Fetch Relational Data
        $data['heroSlides'] = $pdo->query("SELECT subtitle, title_line1 AS titleLine1, title_highlight AS titleHighlight, description, background_image AS backgroundImage FROM hero_slides ORDER BY display_order ASC")->fetchAll();
        $data['services'] = $pdo->query("SELECT icon, title, description FROM services ORDER BY display_order ASC")->fetchAll();
        $data['features'] = $pdo->query("SELECT icon, title, description FROM features ORDER BY display_order ASC")->fetchAll();

        echo json_encode($data);

    } catch(PDOException $e) {
        Logger::log("Read Content Error: " . $e->getMessage(), 'CRITICAL');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Internal server error while fetching content.']);
    }
} 

// -------------------------------------------------------------
// POST REQUEST: Securely Update Content (Admin Only)
// -------------------------------------------------------------
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Authentication Check
    if (empty($_SESSION['admin_logged_in'])) {
        Logger::log("Unauthorized content update attempt.", 'WARNING');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // 2. CSRF Protection Check
    CSRF::checkOrDie();

    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (!$input) {
         http_response_code(400);
         echo json_encode(['success' => false, 'message' => 'Invalid JSON payload format.']);
         exit;
    }

    try {
        // 3. Begin Transaction Data Safety
        $pdo->beginTransaction();

        // Update site_settings
        $settingsToUpdate = [];
        if (isset($input['about'])) foreach ($input['about'] as $k => $v) $settingsToUpdate["about_$k"] = $v;
        if (isset($input['stats'])) foreach ($input['stats'] as $k => $v) $settingsToUpdate["stats_$k"] = $v;
        if (isset($input['website'])) foreach ($input['website'] as $k => $v) $settingsToUpdate["website_$k"] = $v;
        if (isset($input['contact'])) {
            foreach ($input['contact'] as $k => $v) {
                if ($k === 'social') {
                    foreach ($v as $sk => $sv) $settingsToUpdate["social_$sk"] = $sv;
                } else {
                    $settingsToUpdate["contact_$k"] = $v;
                }
            }
        }

        if (!empty($settingsToUpdate)) {
            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($settingsToUpdate as $key => $value) {
                $stmt->execute([$key, $value]);
            }
        }

        // Rebuild Relational Lists (Hero, Services, Features) Safely
        if (isset($input['heroSlides'])) {
            $pdo->query("DELETE FROM hero_slides");
            $stmt = $pdo->prepare("INSERT INTO hero_slides (subtitle, title_line1, title_highlight, description, background_image, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($input['heroSlides'] as $index => $slide) {
                $stmt->execute([
                    $slide['subtitle'] ?? '', $slide['titleLine1'] ?? '', $slide['titleHighlight'] ?? '', 
                    $slide['description'] ?? '', $slide['backgroundImage'] ?? '', $index + 1
                ]);
            }
        }

        if (isset($input['services'])) {
            $pdo->query("DELETE FROM services");
            $stmt = $pdo->prepare("INSERT INTO services (icon, title, description, display_order) VALUES (?, ?, ?, ?)");
            foreach ($input['services'] as $index => $item) {
                $stmt->execute([$item['icon'] ?? '', $item['title'] ?? '', $item['description'] ?? '', $index + 1]);
            }
        }

        if (isset($input['features'])) {
            $pdo->query("DELETE FROM features");
            $stmt = $pdo->prepare("INSERT INTO features (icon, title, description, display_order) VALUES (?, ?, ?, ?)");
            foreach ($input['features'] as $index => $item) {
                $stmt->execute([$item['icon'] ?? '', $item['title'] ?? '', $item['description'] ?? '', $index + 1]);
            }
        }

        // 4. Commit Transaction
        $pdo->commit();
        Logger::log("Content updated successfully by user ID " . $_SESSION['admin_id'], 'INFO');
        
        echo json_encode(['success' => true, 'message' => 'Data saved successfully.']);

    } catch(PDOException $e) {
        $pdo->rollBack();
        Logger::log("Content Update Transaction Failed: " . $e->getMessage(), 'CRITICAL');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Internal server error. Changes rolled back.']);
    }
}
else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>

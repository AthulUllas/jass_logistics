<?php
/**
 * api/upload.php
 * POST (multipart) → upload an image to /uploads/, return JSON with filePath
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file     = $_FILES['image'];
$uploadsDir = realpath(__DIR__ . '/../uploads');

if (!$uploadsDir) {
    // Create uploads dir if it doesn't exist
    mkdir(__DIR__ . '/../uploads', 0755, true);
    $uploadsDir = realpath(__DIR__ . '/../uploads');
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/x-icon'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only images are allowed.']);
    exit;
}

// Max 5MB
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 5MB.']);
    exit;
}

// Generate unique filename
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = time() . '-' . mt_rand(100000000, 999999999) . '.' . $ext;
$destPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
    echo json_encode([
        'success' => true,
        'filePath' => 'uploads/' . $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save uploaded file']);
}

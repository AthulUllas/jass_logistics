<?php
/**
 * JASS Logistics - File Upload API v2
 */
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method Not Allowed', [], 405);
}

if (empty($_SESSION['admin_logged_in'])) {
    sendResponse(false, 'Unauthorized', [], 401);
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, 'No image uploaded or upload error occurred.', [], 400);
}

$file = $_FILES['image'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($extension, $allowed)) {
    sendResponse(false, 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.', [], 400);
}

if ($file['size'] > MAX_UPLOAD_SIZE) {
    sendResponse(false, 'File too large (Max 5MB)', [], 400);
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$filename = bin2hex(random_bytes(16)) . '-' . time() . '.' . $extension;
$destination = UPLOAD_DIR . $filename;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    Logger::log("Uploaded: $filename", 'INFO');
    sendResponse(true, 'File uploaded', ['filePath' => 'uploads/' . $filename]);
} else {
    sendResponse(false, 'Failed to save file.', [], 500);
}

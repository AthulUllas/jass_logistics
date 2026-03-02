<?php
/**
 * JASS Logistics - File Upload API (Production)
 * Secure image upload handling.
 */
require_once __DIR__ . '/../database.php';
// We do not require CSRF token here because the frontend usually sends uploads via FormData 
// without easily appending CSRF payload unless specifically programmed.
// However, we MUST restrict to active Sessions.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Ensure user is logged in
if (empty($_SESSION['admin_logged_in'])) {
    Logger::log("Unauthorized upload attempt blocked.", 'WARNING');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error occurred.']);
    exit;
}

$file = $_FILES['image'];

// Stricter file type validation
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($mimeType, $allowedMimeTypes) || !in_array($extension, $allowedExtensions)) {
    Logger::log("Invalid file upload attempt (MIME: $mimeType, EXT: $extension)", 'WARNING');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.']);
    exit;
}

$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
    exit;
}

// Generate highly unique filename
$filename = bin2hex(random_bytes(16)) . '-' . time() . '.' . $extension;

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        Logger::log("Failed to create upload directory.", 'CRITICAL');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server error creating directory.']);
        exit;
    }
}

$destination = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $destination)) {
    Logger::log("File uploaded successfully: $filename by Admin ID {$_SESSION['admin_id']}", 'INFO');
    $relativePath = 'uploads/' . $filename;
    echo json_encode(['success' => true, 'filePath' => $relativePath]);
} else {
    Logger::log("move_uploaded_file failed for $filename", 'CRITICAL');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file on server.']);
}
?>

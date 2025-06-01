<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

// Check if file was uploaded
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['logo']) ? error_get_last()['message'] : 'No file uploaded';
    die(json_encode(['success' => false, 'error' => $error]));
}

$file = $_FILES['logo'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    die(json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG and GIF allowed. Detected: ' . $mime_type]));
}

// Validate file size (1MB)
if ($file['size'] > 1024 * 1024) {
    die(json_encode(['success' => false, 'error' => 'File too large. Maximum size is 1MB']));
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/logos';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        die(json_encode(['success' => false, 'error' => 'Failed to create upload directory: ' . error_get_last()['message']]));
    }
    chmod($upload_dir, 0777);
}

// Generate unique filename
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = uniqid('logo_') . '.' . $extension;
$filepath = $upload_dir . '/' . $filename;

// Remove old logo if exists
$stmt = $conn->prepare("SELECT logo_path FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if ($row['logo_path'] && file_exists('../' . $row['logo_path'])) {
        @unlink('../' . $row['logo_path']);
    }
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    $error = error_get_last()['message'];
    die(json_encode(['success' => false, 'error' => 'Failed to save file: ' . $error]));
}

// Ensure file permissions
chmod($filepath, 0644);

// Update database
$relative_path = 'uploads/logos/' . $filename;
$stmt = $conn->prepare("UPDATE users SET logo_path = ? WHERE id = ?");
$stmt->bind_param("si", $relative_path, $_SESSION['user_id']);

if ($stmt->execute()) {
    $_SESSION['logo_path'] = $relative_path;
    echo json_encode([
        'success' => true,
        'logo_path' => $relative_path
    ]);
} else {
    // If database update fails, remove the uploaded file
    @unlink($filepath);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update database: ' . $conn->error
    ]);
} 
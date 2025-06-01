<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure we only output JSON
header('Content-Type: application/json');

// Error handling function
function sendError($message, $code = 400) {
    http_response_code($code);
    die(json_encode(['success' => false, 'error' => $message]));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendError('Not authenticated', 401);
}

// Check if file was uploaded
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['logo']) ? 
        getUploadErrorMessage($_FILES['logo']['error']) : 
        'No file uploaded';
    sendError($error);
}

// Helper function to get upload error messages
function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}

try {
    $file = $_FILES['logo'];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        sendError('Invalid file type. Only JPG, PNG and GIF allowed. Detected: ' . $mime_type);
    }

    // Validate file size (1MB)
    if ($file['size'] > 1024 * 1024) {
        sendError('File too large. Maximum size is 1MB');
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/logos';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            sendError('Failed to create upload directory: ' . error_get_last()['message']);
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
        sendError('Failed to save file: ' . error_get_last()['message']);
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
        sendError('Failed to update database: ' . $conn->error);
    }

} catch (Exception $e) {
    sendError('Upload failed: ' . $e->getMessage());
} 
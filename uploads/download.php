<?php
// download.php - Add for path traversal practice
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

$file = $_GET['file'] ?? '';

// VULNERABILITY: Path traversal
// No validation on file path
$base_dir = 'uploads/';
$filepath = $base_dir . $file;

if (file_exists($filepath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
    readfile($filepath);
    
    // FLAG{path_traversal_success}
} else {
    echo "File not found: " . htmlspecialchars($file);
    // FLAG{path_traversal_error}
}
?>
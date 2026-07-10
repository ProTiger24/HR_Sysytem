<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_name = 'signature.png';
        $file_path = $upload_dir . $file_name;
        
        // Delete old signature if exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Upload new signature
        if (move_uploaded_file($_FILES['signature']['tmp_name'], $file_path)) {
            echo json_encode(['success' => true, 'message' => 'Signature uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload signature']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No signature file uploaded']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $upload_dir = '../uploads/';
    $file = $_FILES['profile_picture'];
    
    // Create directory if not exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $filepath = $upload_dir . $filename;
    $db_path = 'uploads/' . $filename;
    
    // Allowed extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, WEBP files are allowed']);
        exit;
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old profile picture if exists
        $stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($old && $old['profile_picture'] && file_exists('../' . $old['profile_picture'])) {
            unlink('../' . $old['profile_picture']);
        }
        
        // Update database
        $stmt = $db->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
        $stmt->bindParam(':profile_picture', $db_path);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Update session
        $_SESSION['profile_picture'] = $db_path;
        
        echo json_encode(['success' => true, 'message' => 'Profile picture updated', 'image_path' => $db_path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>

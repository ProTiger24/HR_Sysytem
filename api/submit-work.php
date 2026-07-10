<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$employee_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit;
    }
    
    $file_path = '';
    $file_name = '';
    $file_size = 0;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/submissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file = $_FILES['file'];
        $file_name = basename($file['name']);
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'txt', 'zip', 'rar'];
        
        if (!in_array($file_ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, TXT, ZIP, RAR']);
            exit;
        }
        
        if ($file_size > 10485760) { // 10MB
            echo json_encode(['success' => false, 'message' => 'File too large (Max 10MB)']);
            exit;
        }
        
        $new_filename = 'submission_' . $employee_id . '_' . time() . '.' . $file_ext;
        $file_path = 'uploads/submissions/' . $new_filename;
        
        if (!move_uploaded_file($file['tmp_name'], '../' . $file_path)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File is required']);
        exit;
    }
    
    try {
        // Insert submission
        $stmt = $db->prepare("INSERT INTO employee_submissions 
                              (employee_id, title, description, file_path, file_name, file_size, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$employee_id, $title, $description, $file_path, $file_name, $file_size]);
        $submission_id = $db->lastInsertId();
        
        // Get employee name
        $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$employee_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        $employee_name = $employee['first_name'] . ' ' . $employee['last_name'];
        
        // Send notification to all HR
        $stmt = $db->prepare("SELECT id FROM users WHERE user_type = 'hr' AND status = 'active'");
        $stmt->execute();
        $hrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $notification_title = "📄 New Work Submission";
        $notification_message = $employee_name . " submitted: " . $title;
        
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, 'info', 0)");
        foreach ($hrs as $hr) {
            $stmt->execute([$hr['id'], $notification_title, $notification_message]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Work submitted successfully! HR has been notified.']);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>

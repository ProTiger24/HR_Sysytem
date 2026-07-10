<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Get all notices with read status
    $stmt = $db->prepare("SELECT n.*, u.first_name, u.last_name,
                           (SELECT COUNT(*) FROM notice_reads WHERE notice_id = n.id AND user_id = ?) as is_read
                           FROM notices n 
                           JOIN users u ON n.created_by = u.id 
                           ORDER BY n.created_at DESC");
    $stmt->execute([$user_id]);
    $notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($user_type == 'employee') {
        $unread = $db->prepare("SELECT COUNT(*) as unread 
                                 FROM notices n 
                                 LEFT JOIN notice_reads nr ON n.id = nr.notice_id AND nr.user_id = ? 
                                 WHERE nr.id IS NULL");
        $unread->execute([$user_id]);
        $count = $unread->fetch(PDO::FETCH_ASSOC)['unread'];
        echo json_encode(['success' => true, 'data' => $notices, 'unread' => (int)$count]);
    } else {
        echo json_encode(['success' => true, 'data' => $notices]);
    }
}
elseif ($method == 'POST') {
    if ($user_type !== 'hr') {
        echo json_encode(['success' => false, 'message' => 'Only HR can post notices']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['title']) || empty($input['message'])) {
        echo json_encode(['success' => false, 'message' => 'Title and message required']);
        exit;
    }
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Insert notice
        $stmt = $db->prepare("INSERT INTO notices (title, content, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$input['title'], $input['message'], $user_id]);
        $notice_id = $db->lastInsertId();
        
        // Get all employees
        $stmt = $db->prepare("SELECT id FROM users WHERE user_type = 'employee' AND status = 'active'");
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create notification for each employee
        $notification_title = "📢 New Notice: " . $input['title'];
        $notification_message = substr($input['message'], 0, 100) . (strlen($input['message']) > 100 ? '...' : '');
        
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, 'info', 0)");
        
        foreach ($employees as $employee) {
            $stmt->execute([$employee['id'], $notification_title, $notification_message]);
        }
        
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Notice posted and notifications sent to all employees']);
        
    } catch(Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
elseif ($method == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notice_id = $input['notice_id'] ?? 0;
    
    $stmt = $db->prepare("SELECT id FROM notice_reads WHERE notice_id = ? AND user_id = ?");
    $stmt->execute([$notice_id, $user_id]);
    
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO notice_reads (notice_id, user_id) VALUES (?, ?)");
        $stmt->execute([$notice_id, $user_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Notice marked as read']);
}
elseif ($method == 'DELETE') {
    if ($user_type !== 'hr') {
        echo json_encode(['success' => false, 'message' => 'Only HR can delete notices']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $notice_id = $input['notice_id'] ?? 0;
    
    $stmt = $db->prepare("DELETE FROM notice_reads WHERE notice_id = ?");
    $stmt->execute([$notice_id]);
    
    $stmt = $db->prepare("DELETE FROM notices WHERE id = ? AND created_by = ?");
    $stmt->execute([$notice_id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Notice deleted']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

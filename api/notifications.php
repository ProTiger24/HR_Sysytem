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
    // Get unread count (30 days)
    if (isset($_GET['count'])) {
        $stmt = $db->prepare("SELECT COUNT(*) as unread FROM notifications 
                               WHERE user_id = ? AND is_read = 0 
                               AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$user_id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
        echo json_encode(['success' => true, 'unread' => (int)$count]);
        exit;
    }
    
    // Get all notifications (last 30 days)
    $stmt = $db->prepare("SELECT * FROM notifications 
                           WHERE user_id = ? 
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                           ORDER BY created_at DESC 
                           LIMIT 50");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count unread
    $unread = $db->prepare("SELECT COUNT(*) as unread FROM notifications 
                             WHERE user_id = ? AND is_read = 0");
    $unread->execute([$user_id]);
    $unread_count = $unread->fetch(PDO::FETCH_ASSOC)['unread'];
    
    echo json_encode(['success' => true, 'data' => $notifications, 'unread' => (int)$unread_count]);
}
elseif ($method == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if ($id == 0) {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 
                               WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 
                               WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
    }
    echo json_encode(['success' => true, 'message' => 'Notifications marked as read']);
}
elseif ($method == 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Notification ID required']);
        exit;
    }
    
    $stmt = $db->prepare("SELECT id FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit;
    }
    
    $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Notification deleted']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

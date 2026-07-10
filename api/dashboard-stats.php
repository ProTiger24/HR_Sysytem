<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

try {
    // Total employees
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE user_type = 'employee' AND status = 'active'");
    $stmt->execute();
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Today's present attendance
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(DISTINCT employee_id) as present FROM attendance WHERE attendance_date = :today");
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $presentToday = $stmt->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;
    
    // On leave today (approved leaves where today is between start and end date)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT employee_id) as on_leave FROM leave_requests 
                          WHERE :today BETWEEN start_date AND end_date 
                          AND status = 'approved'");
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $onLeave = $stmt->fetch(PDO::FETCH_ASSOC)['on_leave'] ?? 0;
    
    // Pending leaves (all pending requests)
    $stmt = $db->prepare("SELECT COUNT(*) as pending FROM leave_requests WHERE status = 'pending'");
    $stmt->execute();
    $pendingLeaves = $stmt->fetch(PDO::FETCH_ASSOC)['pending'] ?? 0;
    
    // Unread notifications count
    $stmt = $db->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $unreadNotifications = $stmt->fetch(PDO::FETCH_ASSOC)['unread'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_employees' => (int)$totalEmployees,
            'present_today' => (int)$presentToday,
            'on_leave' => (int)$onLeave,
            'pending_leaves' => (int)$pendingLeaves,
            'unread_notifications' => (int)$unreadNotifications
        ]
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'] ?? 0;
$user_type = $_SESSION['user_type'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if ($user_type == 'hr') {
        $stmt = $db->prepare("SELECT l.*, u.first_name, u.last_name, u.employee_id, 
                               CONCAT(u.first_name, ' ', u.last_name) as employee_name
                               FROM leave_requests l 
                               JOIN users u ON l.employee_id = u.id 
                               ORDER BY l.applied_on DESC");
        $stmt->execute();
    } else {
        $stmt = $db->prepare("SELECT * FROM leave_requests WHERE employee_id = :user_id ORDER BY applied_on DESC");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($leaves);
}
elseif ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $leave_type = $input['leave_type'];
    $start_date = $input['start_date'];
    $end_date = $input['end_date'];
    $total_days = $input['total_days'];
    $reason = $input['reason'];
    
    // Check leave balance
    $stmt = $db->prepare("SELECT leave_balance FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $balance = $stmt->fetch(PDO::FETCH_ASSOC)['leave_balance'] ?? 20;
    
    if ($total_days > $balance) {
        echo json_encode(['success' => false, 'message' => 'Insufficient leave balance! Available: ' . $balance . ' days']);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status) 
                           VALUES (:user_id, :leave_type, :start_date, :end_date, :total_days, :reason, 'pending')");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':leave_type', $leave_type);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->bindParam(':total_days', $total_days);
    $stmt->bindParam(':reason', $reason);
    
    if ($stmt->execute()) {
        // Notification for all HR users
        $emp_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        $notify = $db->prepare("INSERT INTO notifications (user_id, title, message, type) 
                                 SELECT id, 'New Leave Request', 
                                 CONCAT(:emp_name, ' has applied for ', :total_days, ' days leave'), 
                                 'info' 
                                 FROM users WHERE user_type = 'hr'");
        $notify->bindParam(':emp_name', $emp_name);
        $notify->bindParam(':total_days', $total_days);
        $notify->execute();
        
        echo json_encode(['success' => true, 'message' => 'Leave request submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit request']);
    }
}
elseif ($method == 'PUT') {
    if ($user_type != 'hr') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents("php://input"), true);
    $leave_id = $input['id'];
    $status = $input['status'];
    $rejection_reason = $input['rejection_reason'] ?? null;
    
    // Get leave details
    $stmt = $db->prepare("SELECT employee_id, total_days FROM leave_requests WHERE id = :id");
    $stmt->bindParam(':id', $leave_id);
    $stmt->execute();
    $leave = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($status == 'approved') {
        // Update leave balance
        $stmt = $db->prepare("UPDATE users SET leave_balance = leave_balance - :days WHERE id = :emp_id");
        $stmt->bindParam(':days', $leave['total_days']);
        $stmt->bindParam(':emp_id', $leave['employee_id']);
        $stmt->execute();
        
        $stmt = $db->prepare("UPDATE leave_requests SET status = :status, approved_by = :hr_id, approved_on = NOW() WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':hr_id', $user_id);
        $stmt->bindParam(':id', $leave_id);
        
        // Notification for employee
        $notify = $db->prepare("INSERT INTO notifications (user_id, title, message, type) 
                                 VALUES (:emp_id, 'Leave Approved', 
                                 CONCAT('Your ', :total_days, ' days leave request has been approved'), 
                                 'success')");
        $notify->bindParam(':emp_id', $leave['employee_id']);
        $notify->bindParam(':total_days', $leave['total_days']);
        $notify->execute();
        
    } else {
        $stmt = $db->prepare("UPDATE leave_requests SET status = :status, approved_by = :hr_id, approved_on = NOW(), rejection_reason = :reason WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':hr_id', $user_id);
        $stmt->bindParam(':id', $leave_id);
        $stmt->bindParam(':reason', $rejection_reason);
        
        // Notification for employee
        $notify = $db->prepare("INSERT INTO notifications (user_id, title, message, type) 
                                 VALUES (:emp_id, 'Leave Rejected', 
                                 CONCAT('Your ', :total_days, ' days leave request has been rejected. Reason: ', :reason), 
                                 'danger')");
        $notify->bindParam(':emp_id', $leave['employee_id']);
        $notify->bindParam(':total_days', $leave['total_days']);
        $notify->bindParam(':reason', $rejection_reason);
        $notify->execute();
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Leave ' . $status . ' successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }
}
?>

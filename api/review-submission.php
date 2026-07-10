<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$hr_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? 0;
$status = $input['status'] ?? 'pending';
$feedback = $input['feedback'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Submission ID required']);
    exit;
}

try {
    // Get submission details before update
    $stmt = $db->prepare("SELECT s.employee_id, s.title, u.first_name, u.last_name 
                           FROM employee_submissions s 
                           JOIN users u ON s.employee_id = u.id 
                           WHERE s.id = ?");
    $stmt->execute([$id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$submission) {
        echo json_encode(['success' => false, 'message' => 'Submission not found']);
        exit;
    }
    
    // Update submission
    $stmt = $db->prepare("UPDATE employee_submissions 
                          SET status = ?, feedback = ?, reviewed_by = ?, reviewed_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$status, $feedback, $hr_id, $id]);
    
    // Send notification to employee
    $status_icons = [
        'pending' => '⏳',
        'reviewed' => '📝', 
        'approved' => '✅',
        'rejected' => '❌'
    ];
    $status_text = ucfirst($status);
    $icon = $status_icons[$status] ?? '📋';
    
    $notification_title = $icon . " Submission Update: " . $submission['title'];
    $notification_message = "Your submission has been " . $status_text . 
                           ($feedback ? ". Feedback: " . $feedback : "");
    
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) 
                           VALUES (?, ?, ?, 'info', 0)");
    $stmt->execute([$submission['employee_id'], $notification_title, $notification_message]);
    
    // Also notify HR who reviewed it
    $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
    $stmt->execute([$hr_id]);
    $hr = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $hr_notification_title = "📋 Submission Reviewed";
    $hr_notification_message = "You reviewed " . $submission['first_name'] . "'s submission: " . $submission['title'];
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) 
                           VALUES (?, ?, ?, 'success', 0)");
    $stmt->execute([$hr_id, $hr_notification_title, $hr_notification_message]);
    
    echo json_encode(['success' => true, 'message' => 'Submission updated and notification sent']);
    
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

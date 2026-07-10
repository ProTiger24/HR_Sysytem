<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if ($user_type == 'employee') {
        // Get employee's performance reviews
        $stmt = $db->prepare("SELECT pr.*, u.first_name as reviewer_first, u.last_name as reviewer_last 
                               FROM performance_reviews pr 
                               JOIN users u ON pr.reviewer_id = u.id 
                               WHERE pr.employee_id = ? 
                               ORDER BY pr.created_at DESC");
        $stmt->execute([$user_id]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($reviews);
    } else {
        // HR gets all reviews
        $stmt = $db->prepare("SELECT pr.*, 
                               e.first_name as emp_first, e.last_name as emp_last,
                               r.first_name as rev_first, r.last_name as rev_last
                               FROM performance_reviews pr 
                               JOIN users e ON pr.employee_id = e.id 
                               JOIN users r ON pr.reviewer_id = r.id 
                               ORDER BY pr.created_at DESC");
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($reviews);
    }
}
elseif ($method == 'POST') {
    // HR adds performance review
    if ($user_type !== 'hr') {
        echo json_encode(['success' => false, 'message' => 'Only HR can add performance reviews']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log the input for debugging
    error_log("Performance Review Input: " . print_r($input, true));
    
    if (empty($input['employee_id'])) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
        exit;
    }
    
    try {
        // Set default values
        $review_title = $input['review_title'] ?? 'Performance Review';
        $overall_rating = isset($input['overall_rating']) ? (int)$input['overall_rating'] : 3;
        
        // Insert performance review
        $stmt = $db->prepare("INSERT INTO performance_reviews 
                              (employee_id, reviewer_id, review_date, review_title, review_period,
                               technical_skills, communication, teamwork, leadership, problem_solving,
                               overall_rating, strengths, weaknesses, goals, comments, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $input['employee_id'],
            $user_id,
            $input['review_date'] ?? date('Y-m-d'),
            $review_title,
            $input['review_period'] ?? 'Monthly',
            $input['technical_skills'] ?? 3,
            $input['communication'] ?? 3,
            $input['teamwork'] ?? 3,
            $input['leadership'] ?? 3,
            $input['problem_solving'] ?? 3,
            $overall_rating,
            $input['strengths'] ?? '',
            $input['weaknesses'] ?? '',
            $input['goals'] ?? '',
            $input['comments'] ?? '',
            $input['status'] ?? 'completed'
        ]);
        
        $review_id = $db->lastInsertId();
        error_log("Performance Review inserted with ID: " . $review_id);
        
        // Get employee info for notification
        $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->execute([$input['employee_id']]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        $employee_name = $employee['first_name'] . ' ' . $employee['last_name'];
        
        // Send notification to employee with rating
        $stars = str_repeat('⭐', $overall_rating);
        $notification_title = "⭐ New Performance Review";
        $notification_message = "You have received a performance review from HR. Title: " . $review_title . ", Rating: " . $overall_rating . "/5 " . $stars;
        
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) 
                               VALUES (?, ?, ?, 'success', 0)");
        $result = $stmt->execute([$input['employee_id'], $notification_title, $notification_message]);
        
        if ($result) {
            error_log("Notification sent to employee ID: " . $input['employee_id']);
        } else {
            error_log("Failed to send notification to employee ID: " . $input['employee_id']);
        }
        
        // Also send notification to HR who created it
        $hr_notification_title = "✅ Performance Review Submitted";
        $hr_notification_message = "You have submitted a performance review for " . $employee_name . " (Title: " . $review_title . ", Rating: " . $overall_rating . "/5)";
        $stmt->execute([$user_id, $hr_notification_title, $hr_notification_message]);
        
        echo json_encode(['success' => true, 'message' => 'Performance review added and notification sent']);
        
    } catch(Exception $e) {
        error_log("Performance Review Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
elseif ($method == 'PUT') {
    // Update review status
    $input = json_decode(file_get_contents('php://input'), true);
    $review_id = $input['id'] ?? 0;
    $status = $input['status'] ?? 'completed';
    
    if (!$review_id) {
        echo json_encode(['success' => false, 'message' => 'Review ID required']);
        exit;
    }
    
    $stmt = $db->prepare("UPDATE performance_reviews SET status = ? WHERE id = ?");
    $stmt->execute([$status, $review_id]);
    
    echo json_encode(['success' => true, 'message' => 'Review updated']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>

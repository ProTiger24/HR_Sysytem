<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    $employee_id = $_SESSION['user_id'];
    $activities = [];

    // ============================================
    // 1. Today's Attendance
    // ============================================
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT check_in, check_out FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    $stmt->execute([$employee_id, $today]);
    $att = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($att) {
        if ($att['check_in']) {
            $activities[] = [
                'id' => 'att_' . time(),
                'type' => 'attendance',
                'description' => '✅ Checked in at ' . date('h:i A', strtotime($att['check_in'])),
                'time' => date('h:i A', strtotime($att['check_in'])),
                'date' => date('Y-m-d')
            ];
        }
        if ($att['check_out']) {
            $activities[] = [
                'id' => 'att_' . time() . '_out',
                'type' => 'attendance',
                'description' => '✅ Checked out at ' . date('h:i A', strtotime($att['check_out'])),
                'time' => date('h:i A', strtotime($att['check_out'])),
                'date' => date('Y-m-d')
            ];
        }
    }

    // ============================================
    // 2. LEAVE REQUESTS - 30 Days
    // ============================================
    $stmt = $db->prepare("SELECT id, leave_type, total_days, status, applied_on 
                           FROM leave_requests 
                           WHERE employee_id = ? 
                           AND applied_on >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                           ORDER BY applied_on DESC");
    $stmt->execute([$employee_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $statusText = $row['status'] == 'pending' ? '📝 Applied for' : 
                     ($row['status'] == 'approved' ? '✅ Approved' : '❌ Rejected');
        $activities[] = [
            'id' => 'leave_' . $row['id'],
            'type' => 'leave',
            'description' => $statusText . ' ' . $row['total_days'] . 'd ' . ucfirst($row['leave_type']) . ' Leave',
            'time' => date('d M Y', strtotime($row['applied_on'])),
            'date' => date('Y-m-d', strtotime($row['applied_on']))
        ];
    }

    // ============================================
    // 3. PERFORMANCE REVIEWS - Latest 10 Only
    // ============================================
    $stmt = $db->prepare("SELECT id, review_title, overall_rating, created_at, status, reviewer_id,
                           (SELECT first_name FROM users WHERE id = reviewer_id) as reviewer_name
                           FROM performance_reviews 
                           WHERE employee_id = ? 
                           ORDER BY created_at DESC 
                           LIMIT 10");
    $stmt->execute([$employee_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rating = $row['overall_rating'] ?? 0;
        $stars = str_repeat('⭐', $rating) . str_repeat('☆', 5 - $rating);
        $statusText = $row['status'] == 'completed' ? '✅' : '⏳';
        $reviewer = $row['reviewer_name'] ?? 'HR';
        
        $activities[] = [
            'id' => 'perf_' . $row['id'],
            'type' => 'performance',
            'description' => $statusText . ' ' . $stars . ' Performance Review from ' . $reviewer . ': ' . ($row['review_title'] ?? 'Review') . ' (' . $rating . '/5)',
            'time' => date('d M Y', strtotime($row['created_at'])),
            'date' => date('Y-m-d', strtotime($row['created_at']))
        ];
    }

    // ============================================
    // 4. NOTICES FROM HR - Recent 24 hours
    // ============================================
    $stmt = $db->prepare("SELECT id, title, content, created_at 
                           FROM notices 
                           WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                           ORDER BY created_at DESC");
    $stmt->execute();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $check = $db->prepare("SELECT id FROM notice_reads WHERE notice_id = ? AND user_id = ?");
        $check->execute([$row['id'], $employee_id]);
        $isRead = $check->fetch() ? '✓' : '●';
        
        $content_preview = substr($row['content'], 0, 50) . (strlen($row['content']) > 50 ? '...' : '');
        
        $activities[] = [
            'id' => 'notice_' . $row['id'],
            'type' => 'notice',
            'description' => $isRead . ' 📢 ' . $row['title'] . ': ' . $content_preview,
            'time' => date('h:i A', strtotime($row['created_at'])),
            'date' => date('Y-m-d', strtotime($row['created_at']))
        ];
    }

    // ============================================
    // 5. NOTIFICATIONS - Recent 24 hours
    // ============================================
    $stmt = $db->prepare("SELECT id, title, message, created_at 
                           FROM notifications 
                           WHERE user_id = ? 
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                           ORDER BY created_at DESC");
    $stmt->execute([$employee_id]);
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activities[] = [
            'id' => 'notif_' . $row['id'],
            'type' => 'notification',
            'description' => '🔔 ' . $row['title'] . ': ' . $row['message'],
            'time' => date('h:i A', strtotime($row['created_at'])),
            'date' => date('Y-m-d', strtotime($row['created_at']))
        ];
    }

    // Sort by date (newest first)
    usort($activities, function($a, $b) {
        return strtotime($b['date'] . ' ' . $b['time']) - strtotime($a['date'] . ' ' . $a['time']);
    });

    // Limit to last 24 hours
    $activities = array_filter($activities, function($act) {
        return strtotime($act['date'] . ' ' . $act['time']) >= strtotime('-24 hours');
    });

    // Limit to latest 20 activities overall
    $activities = array_slice($activities, 0, 20);

    echo json_encode(['success' => true, 'activities' => array_values($activities)]);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

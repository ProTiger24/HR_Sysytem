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
$today = date('Y-m-d');
$now = date('H:i:s');
$office_start = '10:00:00';

$input = json_decode(file_get_contents("php://input"), true);
$type = $input['type'] ?? '';
$action = $_GET['action'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

// GET: Today's status (for employee)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $action == 'today_status') {
    $stmt = $db->prepare("SELECT check_in, check_out, status FROM attendance WHERE employee_id = ? AND attendance_date = ?");
    $stmt->execute([$user_id, $today]);
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($attendance) {
        if ($attendance['check_out']) {
            $status = 'Completed (Checked Out at ' . date('h:i A', strtotime($attendance['check_out'])) . ')';
        } elseif ($attendance['check_in']) {
            $status = 'Checked In at ' . date('h:i A', strtotime($attendance['check_in']));
        } else {
            $status = 'Not Checked In';
        }
        echo json_encode(['success' => true, 'status' => $status]);
    } else {
        echo json_encode(['success' => true, 'status' => 'Not Checked In']);
    }
    exit;
}

// GET: Attendance records (for HR)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SESSION['user_type'] == 'hr') {
    $sql = "SELECT u.first_name, u.last_name, u.employee_id, 
                   a.check_in, a.check_out, 
                   COALESCE(a.status, 'absent') as status
            FROM users u 
            LEFT JOIN attendance a ON u.id = a.employee_id AND a.attendance_date = :date 
            WHERE u.user_type = 'employee' 
            ORDER BY u.first_name ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $present = 0;
    $absent = 0;
    $late = 0;
    
    foreach ($data as &$row) {
        if ($row['check_in']) {
            if ($row['status'] == 'late') {
                $late++;
            } else {
                $present++;
                $row['status'] = 'present';
            }
        } else {
            $row['status'] = 'absent';
            $absent++;
        }
    }
    
    echo json_encode([
        'success' => true, 
        'data' => $data,
        'counts' => [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'total' => count($data)
        ]
    ]);
    exit;
}

// POST: Check In / Check Out
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($type == 'check_in') {
        $stmt = $db->prepare("SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ?");
        $stmt->execute([$user_id, $today]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Already checked in today']);
            exit;
        }
        
        $status = ($now > $office_start) ? 'late' : 'present';
        
        $stmt = $db->prepare("INSERT INTO attendance (employee_id, attendance_date, check_in, status) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $today, $now, $status])) {
            echo json_encode(['success' => true, 'message' => 'Check-in successful at ' . date('h:i A') . ($status == 'late' ? ' (Late)' : '')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Check-in failed']);
        }
    }
    elseif ($type == 'check_out') {
        $stmt = $db->prepare("UPDATE attendance SET check_out = ? WHERE employee_id = ? AND attendance_date = ? AND check_out IS NULL");
        if ($stmt->execute([$now, $user_id, $today]) && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Check-out successful at ' . date('h:i A')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No check-in found for today']);
        }
    }
    exit;
}
?>

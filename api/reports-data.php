<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();

// Employee Statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'employee'");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as active FROM users WHERE user_type = 'employee' AND status = 'active'");
$active = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

$stmt = $db->query("SELECT COUNT(*) as inactive FROM users WHERE user_type = 'employee' AND status != 'active'");
$inactive = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

// Attendance (Today)
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE attendance_date = :today GROUP BY status");
$stmt->bindParam(':today', $today);
$stmt->execute();
$attendanceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$present = $absent = $late = 0;
foreach($attendanceData as $row) {
    if($row['status'] == 'present') $present = $row['count'];
    elseif($row['status'] == 'absent') $absent = $row['count'];
    elseif($row['status'] == 'late') $late = $row['count'];
}

// Department-wise employees
$stmt = $db->query("SELECT department, COUNT(*) as count FROM users WHERE user_type = 'employee' AND department IS NOT NULL AND department != '' GROUP BY department");
$departments = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $departments[$row['department']] = $row['count'];
}

// Leave Statistics
$stmt = $db->query("SELECT status, COUNT(*) as count FROM leave_requests GROUP BY status");
$leaves = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $leaves[$row['status']] = $row['count'];
}

echo json_encode([
    'success' => true,
    'employees' => [
        'total' => (int)$total,
        'active' => (int)$active,
        'inactive' => (int)$inactive
    ],
    'attendance' => [
        'present' => (int)$present,
        'absent' => (int)$absent,
        'late' => (int)$late
    ],
    'departments' => $departments,
    'leaves' => $leaves
]);
?>

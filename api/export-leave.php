<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="leave_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Reason', 'Status']);

$stmt = $db->query("SELECT u.first_name, u.last_name, l.leave_type, l.start_date, l.end_date, l.total_days, l.reason, l.status 
                    FROM leave_requests l 
                    JOIN users u ON l.employee_id = u.id 
                    ORDER BY l.applied_on DESC");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['first_name'] . ' ' . $row['last_name'],
        $row['leave_type'] ?? 'N/A',
        $row['start_date'] ?? 'N/A',
        $row['end_date'] ?? 'N/A',
        $row['total_days'] ?? 0,
        $row['reason'] ?? 'N/A',
        $row['status'] ?? 'N/A'
    ]);
}
fclose($output);

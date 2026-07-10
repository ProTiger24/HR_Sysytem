<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Employee', 'Date', 'Check In', 'Check Out', 'Status']);

$stmt = $db->query("SELECT u.first_name, u.last_name, a.attendance_date, a.check_in, a.check_out, a.status 
                    FROM attendance a 
                    JOIN users u ON a.employee_id = u.id 
                    ORDER BY a.attendance_date DESC");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['first_name'] . ' ' . $row['last_name'],
        $row['attendance_date'],
        $row['check_in'] ?? 'N/A',
        $row['check_out'] ?? 'N/A',
        $row['status'] ?? 'N/A'
    ]);
}
fclose($output);

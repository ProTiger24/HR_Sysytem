<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Employee ID', 'Name', 'Email', 'Department', 'Position', 'Salary', 'Status', 'Join Date']);

$stmt = $db->query("SELECT employee_id, first_name, last_name, email, department, position, salary, status, join_date FROM users WHERE user_type='employee'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['employee_id'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['email'],
        $row['department'] ?? 'N/A',
        $row['position'] ?? 'N/A',
        $row['salary'] ?? 0,
        $row['status'] ?? 'active',
        $row['join_date'] ?? 'N/A'
    ]);
}
fclose($output);

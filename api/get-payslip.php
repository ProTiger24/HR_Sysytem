<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? 0;
$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT p.*, u.first_name, u.last_name, u.employee_id 
                       FROM payroll p 
                       JOIN users u ON p.employee_id = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$payroll = $stmt->fetch(PDO::FETCH_ASSOC);

if ($payroll) {
    echo json_encode(['success' => true, 'data' => $payroll]);
} else {
    echo json_encode(['success' => false, 'message' => 'Payslip not found']);
}
?>

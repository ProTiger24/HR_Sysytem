<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT id, employee_id, first_name, last_name FROM users WHERE user_type = 'employee' AND status = 'active' ORDER BY first_name ASC");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $employees]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

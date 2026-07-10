<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $search = $_GET['search'] ?? '';
    $dept = $_GET['department'] ?? '';
    $status = $_GET['status'] ?? '';

    $sql = "SELECT id, employee_id, first_name, last_name, email, phone, department, position, salary, status, join_date 
            FROM users WHERE user_type='employee'";
    
    $params = [];
    if ($search) {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR employee_id LIKE ?)";
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    if ($dept) {
        $sql .= " AND department = ?";
        $params[] = $dept;
    }
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}
elseif ($method == 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
        exit;
    }
    
    try {
        // Check if employee exists
        $stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND user_type = 'employee'");
        $stmt->execute([$id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$employee) {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            exit;
        }
        
        // Delete employee (cascade will handle related records)
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND user_type = 'employee'");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Employee ' . $employee['first_name'] . ' ' . $employee['last_name'] . ' deleted successfully']);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
elseif ($method == 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    $salary = $input['salary'] ?? 0;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Employee ID required']);
        exit;
    }
    
    $stmt = $db->prepare("UPDATE users SET salary = ? WHERE id = ? AND user_type = 'employee'");
    $stmt->execute([$salary, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Salary updated successfully']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

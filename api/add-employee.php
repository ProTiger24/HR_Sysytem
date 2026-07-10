<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Debug: log received data
error_log("Add Employee Data: " . print_r($input, true));

// Validate required fields
if (empty($input['first_name']) || empty($input['last_name']) || empty($input['email']) || empty($input['password'])) {
    echo json_encode(['success' => false, 'message' => 'First name, last name, email and password are required']);
    exit;
}

// Check if email already exists
$check = $db->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$input['email']]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    exit;
}

$user_type = $input['user_type'] ?? 'employee';
$prefix = ($user_type == 'hr') ? 'HR' : 'EMP';
$employee_id = $prefix . date('Y') . rand(1000, 9999);

$password_hash = password_hash($input['password'], PASSWORD_DEFAULT);
$join_date = $input['join_date'] ?? date('Y-m-d');

// Insert with new fields
$stmt = $db->prepare("INSERT INTO users (
    employee_id, 
    first_name, 
    last_name, 
    email, 
    phone, 
    department, 
    position, 
    salary, 
    password_hash, 
    user_type, 
    status, 
    join_date,
    blood_group,
    emergency_contact,
    date_of_birth,
    address
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?)");

$result = $stmt->execute([
    $employee_id,
    $input['first_name'],
    $input['last_name'],
    $input['email'],
    $input['phone'] ?? '',
    $input['department'] ?? '',
    $input['position'] ?? '',
    $input['salary'] ?? 0,
    $password_hash,
    $user_type,
    $join_date,
    $input['blood_group'] ?? '',
    $input['emergency_contact'] ?? '',
    $input['date_of_birth'] ?? null,
    $input['address'] ?? ''
]);

if ($result) {
    echo json_encode([
        'success' => true, 
        'employee_id' => $employee_id, 
        'user_type' => $user_type,
        'message' => 'Employee added successfully'
    ]);
} else {
    $error = $stmt->errorInfo();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $error[2]]);
}
?>

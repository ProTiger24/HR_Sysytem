<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    // Get all employees
    $query = "SELECT id, employee_id, first_name, last_name, email, phone, department, position, status, created_at 
              FROM users WHERE user_type = 'employee' ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $employees = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $employees[] = $row;
    }
    
    echo json_encode($employees);
    
} elseif ($method == 'POST') {
    // Add new employee
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->first_name) && !empty($data->email) && !empty($data->password)) {
        $employee_id = "EMP" . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users SET employee_id=?, first_name=?, last_name=?, email=?, phone=?, 
                 department=?, position=?, user_type='employee', password_hash=?";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $employee_id);
        $stmt->bindParam(2, $data->first_name);
        $stmt->bindParam(3, $data->last_name);
        $stmt->bindParam(4, $data->email);
        $stmt->bindParam(5, $data->phone);
        $stmt->bindParam(6, $data->department);
        $stmt->bindParam(7, $data->position);
        $stmt->bindParam(8, $password_hash);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Employee added successfully.", "employee_id" => $employee_id));
        } else {
            echo json_encode(array("message" => "Unable to add employee."));
        }
    }
}
?>
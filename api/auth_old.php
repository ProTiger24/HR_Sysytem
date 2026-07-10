<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if ($action == 'register') {
        $first_name = trim($input['first_name'] ?? '');
        $last_name = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $department = trim($input['department'] ?? '');
        $position = trim($input['position'] ?? '');
        $password = trim($input['password'] ?? '');
        $user_type = trim($input['user_type'] ?? 'employee');
        
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields required']);
            exit;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be 6+ characters']);
            exit;
        }
        
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindParam(':email', $email);
        $check->execute();
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        $employee_id = ($user_type == 'hr' ? 'HR' : 'EMP') . date('Y') . rand(100, 9999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $join_date = date('Y-m-d');
        
        $query = "INSERT INTO users (employee_id, first_name, last_name, email, phone, department, position, password_hash, user_type, status, join_date) 
                  VALUES (:employee_id, :first_name, :last_name, :email, :phone, :department, :position, :password_hash, :user_type, 'active', :join_date)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':employee_id', $employee_id);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':user_type', $user_type);
        $stmt->bindParam(':join_date', $join_date);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registration successful', 'employee_id' => $employee_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
        exit;
    }
    
    elseif ($action == 'login') {
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        
        $query = "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            echo json_encode(['success' => true, 'message' => 'Login successful', 'user_type' => $user['user_type']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>

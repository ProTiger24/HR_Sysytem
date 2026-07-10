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
    
    // ========== REGISTER ==========
    if ($action == 'register') {
        $first_name = trim($input['first_name'] ?? '');
        $last_name = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $department = trim($input['department'] ?? '');
        $position = trim($input['position'] ?? '');
        $password = trim($input['password'] ?? '');
        $user_type = trim($input['user_type'] ?? 'employee');
        
        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields required']);
            exit;
        }
        
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be 6+ characters']);
            exit;
        }
        
        // Check if email exists
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindParam(':email', $email);
        $check->execute();
        
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        // Generate employee ID
        $employee_id = ($user_type == 'hr' ? 'HR' : 'EMP') . date('Y') . rand(100, 9999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $join_date = date('Y-m-d');
        
        // Insert user
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
    
    // ========== LOGIN ==========
    elseif ($action == 'login') {
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');
        
        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            exit;
        }
        
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
            $_SESSION['department'] = $user['department'] ?? '';
            $_SESSION['position'] = $user['position'] ?? '';
            
            // 🔒 Check if default password (force change)
            $force_change = false;
            if (password_verify('123456', $user['password_hash'])) {
                $force_change = true;
                $_SESSION['force_password_change'] = true;
            } else {
                unset($_SESSION['force_password_change']);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful', 
                'user_type' => $user['user_type'],
                'force_password_change' => $force_change
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        exit;
    }
    
    // ========== CHANGE PASSWORD ==========
    elseif ($action == 'change_password') {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login first.']);
            exit;
        }
        
        $current = trim($input['current_password'] ?? '');
        $new = trim($input['new_password'] ?? '');
        $confirm = trim($input['confirm_password'] ?? '');
        
        // Validation
        if (empty($current) || empty($new) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }
        
        if ($new !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit;
        }
        
        // Password strength check
        $errors = [];
        if (strlen($new) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $new)) {
            $errors[] = 'Password must contain at least 1 uppercase letter';
        }
        if (!preg_match('/[a-z]/', $new)) {
            $errors[] = 'Password must contain at least 1 lowercase letter';
        }
        if (!preg_match('/[0-9]/', $new)) {
            $errors[] = 'Password must contain at least 1 number';
        }
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $new)) {
            $errors[] = 'Password must contain at least 1 special character (!@#$%^&*)';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($current, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Update password
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->bindParam(':password_hash', $new_hash);
        $stmt->bindParam(':id', $user_id);
        
        if ($stmt->execute()) {
            // Clear force password change flag
            unset($_SESSION['force_password_change']);
            
            echo json_encode([
                'success' => true,
                'force_cleared' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update password']);
        }
        exit;
    }
}

// If no valid action
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>

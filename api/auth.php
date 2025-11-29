~~~{"id":"80713","variant":"standard","subject":"Authentication API"} 
<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($data) {
        try {
            // Check if email exists
            $checkQuery = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([$data['email']]);
            if ($checkStmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Generate employee ID
            $employee_id = $this->generateEmployeeId();

            // Insert user
            $query = "INSERT INTO users (employee_id, first_name, last_name, email, phone, department, position, password_hash, user_type, status)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            $stmt = $this->conn->prepare($query);
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt->execute([
                $employee_id,
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['department'],
                $data['position'],
                $hashedPassword,
                $data['user_type']
            ]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Registration successful', 'employee_id' => $employee_id];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM users WHERE email = ? AND status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email]);

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['employee_id'] = $user['employee_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];

                    return [
                        'success' => true,
                        'user_type' => $user['user_type'],
                        'user_data' => [
                            'id' => $user['id'],
                            'employee_id' => $user['employee_id'],
                            'first_name' => $user['first_name'],
                            'last_name' => $user['last_name'],
                            'email' => $user['email'],
                            'department' => $user['department'],
                            'position' => $user['position']
                        ]
                    ];
                }
            }
            return ['success' => false, 'message' => 'Invalid email or password'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    private function generateEmployeeId() {
        $prefix = "EMP";
        $year = date('Y');
        $query = "SELECT employee_id FROM users WHERE employee_id LIKE ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$prefix.$year.'%']);
        if ($stmt->rowCount() > 0) {
            $lastId = $stmt->fetch(PDO::FETCH_ASSOC)['employee_id'];
            $lastNumber = intval(substr($lastId, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $action = $_GET['action'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'register') {
        echo json_encode($auth->register($input));
    } elseif ($action === 'login') {
        echo json_encode($auth->login($input['email'], $input['password']));
    }
}
?>



<?php
session_start();
require_once '../config/database.php';

// Clean any accidental output (VERY IMPORTANT)
ob_start();

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

class Auth {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // -------------------------
    // REGISTER
    // -------------------------
    public function register($data) {

        if (!isset($data['name'], $data['email'], $data['password'], $data['user_type'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        $name = $data['name'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user_type = $data['user_type'];

        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        $query = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute([$name, $email, $password, $user_type])) {
            return ['success' => true, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    // -------------------------
    // LOGIN
    // -------------------------
    public function login($email, $password) {

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Incorrect password'];
        }

        $token = bin2hex(random_bytes(32));

        return [
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user_type' => $user['user_type'],
            'user_data' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email']
            ]
        ];
    }
}

// -------------------------
// API ROUTES
// -------------------------

$auth = new Auth();
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

// Clear unwanted output again
ob_clean();

if ($action === 'register') {
    echo json_encode($auth->register($input));
    exit;
}

if ($action === 'login') {
    echo json_encode($auth->login($input['email'], $input['password']));
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;

<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kormoshathi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Set timezone
date_default_timezone_set('Asia/Dhaka');

class Database {
    private $conn;
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set timezone in MySQL
            $this->conn->exec("SET time_zone = '+06:00'");
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
            return null;
        }
        return $this->conn;
    }
}
?>

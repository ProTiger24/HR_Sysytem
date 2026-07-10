<?php
class Database {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $env = $this->loadEnv(__DIR__ . '/../.env');
        $this->host = $env['DB_HOST'] ?? 'localhost';
        $this->port = $env['DB_PORT'] ?? 3306;
        $this->db_name = $env['DB_NAME'] ?? 'kormoshathi';
        $this->username = $env['DB_USER'] ?? 'root';
        $this->password = $env['DB_PASS'] ?? '';
    }

    private function loadEnv($path) {
        $env = [];
        if (!file_exists($path)) {
            error_log(".env file not found at: " . $path);
            return $env;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            // Skip lines without '='
            if (strpos($line, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
        return $env;
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>

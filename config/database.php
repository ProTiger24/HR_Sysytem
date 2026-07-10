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
        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || $line[0] === ';') continue;
                if (strpos($line, '=') === false) continue;
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
        // Render/system environment variables override .env file
        $keys = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'GROQ_API_KEY', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_HOST', 'SMTP_PORT'];
        foreach ($keys as $key) {
            $val = getenv($key);
            if ($val !== false) {
                $env[$key] = $val;
            }
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

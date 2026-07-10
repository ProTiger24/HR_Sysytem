<?php
header("Content-Type: application/json");
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = (new Database())->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'GET') {
        $stmt = $db->query("SELECT * FROM job_postings WHERE status = 'open' ORDER BY created_at DESC");
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $jobs]);
    }
    elseif ($method == 'POST') {
        if ($_SESSION['user_type'] !== 'hr') {
            echo json_encode(['success' => false, 'message' => 'Only HR can post jobs']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(empty($input['job_title']) || empty($input['department']) || empty($input['job_description']) || empty($input['requirements'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $stmt = $db->prepare("INSERT INTO job_postings 
                               (job_title, department, job_type, vacancies, job_description, requirements, salary_range, location, last_date, status, posted_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        
        $result = $stmt->execute([
            $input['job_title'],
            $input['department'],
            $input['job_type'] ?? 'full_time',
            $input['vacancies'] ?? 1,
            $input['job_description'],
            $input['requirements'],
            $input['salary_range'] ?? '',
            $input['location'] ?? '',
            $input['last_date'] ?? null,
            $input['status'] ?? 'open'
        ]);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Job posted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

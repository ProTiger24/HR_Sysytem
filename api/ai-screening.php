<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    if (empty($input['job_title']) || empty($input['required_skills']) || 
        empty($input['start_date']) || empty($input['end_date'])) {
        echo json_encode(['success' => false, 'message' => 'Job title, skills, start and end date required']);
        exit;
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO ai_screening_config 
                              (job_title, department, start_date, end_date, 
                               required_skills, priority_skills, min_experience, 
                               preferred_education, auto_shortlist_score, auto_reject_score, 
                               interview_date, interview_location, interview_type, 
                               meeting_link, interviewer_name, notes, created_by) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $input['job_title'],
            $input['department'] ?? '',
            $input['start_date'],
            $input['end_date'],
            $input['required_skills'],
            $input['priority_skills'] ?? '',
            intval($input['min_experience'] ?? 0),
            $input['preferred_education'] ?? '',
            intval($input['auto_shortlist_score'] ?? 80),
            intval($input['auto_reject_score'] ?? 40),
            $input['interview_date'] ?? null,
            $input['interview_location'] ?? '',
            $input['interview_type'] ?? 'physical',
            $input['meeting_link'] ?? '',
            $input['interviewer_name'] ?? '',
            $input['notes'] ?? '',
            $_SESSION['user_id']
        ]);
        
        $config_id = $db->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'AI Screening started successfully!',
            'config_id' => $config_id
        ]);
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
elseif ($method == 'GET') {
    $action = $_GET['action'] ?? '';
    $config_id = $_GET['config_id'] ?? 0;
    
    if ($action === 'results') {
        $stmt = $db->prepare("SELECT * FROM ai_screening_results WHERE config_id = ? ORDER BY match_score DESC");
        $stmt->execute([$config_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'shortlist') {
        $stmt = $db->prepare("SELECT * FROM ai_screening_results 
                              WHERE config_id = ? AND recommendation = 'shortlist' 
                              ORDER BY match_score DESC");
        $stmt->execute([$config_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'config') {
        $stmt = $db->prepare("SELECT * FROM ai_screening_config WHERE id = ?");
        $stmt->execute([$config_id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

<?php
header("Content-Type: application/json");
require_once '../config/database.php';
require_once '../config/ai_config.php';
require_once '../config/email_config.php';
require_once '../vendor/autoload.php';

use Smalot\PdfParser\Parser;

$db = (new Database())->getConnection();

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name and email required']);
    exit;
}

// Get active screening config
$stmt = $db->prepare("SELECT * FROM ai_screening_config 
                       WHERE is_active = 1 
                       AND start_date <= CURDATE() 
                       AND end_date >= CURDATE() 
                       ORDER BY id DESC 
                       LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    echo json_encode(['success' => false, 'message' => 'No active job openings']);
    exit;
}

// Handle file upload
$resume_text = '';
$file_path = '';

if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/resumes/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_name = time() . '_' . basename($_FILES['resume']['name']);
    $file_path = 'uploads/resumes/' . $file_name;
    $full_path = '../' . $file_path;
    move_uploaded_file($_FILES['resume']['tmp_name'], $full_path);
    
    $resume_text = extractResumeText($full_path);
    
    if (empty($resume_text) || strlen($resume_text) < 20) {
        $resume_text = "Candidate: $name, Email: $email";
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Resume file required']);
    exit;
}

// Insert into database
try {
    $stmt = $db->prepare("INSERT INTO ai_screening_results 
                          (config_id, candidate_name, candidate_email, candidate_phone, 
                           resume_text, resume_file_path, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $config['id'], 
        $name, 
        $email, 
        $phone, 
        $resume_text, 
        $file_path
    ]);
    $result_id = $db->lastInsertId();
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// 📧 Send Confirmation Email
$subject = "📄 Resume Received - KormoShathi";
$body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; }
        .header { background: #2c5aa0; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { background: #eee; padding: 10px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>📄 Resume Received</h2>
        </div>
        <div class='content'>
            <p>Dear <strong>$name</strong>,</p>
            <p>Thank you for submitting your resume for the position of <strong>{$config['job_title']}</strong>.</p>
            <p>We have received your application and our AI system is reviewing it.</p>
            <p>You will be notified about the next steps soon.</p>
            <br>
            <p>Best regards,</p>
            <p><strong>HR Team - KormoShathi</strong></p>
        </div>
        <div class='footer'>
            <p>&copy; 2026 KormoShathi. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

$email_sent = sendEmail($email, $subject, $body);
if ($email_sent) {
    error_log("✅ Confirmation email sent to: $email");
} else {
    error_log("❌ Failed to send confirmation email to: $email");
}

// 🤖 AI Screening with Groq
$ai_result = screenResumeWithGroq($resume_text, $config);

if ($ai_result && isset($ai_result['match_score']) && $ai_result['match_score'] > 0) {
    // 🔥 Fix: Convert skills array to string if needed
    $skills_found = $ai_result['skills_found'] ?? '';
    if (is_array($skills_found)) {
        $skills_found = implode(', ', $skills_found);
    }
    $missing_skills = $ai_result['missing_skills'] ?? '';
    if (is_array($missing_skills)) {
        $missing_skills = implode(', ', $missing_skills);
    }
    $priority_skills = $ai_result['priority_skills_found'] ?? '';
    if (is_array($priority_skills)) {
        $priority_skills = implode(', ', $priority_skills);
    }
    
    $stmt = $db->prepare("UPDATE ai_screening_results 
                          SET match_score = ?, skills_found = ?, missing_skills = ?, 
                              priority_skills_found = ?, summary = ?, recommendation = ?,
                              status = 'screened'
                          WHERE id = ?");
    $stmt->execute([
        $ai_result['match_score'] ?? 0,
        $skills_found,
        $missing_skills,
        $priority_skills,
        $ai_result['summary'] ?? '',
        $ai_result['recommendation'] ?? 'review',
        $result_id
    ]);
    $match_score = $ai_result['match_score'];
    $recommendation = $ai_result['recommendation'];
    error_log("✅ AI Success: Score=$match_score, Skills=$skills_found");
} else {
    $score = calculateManualScore($resume_text, $config);
    $recommendation = $score >= 80 ? 'shortlist' : ($score >= 40 ? 'review' : 'reject');
    
    $stmt = $db->prepare("UPDATE ai_screening_results 
                          SET match_score = ?, 
                              skills_found = ?,
                              summary = 'Manual calculation',
                              recommendation = ?,
                              status = 'screened'
                          WHERE id = ?");
    $stmt->execute([
        $score, 
        $config['required_skills'] ?? '', 
        $recommendation, 
        $result_id
    ]);
    $match_score = $score;
    error_log("⚠️ Manual: Score=$score, Recommendation=$recommendation");
}

echo json_encode([
    'success' => true, 
    'message' => 'Resume submitted and screened!',
    'match_score' => $match_score ?? 0,
    'recommendation' => $recommendation ?? 'review'
]);

// ============================================
// 📄 Extract Resume Text
// ============================================
function extractResumeText($file_path) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    $text = '';
    
    if ($ext === 'txt') {
        $text = file_get_contents($file_path);
    } 
    elseif ($ext === 'pdf') {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($file_path);
            $text = $pdf->getText();
        } catch(Exception $e) {
            $text = "PDF: " . basename($file_path);
        }
    } 
    elseif ($ext === 'docx') {
        $zip = new ZipArchive();
        if ($zip->open($file_path) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $text = strip_tags($xml);
                $text = preg_replace('/\s+/', ' ', $text);
            }
            $zip->close();
        }
    }
    
    return $text ?: "Resume from " . basename($file_path);
}

function calculateManualScore($resume_text, $config) {
    $required = strtolower($config['required_skills'] ?? '');
    $skills = array_filter(array_map('trim', explode(',', $required)));
    $text_lower = strtolower($resume_text);
    
    if (empty($skills)) return 50;
    
    $found = 0;
    foreach ($skills as $skill) {
        if (strpos($text_lower, $skill) !== false) $found++;
    }
    
    return round(($found / count($skills)) * 100);
}

function screenResumeWithGroq($resume_text, $config) {
    $api_key = AI_API_KEY;
    $url = AI_API_URL;
    
    $required_skills = $config['required_skills'] ?? '';
    $job_title = $config['job_title'] ?? 'Job';
    
    $prompt = "Analyze this resume. Job: $job_title. Required Skills: $required_skills.

Resume: " . substr($resume_text, 0, 3000) . "

Return ONLY JSON:
{
    \"match_score\": 85,
    \"skills_found\": \"PHP, Laravel, MySQL, JavaScript\",
    \"missing_skills\": \"Docker, AWS\",
    \"priority_skills_found\": \"Laravel, MySQL\",
    \"summary\": \"Strong candidate with relevant skills\",
    \"recommendation\": \"shortlist\"
}";

    $data = [
        'model' => AI_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => 'Return only valid JSON. Skills should be comma separated strings, not arrays.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.3,
        'max_tokens' => 500
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        $content = $result['choices'][0]['message']['content'] ?? '';
        $content = trim($content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/^```\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        
        $json_start = strpos($content, '{');
        $json_end = strrpos($content, '}');
        if ($json_start !== false && $json_end !== false) {
            $json = substr($content, $json_start, $json_end - $json_start + 1);
            return json_decode($json, true);
        }
    }
    
    return null;
}
?>

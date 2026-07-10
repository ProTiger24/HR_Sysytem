<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';
require_once '../config/email_config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$input = json_decode(file_get_contents('php://input'), true);

// Log input
error_log("send-interview.php Input: " . print_r($input, true));

$result_ids = $input['result_ids'] ?? [];
$interview_date = $input['interview_date'] ?? '';
$interview_location = $input['interview_location'] ?? '';
$interview_type = $input['interview_type'] ?? 'physical';
$meeting_link = $input['meeting_link'] ?? '';
$notes = $input['notes'] ?? '';
$interviewer_name = $input['interviewer_name'] ?? 'HR Manager';

if (empty($result_ids) || empty($interview_date)) {
    echo json_encode(['success' => false, 'message' => 'Select candidates and interview date']);
    exit;
}

$sent = 0;

foreach ($result_ids as $result_id) {
    error_log("Processing candidate ID: " . $result_id);
    
    $stmt = $db->prepare("SELECT candidate_name, candidate_email FROM ai_screening_results WHERE id = ?");
    $stmt->execute([$result_id]);
    $c = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($c) {
        $subject = "📅 Interview Call - KormoShathi";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { background: #eee; padding: 10px; text-align: center; font-size: 12px; }
                .details { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>📅 Interview Call</h2>
                </div>
                <div class='content'>
                    <p>Dear <strong>{$c['candidate_name']}</strong>,</p>
                    <p>Congratulations! You have been <strong>shortlisted</strong> for an interview.</p>
                    
                    <div class='details'>
                        <p><strong>📅 Date:</strong> " . date('l, d M Y, h:i A', strtotime($interview_date)) . "</p>
                        <p><strong>📍 Location:</strong> " . ($interview_location ?: 'To be confirmed') . "</p>
                        <p><strong>🔗 Type:</strong> " . ucfirst($interview_type) . "</p>
                        " . ($meeting_link ? "<p><strong>🔗 Meeting Link:</strong> <a href='$meeting_link'>$meeting_link</a></p>" : "") . "
                        " . ($notes ? "<p><strong>📝 Notes:</strong> $notes</p>" : "") . "
                        <p><strong>👤 Interviewer:</strong> $interviewer_name</p>
                    </div>
                    
                    <p>Please confirm your availability by replying to this email.</p>
                    <br>
                    <p>Best regards,</p>
                    <p><strong>$interviewer_name</strong><br>HR Team - KormoShathi</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 KormoShathi. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        error_log("📧 Sending interview email to: " . $c['candidate_email']);
        
        $email_sent = sendEmail($c['candidate_email'], $subject, $body);
        
        if ($email_sent) {
            $stmt = $db->prepare("UPDATE ai_screening_results SET status = 'interview_scheduled', interview_sent = 1 WHERE id = ?");
            $stmt->execute([$result_id]);
            $sent++;
            error_log("✅ Interview email sent to: " . $c['candidate_email']);
        } else {
            error_log("❌ Failed to send interview email to: " . $c['candidate_email']);
        }
    } else {
        error_log("❌ Candidate not found for ID: " . $result_id);
    }
}

error_log("Total interview emails sent: " . $sent);
echo json_encode(['success' => true, 'message' => "Interview invitations sent to $sent candidate(s)"]);
?>

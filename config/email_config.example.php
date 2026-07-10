<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $body) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USERNAME'] ?? 'your-email@gmail.com';
        $mail->Password   = $env['SMTP_PASSWORD'] ?? 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env['SMTP_PORT'] ?? 587;
        
        $mail->setFrom('hr@kormoshathi.com', 'KormoShathi HR');
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>

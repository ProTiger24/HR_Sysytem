<?php
// ============================================
// Email Configuration - Brevo API
// ============================================
function sendEmail($to, $subject, $body) {
    $api_key = getenv('BREVO_API_KEY');

    if (!$api_key) {
        error_log("❌ BREVO_API_KEY not set in environment");
        return false;
    }

    $data = [
        'sender' => [
            'name' => 'KormoShathi HR',
            'email' => 'abdulalim528260@gmail.com'
        ],
        'to' => [
            ['email' => $to]
        ],
        'subject' => $subject,
        'htmlContent' => $body
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'api-key: ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode == 201) {
        error_log("✅ Email sent successfully to: $to");
        return true;
    } else {
        error_log("❌ Brevo Email Error (HTTP $httpCode): $response | cURL: $curlError");
        return false;
    }
}
?>

<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

function uploadToCloudinary($file_tmp_path, $public_id) {
    $cloud_url = getenv('CLOUDINARY_URL');
    if (!$cloud_url) {
        return ['error' => 'CLOUDINARY_URL not configured'];
    }
    preg_match('/cloudinary:\/\/(.*):(.*)@(.*)/', $cloud_url, $matches);
    $api_key = $matches[1] ?? '';
    $api_secret = $matches[2] ?? '';
    $cloud_name = $matches[3] ?? '';

    if (!$api_key || !$api_secret || !$cloud_name) {
        return ['error' => 'Invalid CLOUDINARY_URL format'];
    }

    $timestamp = time();
    $params_to_sign = "public_id=$public_id&timestamp=$timestamp";
    $signature = sha1($params_to_sign . $api_secret);

    $url = "https://api.cloudinary.com/v1_1/$cloud_name/image/upload";

    $post_data = [
        'file' => new CURLFile($file_tmp_path),
        'api_key' => $api_key,
        'timestamp' => $timestamp,
        'public_id' => $public_id,
        'signature' => $signature
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode == 200 && isset($result['secure_url'])) {
        return ['success' => true, 'url' => $result['secure_url']];
    } else {
        return ['error' => $result['error']['message'] ?? 'Upload failed', 'raw' => $response];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, WEBP files are allowed']);
        exit;
    }

    $public_id = 'kormoshathi/profile_' . $user_id . '_' . time();
    $upload_result = uploadToCloudinary($file['tmp_name'], $public_id);

    if (isset($upload_result['success'])) {
        $image_url = $upload_result['url'];

        $stmt = $db->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id");
        $stmt->bindParam(':profile_picture', $image_url);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $_SESSION['profile_picture'] = $image_url;

        echo json_encode(['success' => true, 'message' => 'Profile picture updated', 'image_path' => $image_url]);
    } else {
        error_log("Cloudinary upload error: " . json_encode($upload_result));
        echo json_encode(['success' => false, 'message' => 'Upload failed: ' . ($upload_result['error'] ?? 'Unknown error')]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>

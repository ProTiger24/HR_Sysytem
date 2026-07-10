<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$lastCheck = isset($_GET['last_check']) ? $_GET['last_check'] : date('Y-m-d H:i:s', strtotime('-30 seconds'));

try {
    // Check for new attendance
    $stmt = $db->prepare("SELECT COUNT(*) as new_count FROM attendance WHERE created_at > :last_check");
    $stmt->bindParam(':last_check', $lastCheck);
    $stmt->execute();
    $newCount = $stmt->fetch(PDO::FETCH_ASSOC)['new_count'];
    
    echo json_encode([
        'success' => true,
        'has_updates' => $newCount > 0,
        'new_count' => $newCount,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'has_updates' => false]);
}
?>
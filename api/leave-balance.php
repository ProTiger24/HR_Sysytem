<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $db->prepare("SELECT leave_balance FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$balance = $stmt->fetch(PDO::FETCH_ASSOC)['leave_balance'] ?? 20;

echo json_encode(['success' => true, 'balance' => $balance]);
?>

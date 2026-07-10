<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') exit(json_encode(['success'=>false]));

$db = (new Database())->getConnection();
$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT id,first_name,last_name,salary FROM users WHERE id=? AND user_type='employee'");
$stmt->execute([$id]);
$emp = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($emp ? ['success'=>true,'data'=>$emp] : ['success'=>false]);

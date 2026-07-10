<?php
header("Content-Type: application/json");
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') exit(json_encode(['success'=>false]));

$input = json_decode(file_get_contents('php://input'), true);
$db = (new Database())->getConnection();
$stmt = $db->prepare("UPDATE users SET salary=? WHERE id=? AND user_type='employee'");
$stmt->execute([$input['salary'], $input['id']]);

echo json_encode(['success'=>true, 'message'=>'Salary updated']);

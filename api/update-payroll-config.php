<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents("php://input"), true);

$config = [
    'house_rent_percent' => $input['house_rent_percent'],
    'medical_percent' => $input['medical_percent'],
    'travel_percent' => $input['travel_percent'],
    'pf_percent' => $input['pf_percent'],
    'tax_percent' => $input['tax_percent']
];

$payroll_config = json_encode($config);

$stmt = $db->prepare("UPDATE company_settings SET payroll_config = :config WHERE id = 1");
$stmt->bindParam(':config', $payroll_config);

if($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Configuration saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save configuration']);
}
?>

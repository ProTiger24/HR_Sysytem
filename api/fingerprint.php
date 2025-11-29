<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->employee_id) && !empty($data->fingerprint_data)) {
        // Store fingerprint data
        $query = "UPDATE users SET fingerprint_data = ? WHERE employee_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $data->fingerprint_data);
        $stmt->bindParam(2, $data->employee_id);
        
        if ($stmt->execute()) {
            echo json_encode(array("message" => "Fingerprint registered successfully."));
        } else {
            echo json_encode(array("message" => "Unable to register fingerprint."));
        }
    }
}
?>
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (!empty($data->employee_id)) {
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        
        // Check existing attendance
        $check_query = "SELECT * FROM attendance WHERE employee_id = ? AND attendance_date = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(1, $data->employee_id);
        $check_stmt->bindParam(2, $current_date);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Update check-out
            $attendance = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if (empty($attendance['check_out'])) {
                $update_query = "UPDATE attendance SET check_out = ?, total_hours = TIMEDIFF(?, check_in)/10000 
                               WHERE employee_id = ? AND attendance_date = ?";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(1, $current_time);
                $update_stmt->bindParam(2, $current_time);
                $update_stmt->bindParam(3, $data->employee_id);
                $update_stmt->bindParam(4, $current_date);
                
                if ($update_stmt->execute()) {
                    echo json_encode(array("message" => "Check-out recorded successfully."));
                }
            } else {
                echo json_encode(array("message" => "Attendance already completed for today."));
            }
        } else {
            // Insert check-in
            $insert_query = "INSERT INTO attendance SET employee_id = ?, attendance_date = ?, check_in = ?, status = 'present'";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(1, $data->employee_id);
            $insert_stmt->bindParam(2, $current_date);
            $insert_stmt->bindParam(3, $current_time);
            
            if ($insert_stmt->execute()) {
                echo json_encode(array("message" => "Check-in recorded successfully."));
            }
        }
    }
} elseif ($method == 'GET') {
    // Get today's attendance
    $current_date = date('Y-m-d');
    
    $query = "SELECT u.first_name, u.last_name, a.check_in, a.check_out, a.status 
              FROM attendance a 
              JOIN users u ON a.employee_id = u.id 
              WHERE a.attendance_date = ? 
              ORDER BY a.check_in DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $current_date);
    $stmt->execute();
    
    $attendance = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attendance[] = $row;
    }
    
    echo json_encode($attendance);
}
?>
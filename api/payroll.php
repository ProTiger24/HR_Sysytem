<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Get payroll configuration from company_settings
function getPayrollConfig($db) {
    $query = "SELECT payroll_config FROM company_settings WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result && $result['payroll_config']) {
        return json_decode($result['payroll_config'], true);
    }
    return [
        'house_rent_percent' => 50,
        'medical_percent' => 10,
        'travel_percent' => 5,
        'pf_percent' => 10,
        'tax_percent' => 5
    ];
}

if ($method == 'GET') {
    $query = "SELECT p.*, u.first_name, u.last_name, u.employee_id 
              FROM payroll p 
              JOIN users u ON p.employee_id = u.id 
              ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $payrolls]);
}
elseif ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $period_start = $input['period_start'];
    $period_end = $input['period_end'];
    $payment_date = $input['payment_date'];
    $month_year = date('F Y', strtotime($period_start));
    
    $config = getPayrollConfig($db);
    
    $empQuery = "SELECT id, salary FROM users WHERE user_type = 'employee' AND status = 'active'";
    $empStmt = $db->prepare($empQuery);
    $empStmt->execute();
    $employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $successCount = 0;
    
    foreach($employees as $emp) {
        $basic_salary = $emp['salary'] ?: 30000;
        $house_rent = $basic_salary * ($config['house_rent_percent'] / 100);
        $medical = $basic_salary * ($config['medical_percent'] / 100);
        $travel = $basic_salary * ($config['travel_percent'] / 100);
        $total_allowance = $house_rent + $medical + $travel;
        $provident_fund = $basic_salary * ($config['pf_percent'] / 100);
        $tax = $basic_salary * ($config['tax_percent'] / 100);
        $total_deductions = $provident_fund + $tax;
        $net_salary = $basic_salary + $total_allowance - $total_deductions;
        
        $query = "INSERT INTO payroll (employee_id, period_start, period_end, month_year, basic_salary, house_rent, medical_allowance, travel_allowance, total_allowance, provident_fund, tax, total_deductions, net_salary, payment_date, status) 
                  VALUES (:emp_id, :period_start, :period_end, :month_year, :basic_salary, :house_rent, :medical, :travel, :total_allowance, :pf, :tax, :total_deductions, :net_salary, :payment_date, 'processed')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':emp_id', $emp['id']);
        $stmt->bindParam(':period_start', $period_start);
        $stmt->bindParam(':period_end', $period_end);
        $stmt->bindParam(':month_year', $month_year);
        $stmt->bindParam(':basic_salary', $basic_salary);
        $stmt->bindParam(':house_rent', $house_rent);
        $stmt->bindParam(':medical', $medical);
        $stmt->bindParam(':travel', $travel);
        $stmt->bindParam(':total_allowance', $total_allowance);
        $stmt->bindParam(':pf', $provident_fund);
        $stmt->bindParam(':tax', $tax);
        $stmt->bindParam(':total_deductions', $total_deductions);
        $stmt->bindParam(':net_salary', $net_salary);
        $stmt->bindParam(':payment_date', $payment_date);
        
        if($stmt->execute()) {
            $successCount++;
        }
    }
    
    echo json_encode(['success' => true, 'message' => "Payroll processed for $successCount employees"]);
}
?>

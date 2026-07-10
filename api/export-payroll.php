<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payroll_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, [
        'Employee Name',
        'Employee ID',
        'Month/Year',
        'Period Start',
        'Period End',
        'Basic Salary',
        'House Rent',
        'Medical Allowance',
        'Travel Allowance',
        'Other Allowance',
        'Total Allowance',
        'Provident Fund',
        'Tax',
        'Other Deductions',
        'Total Deductions',
        'Net Salary',
        'Payment Date',
        'Status'
    ]);

    // Check if payroll table has data
    $check = $db->query("SELECT COUNT(*) as count FROM payroll");
    $count = $check->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count == 0) {
        fputcsv($output, ['No payroll records found']);
        fclose($output);
        exit;
    }

    // Fetch payroll data
    $query = "SELECT u.first_name, u.last_name, u.employee_id, 
                     p.month_year, p.period_start, p.period_end,
                     p.basic_salary, p.house_rent, p.medical_allowance, 
                     p.travel_allowance, p.other_allowance, p.total_allowance,
                     p.provident_fund, p.tax, p.other_deductions, p.total_deductions,
                     p.net_salary, p.payment_date, p.status
              FROM payroll p 
              JOIN users u ON p.employee_id = u.id 
              ORDER BY p.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['first_name'] . ' ' . $row['last_name'],
            $row['employee_id'],
            $row['month_year'] ?? 'N/A',
            $row['period_start'] ?? 'N/A',
            $row['period_end'] ?? 'N/A',
            number_format($row['basic_salary'] ?? 0, 2),
            number_format($row['house_rent'] ?? 0, 2),
            number_format($row['medical_allowance'] ?? 0, 2),
            number_format($row['travel_allowance'] ?? 0, 2),
            number_format($row['other_allowance'] ?? 0, 2),
            number_format($row['total_allowance'] ?? 0, 2),
            number_format($row['provident_fund'] ?? 0, 2),
            number_format($row['tax'] ?? 0, 2),
            number_format($row['other_deductions'] ?? 0, 2),
            number_format($row['total_deductions'] ?? 0, 2),
            number_format($row['net_salary'] ?? 0, 2),
            $row['payment_date'] ?? 'N/A',
            $row['status'] ?? 'pending'
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    exit;
}
?>

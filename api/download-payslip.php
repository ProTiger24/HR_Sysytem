<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$id = $_GET['id'] ?? 0;
$db = (new Database())->getConnection();

$stmt = $db->prepare("SELECT p.*, u.first_name, u.last_name, u.employee_id 
                       FROM payroll p 
                       JOIN users u ON p.employee_id = u.id 
                       WHERE p.id = ?");
$stmt->execute([$id]);
$payroll = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payroll) {
    http_response_code(404);
    echo "Payslip not found";
    exit;
}

$filename = 'payslip_' . $payroll['employee_id'] . '_' . date('Ymd') . '.html';

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { background: #2c5aa0; color: white; padding: 20px; text-align: center; }
        .body { padding: 20px; border: 1px solid #ddd; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .total { font-weight: bold; font-size: 18px; border-bottom: 2px solid #2c5aa0; }
        .footer { text-align: center; padding: 15px; background: #f8f9fa; }
        .label { color: #6c757d; }
    </style>
</head>
<body>
    <div class="header">
        <h2>KormoShathi HR Solutions</h2>
        <p>Payslip</p>
    </div>
    <div class="body">
        <div style="display:flex;justify-content:space-between;">
            <div>
                <p><strong>Employee:</strong> ' . $payroll['first_name'] . ' ' . $payroll['last_name'] . '</p>
                <p><strong>Employee ID:</strong> ' . $payroll['employee_id'] . '</p>
            </div>
            <div>
                <p><strong>Payment Date:</strong> ' . ($payroll['payment_date'] ?? 'N/A') . '</p>
                <p><strong>Status:</strong> ' . ($payroll['status'] ?? 'Processed') . '</p>
            </div>
        </div>
        <hr>
        <div class="row"><span class="label">Basic Salary</span><span>৳ ' . number_format($payroll['basic_salary'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">House Rent</span><span>৳ ' . number_format($payroll['house_rent'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">Medical Allowance</span><span>৳ ' . number_format($payroll['medical_allowance'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">Travel Allowance</span><span>৳ ' . number_format($payroll['travel_allowance'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">Total Allowance</span><span>৳ ' . number_format($payroll['total_allowance'] ?? 0, 2) . '</span></div>
        <hr>
        <div class="row"><span class="label">Provident Fund</span><span>৳ ' . number_format($payroll['provident_fund'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">Tax</span><span>৳ ' . number_format($payroll['tax'] ?? 0, 2) . '</span></div>
        <div class="row"><span class="label">Total Deductions</span><span>৳ ' . number_format($payroll['total_deductions'] ?? 0, 2) . '</span></div>
        <hr>
        <div class="row total"><span>Net Salary</span><span style="color:#2c5aa0;">৳ ' . number_format($payroll['net_salary'] ?? 0, 2) . '</span></div>
    </div>
    <div class="footer">
        <p class="text-muted">This is a computer-generated payslip.</p>
        <p class="text-muted">Generated on: ' . date('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';

// Stream directly to browser as a downloadable file - no disk save needed
header("Content-Type: text/html; charset=UTF-8");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($html));
echo $html;
exit;
?>

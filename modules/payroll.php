<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; }
        .btn-primary:hover { transform: translateY(-2px); }
        .btn-sm { padding: 5px 10px; font-size: 12px; border-radius: 5px; cursor: pointer; }
        .btn-outline-primary { background: transparent; border: 1px solid #2c5aa0; color: #2c5aa0; }
        .btn-outline-primary:hover { background: #2c5aa0; color: white; }
        .btn-outline-info { background: transparent; border: 1px solid #17a2b8; color: #17a2b8; }
        .btn-outline-info:hover { background: #17a2b8; color: white; }
        .status-processed { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-pending { background: #ffc107; color: #333; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-paid { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .table th { background: #f8f9fa; }
        
        /* Payslip Modal Styles */
        .payslip-container { max-width: 600px; margin: 0 auto; }
        .payslip-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .payslip-body { padding: 20px; background: white; }
        .payslip-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .payslip-row.total { border-bottom: 2px solid #2c5aa0; font-weight: bold; font-size: 18px; }
        .payslip-footer { background: #f8f9fa; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; }
        .payslip-label { color: #6c757d; }
        .payslip-value { font-weight: 600; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-money-bill me-2"></i>Payroll Management</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <!-- Process Payroll Form -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Process Payroll</h5>
        </div>
        <div class="card-body">
            <form id="payrollForm">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pay Period Start *</label>
                        <input type="date" class="form-control" name="period_start" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Pay Period End *</label>
                        <input type="date" class="form-control" name="period_end" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" name="payment_date" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Process Payroll</button>
            </form>
        </div>
    </div>

    <!-- Payroll Records -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Payroll Records</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Pay Period</th>
                            <th>Basic Salary</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payrollTableBody">
                        <tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#2c5aa0,#1e3d72);color:white;">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Payslip</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="payslipContent">
                <div class="payslip-container">
                    <div class="payslip-header">
                        <h3><i class="fas fa-building me-2"></i>KormoShathi HR Solutions</h3>
                        <p>Payslip for the period <span id="psPeriod"></span></p>
                    </div>
                    <div class="payslip-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><strong>Employee:</strong> <span id="psEmployee"></span></p>
                                <p><strong>Employee ID:</strong> <span id="psEmployeeId"></span></p>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong>Payment Date:</strong> <span id="psPaymentDate"></span></p>
                                <p><strong>Status:</strong> <span id="psStatus" class="badge bg-success">Paid</span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="payslip-row"><span class="payslip-label">Basic Salary</span><span class="payslip-value" id="psBasic">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">House Rent Allowance</span><span class="payslip-value" id="psHouseRent">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">Medical Allowance</span><span class="payslip-value" id="psMedical">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">Travel Allowance</span><span class="payslip-value" id="psTravel">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">Total Allowances</span><span class="payslip-value" id="psTotalAllowance">৳ 0</span></div>
                        <hr>
                        <div class="payslip-row"><span class="payslip-label">Provident Fund</span><span class="payslip-value" id="psPF">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">Tax</span><span class="payslip-value" id="psTax">৳ 0</span></div>
                        <div class="payslip-row"><span class="payslip-label">Total Deductions</span><span class="payslip-value" id="psTotalDeductions">৳ 0</span></div>
                        <hr>
                        <div class="payslip-row total"><span>Net Salary</span><span id="psNetSalary" style="color:#2c5aa0;font-size:24px;">৳ 0</span></div>
                    </div>
                    <div class="payslip-footer">
                        <p class="text-muted mb-0">This is a computer-generated payslip. No signature required.</p>
                        <p class="text-muted mb-0">Generated on: <span id="psGeneratedDate"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="downloadPayslipBtn"><i class="fas fa-download me-2"></i>Download PDF</button>
            </div>
        </div>
    </div>
</div>

<script>
// Load payroll records
async function loadPayroll() {
    try {
        const response = await fetch('../api/payroll.php');
        const result = await response.json();
        
        if(result.success && result.data) {
            updatePayrollTable(result.data);
        } else {
            document.getElementById('payrollTableBody').innerHTML = '<tr><td colspan="8" class="text-center py-4">No payroll records found</td></tr>';
        }
    } catch(e) {
        console.error('Error:', e);
        document.getElementById('payrollTableBody').innerHTML = '<tr><td colspan="8" class="text-center py-4">Error loading payroll</td></tr>';
    }
}

function updatePayrollTable(payrolls) {
    const tbody = document.getElementById('payrollTableBody');
    if(!payrolls || !payrolls.length) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4">No payroll records found</td></tr>';
        return;
    }

    tbody.innerHTML = payrolls.map(p => `
        <tr>
            <td>
                <strong>${p.first_name || ''} ${p.last_name || ''}</strong>
                <br><small class="text-muted">${p.employee_id || ''}</small>
            </td>
            <td>${p.period_start || 'N/A'} to ${p.period_end || 'N/A'}</td>
            <td>৳ ${parseFloat(p.basic_salary || 0).toLocaleString()}</td>
            <td>৳ ${parseFloat(p.total_allowance || 0).toLocaleString()}</td>
            <td>৳ ${parseFloat(p.total_deductions || 0).toLocaleString()}</td>
            <td><strong>৳ ${parseFloat(p.net_salary || 0).toLocaleString()}</strong></td>
            <td><span class="status-${p.status}">${p.status || 'pending'}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewPayslip(${p.id})">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="downloadPayslip(${p.id})">
                    <i class="fas fa-download"></i> Download
                </button>
            </td>
        </tr>
    `).join('');
}

// Process payroll form submit
document.getElementById('payrollForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const payrollData = {
        period_start: formData.get('period_start'),
        period_end: formData.get('period_end'),
        payment_date: formData.get('payment_date')
    };
    
    if(!payrollData.period_start || !payrollData.period_end || !payrollData.payment_date) {
        alert('Please fill all fields');
        return;
    }
    
    try {
        const response = await fetch('../api/payroll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payrollData)
        });
        const result = await response.json();
        alert(result.message);
        if(result.success) {
            this.reset();
            loadPayroll();
        }
    } catch(error) {
        alert('Error processing payroll');
    }
});

// View Payslip
async function viewPayslip(id) {
    try {
        const response = await fetch(`../api/get-payslip.php?id=${id}`);
        const data = await response.json();
        
        if(data.success) {
            const p = data.data;
            document.getElementById('psEmployee').textContent = p.first_name + ' ' + p.last_name;
            document.getElementById('psEmployeeId').textContent = p.employee_id;
            document.getElementById('psPeriod').textContent = p.period_start + ' to ' + p.period_end;
            document.getElementById('psPaymentDate').textContent = p.payment_date || 'N/A';
            document.getElementById('psStatus').textContent = p.status || 'Paid';
            document.getElementById('psStatus').className = 'badge bg-' + (p.status === 'paid' ? 'success' : 'warning');
            document.getElementById('psBasic').textContent = '৳ ' + parseFloat(p.basic_salary || 0).toLocaleString();
            document.getElementById('psHouseRent').textContent = '৳ ' + parseFloat(p.house_rent || 0).toLocaleString();
            document.getElementById('psMedical').textContent = '৳ ' + parseFloat(p.medical_allowance || 0).toLocaleString();
            document.getElementById('psTravel').textContent = '৳ ' + parseFloat(p.travel_allowance || 0).toLocaleString();
            document.getElementById('psTotalAllowance').textContent = '৳ ' + parseFloat(p.total_allowance || 0).toLocaleString();
            document.getElementById('psPF').textContent = '৳ ' + parseFloat(p.provident_fund || 0).toLocaleString();
            document.getElementById('psTax').textContent = '৳ ' + parseFloat(p.tax || 0).toLocaleString();
            document.getElementById('psTotalDeductions').textContent = '৳ ' + parseFloat(p.total_deductions || 0).toLocaleString();
            document.getElementById('psNetSalary').textContent = '৳ ' + parseFloat(p.net_salary || 0).toLocaleString();
            document.getElementById('psGeneratedDate').textContent = new Date().toLocaleDateString();
            
            document.getElementById('downloadPayslipBtn').onclick = function() {
                downloadPayslip(id);
            };
            
            const modal = new bootstrap.Modal(document.getElementById('payslipModal'));
            modal.show();
        } else {
            alert('Payslip not found');
        }
    } catch(e) {
        alert('Error loading payslip');
    }
}

// Download Payslip
async function downloadPayslip(id) {
    window.open(`../api/download-payslip.php?id=${id}`, '_blank');
}

loadPayroll();
setInterval(loadPayroll, 10000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

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
    <title>Employee Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c5aa0; }
        .stat-label { color: #6c757d; margin-bottom: 0; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-outline-info { background: transparent; border: 1px solid #0dcaf0; color: #0dcaf0; border-radius: 5px; padding: 5px 10px; transition: 0.3s; }
        .btn-outline-info:hover { background: #0dcaf0; color: white; }
        .status-active { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-inactive { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-probation { background: #ffc107; color: #333; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .user-avatar { width: 35px; height: 35px; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 10px; font-size: 14px; }
        .table { margin-bottom: 0; }
        .table th { background: #f8f9fa; font-weight: 600; border-bottom: 2px solid #dee2e6; white-space: nowrap; }
        .table td { vertical-align: middle; }
        .btn-outline-danger { background: transparent; border: 1px solid #dc3545; color: #dc3545; border-radius: 5px; padding: 5px 10px; transition: 0.3s; }
        .btn-outline-danger:hover { background: #dc3545; color: white; }
        .btn-outline-warning { background: transparent; border: 1px solid #ffc107; color: #ffc107; border-radius: 5px; padding: 5px 10px; transition: 0.3s; }
        .btn-outline-warning:hover { background: #ffc107; color: white; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #ddd; padding: 8px 12px; }
        .form-label { font-weight: 600; margin-bottom: 5px; color: #495057; }
        .row { display: flex; flex-wrap: wrap; gap: 20px; }
        .col-md-3 { flex: 0 0 calc(25% - 20px); }
        .col-md-4 { flex: 0 0 calc(33.33% - 20px); }
        .col-md-2 { flex: 0 0 calc(16.66% - 20px); }
        @media (max-width: 768px) { .col-md-3, .col-md-4, .col-md-2 { flex: 0 0 100%; } }
        .edit-salary-btn { background: none; border: none; color: #ffc107; cursor: pointer; margin-left: 8px; transition: 0.3s; }
        .edit-salary-btn:hover { color: #e0a800; transform: scale(1.1); }
        .table-responsive { overflow-x: auto; }
        .action-btns { display: flex; gap: 5px; flex-wrap: wrap; }
        .stat-icon { font-size: 28px; color: #2c5aa0; margin-bottom: 5px; }
        .stat-card .stat-number { font-size: 28px; }
        .stat-card .stat-label { font-size: 13px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-users me-2"></i>Employee Management</h4>
    <div>
        <a href="add-employee.php" class="me-2"><i class="fas fa-user-plus me-1"></i>Add Employee</a>
        <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

<div class="container">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number" id="totalEmployees">0</div>
                <p class="stat-label">Total Employees</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-check" style="color: #28a745;"></i></div>
                <div class="stat-number" id="activeEmployees">0</div>
                <p class="stat-label">Active Employees</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-day" style="color: #ffc107;"></i></div>
                <div class="stat-number" id="onLeaveCount">0</div>
                <p class="stat-label">On Leave Today</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave" style="color: #28a745;"></i></div>
                <div class="stat-number" id="totalSalary">0</div>
                <p class="stat-label">Total Salary</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-building me-1"></i>Department</label>
                    <select id="deptFilter" class="form-select">
                        <option value="">All</option>
                        <option value="IT">IT</option>
                        <option value="HR">HR</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Sales">Sales</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-circle me-1"></i>Status</label>
                    <select id="statusFilter" class="form-select">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="probation">Probation</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><i class="fas fa-search me-1"></i>Search</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by name, email or ID...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button id="resetBtn" class="btn-primary w-100"><i class="fas fa-sync me-2"></i>Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Employees</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Dept</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="empTbody">
                        <tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const deptFilter = document.getElementById('deptFilter');
const statusFilter = document.getElementById('statusFilter');
const searchInput = document.getElementById('searchInput');
const resetBtn = document.getElementById('resetBtn');
const empTbody = document.getElementById('empTbody');
const totalEmployees = document.getElementById('totalEmployees');
const activeEmployees = document.getElementById('activeEmployees');
const onLeaveCount = document.getElementById('onLeaveCount');
const totalSalary = document.getElementById('totalSalary');

function loadEmployees() {
    let url = '../api/employees.php?';
    if (deptFilter.value) url += 'department=' + encodeURIComponent(deptFilter.value) + '&';
    if (statusFilter.value) url += 'status=' + encodeURIComponent(statusFilter.value) + '&';
    if (searchInput.value) url += 'search=' + encodeURIComponent(searchInput.value) + '&';
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                let html = '', totalSal = 0;
                let active = 0, onLeave = 0;
                
                data.data.forEach(e => {
                    let salary = parseFloat(e.salary) || 0;
                    totalSal += salary;
                    if (e.status === 'active') active++;
                    
                    let statusClass = 'status-active';
                    let statusText = e.status || 'active';
                    if (statusText === 'inactive') statusClass = 'status-inactive';
                    else if (statusText === 'probation') statusClass = 'status-probation';
                    
                    html += `<tr>
                        <td><strong>${e.employee_id || '-'}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar">${(e.first_name?.charAt(0)||'')}${(e.last_name?.charAt(0)||'')}</div>
                                ${e.first_name || ''} ${e.last_name || ''}
                            </div>
                        </td>
                        <td>${e.department || '-'}</td>
                        <td>${e.position || '-'}</td>
                        <td><small>${e.email || '-'}</small></td>
                        <td>${e.phone || '-'}</td>
                        <td>
                            <strong>৳ ${salary.toLocaleString()}</strong>
                            <button class="edit-salary-btn" onclick="editSalary(${e.id},'${e.first_name} ${e.last_name}',${salary})" title="Edit Salary">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                        <td><span class="${statusClass}">${statusText}</span></td>
                        <td>${e.join_date || '-'}</td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-outline-info btn-sm" onclick="window.location.href='employee-id-card.php?id=${e.id}'" title="ID Card">
                                    <i class="fas fa-id-card"></i>
                                </button>
                                <button class="btn-outline-danger btn-sm" onclick="deleteEmp(${e.id})" title="Delete Employee">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
                
                empTbody.innerHTML = html;
                totalEmployees.innerText = data.data.length;
                activeEmployees.innerText = active;
                
                // Get on leave count for today
                fetch('../api/leave.php?action=today_on_leave')
                    .then(r => r.json())
                    .then(leaveData => {
                        if (leaveData.success) {
                            onLeaveCount.innerText = leaveData.count || 0;
                        }
                    })
                    .catch(() => onLeaveCount.innerText = '0');
                
                totalSalary.innerText = '৳ ' + totalSal.toLocaleString();
            } else {
                empTbody.innerHTML = '<tr><td colspan="10" class="text-center py-4">No employees found</td></tr>';
            }
        })
        .catch(() => {
            empTbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error loading data</td></tr>';
        });
}

function deleteEmp(id) {
    if (!confirm('⚠️ Are you sure you want to delete this employee permanently? This action cannot be undone!')) return;
    
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('../api/employees.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) loadEmployees();
    })
    .catch(() => {
        alert('Error deleting employee');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function editSalary(id, name, current) {
    let newSal = prompt(`Enter new salary for ${name}:`, current);
    if (newSal !== null && parseFloat(newSal) > 0) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch('../api/update-salary.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, salary: parseFloat(newSal) })
        })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.success) loadEmployees();
        })
        .catch(() => {
            alert('Error updating salary');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

// Event listeners
deptFilter.onchange = loadEmployees;
statusFilter.onchange = loadEmployees;
searchInput.onkeyup = loadEmployees;
resetBtn.onclick = () => {
    deptFilter.value = '';
    statusFilter.value = '';
    searchInput.value = '';
    loadEmployees();
};

// Load on page load
loadEmployees();

// Auto refresh every 30 seconds
setInterval(loadEmployees, 30000);
</script>
</body>
</html>

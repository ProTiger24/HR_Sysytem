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
    <title>Attendance Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 20px; }
        
        .stat-card { background: white; border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-number { font-size: 36px; font-weight: bold; color: #2c5aa0; }
        .stat-label { color: #6c757d; font-size: 14px; }
        
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        
        .status-present { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-absent { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-late { background: #ffc107; color: #333; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .status-half-day { background: #fd7e14; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        
        .table th { background: #f8f9fa; font-weight: 600; border-bottom: 2px solid #dee2e6; }
        .table td { vertical-align: middle; }
        .user-avatar { width: 35px; height: 35px; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 10px; }
        
        .row { display: flex; flex-wrap: wrap; gap: 20px; }
        .col-md-3 { flex: 0 0 calc(25% - 20px); }
        .col-md-4 { flex: 0 0 calc(33.33% - 20px); }
        @media (max-width: 768px) { .col-md-3, .col-md-4 { flex: 0 0 100%; } }
        
        .date-filter { max-width: 250px; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-fingerprint me-2"></i>Attendance Management</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="stat-card"><div class="stat-number" id="totalPresent">0</div><div class="stat-label">Present Today</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-number" id="totalAbsent">0</div><div class="stat-label">Absent Today</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-number" id="totalLate">0</div><div class="stat-label">Late Today</div></div></div>
        <div class="col-md-3"><div class="stat-card"><div class="stat-number" id="totalEmployees">0</div><div class="stat-label">Total Employees</div></div></div>
    </div>

    <!-- Today's Attendance Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Today's Attendance</h5>
            <input type="date" id="attendanceDate" class="form-control date-filter" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendanceBody">
                        <tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary"></div> Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function loadAttendance(date) {
    const url = date ? `../api/attendance.php?date=${date}` : '../api/attendance.php';
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if(data.success && data.data) {
                let html = '';
                data.data.forEach(a => {
                    const statusClass = a.status;
                    html += `<tr>
                        <td><div class="d-flex align-items-center"><div class="user-avatar">${(a.first_name?.charAt(0)||'')}${(a.last_name?.charAt(0)||'')}</div>${a.first_name||''} ${a.last_name||''}</div></td>
                        <td>${a.check_in ? new Date('1970-01-01T' + a.check_in).toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'}) : '-'}</td>
                        <td>${a.check_out ? new Date('1970-01-01T' + a.check_out).toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'}) : '-'}</td>
                        <td><span class="status-${statusClass}">${statusClass}</span></td>
                    </tr>`;
                });
                document.getElementById('attendanceBody').innerHTML = html;
                
                if(data.counts) {
                    document.getElementById('totalPresent').innerText = data.counts.present || 0;
                    document.getElementById('totalAbsent').innerText = data.counts.absent || 0;
                    document.getElementById('totalLate').innerText = data.counts.late || 0;
                    document.getElementById('totalEmployees').innerText = data.counts.total || 0;
                }
            } else {
                document.getElementById('attendanceBody').innerHTML = '<tr><td colspan="4" class="text-center py-4">No attendance records found</td></tr>';
            }
        })
        .catch(e => {
            console.error('Error:', e);
            document.getElementById('attendanceBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Error loading data</td></tr>';
        });
}

document.getElementById('attendanceDate').addEventListener('change', function() {
    loadAttendance(this.value);
});

loadAttendance(document.getElementById('attendanceDate').value);
setInterval(() => loadAttendance(document.getElementById('attendanceDate').value), 30000);
</script>
</body>
</html>

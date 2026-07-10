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
    <title>Reports - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        .stat-box { text-align: center; padding: 15px; border-radius: 10px; color: white; margin-bottom: 10px; }
        .btn-export { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 8px; margin: 5px; transition: 0.3s; }
        .btn-export:hover { transform: scale(1.05); }
        .chart-container { height: 250px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-chart-bar me-2"></i>HR Reports & Analytics</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Employee Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="employeeChart"></canvas>
                    </div>
                    <div class="row mt-3 text-center">
                        <div class="col-4"><div class="stat-box bg-primary"><h3 id="totalEmp">0</h3><small>Total</small></div></div>
                        <div class="col-4"><div class="stat-box bg-success"><h3 id="activeEmp">0</h3><small>Active</small></div></div>
                        <div class="col-4"><div class="stat-box bg-danger"><h3 id="inactiveEmp">0</h3><small>Inactive</small></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Attendance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                    <div class="row mt-3 text-center">
                        <div class="col-4"><div class="stat-box bg-success"><h3 id="presentToday">0</h3><small>Present</small></div></div>
                        <div class="col-4"><div class="stat-box bg-danger"><h3 id="absentToday">0</h3><small>Absent</small></div></div>
                        <div class="col-4"><div class="stat-box bg-warning"><h3 id="lateToday">0</h3><small>Late</small></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Department-wise Employees</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-umbrella-beach me-2"></i>Leave Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="leaveChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export Reports</h5>
        </div>
        <div class="card-body text-center">
            <button class="btn-export" onclick="exportReport('employees')"><i class="fas fa-file-excel me-2"></i>Employee Report</button>
            <button class="btn-export" onclick="exportReport('attendance')"><i class="fas fa-file-excel me-2"></i>Attendance Report</button>
            <button class="btn-export" onclick="exportReport('payroll')"><i class="fas fa-file-excel me-2"></i>Payroll Report</button>
            <button class="btn-export" onclick="exportReport('leave')"><i class="fas fa-file-excel me-2"></i>Leave Report</button>
        </div>
    </div>
</div>

<script>
let employeeChart, attendanceChart, deptChart, leaveChart;

async function loadReports() {
    try {
        const response = await fetch('../api/reports-data.php');
        const data = await response.json();
        
        if(data.success) {
            // Employee stats
            document.getElementById('totalEmp').innerText = data.employees?.total || 0;
            document.getElementById('activeEmp').innerText = data.employees?.active || 0;
            document.getElementById('inactiveEmp').innerText = data.employees?.inactive || 0;
            
            // Attendance stats
            document.getElementById('presentToday').innerText = data.attendance?.present || 0;
            document.getElementById('absentToday').innerText = data.attendance?.absent || 0;
            document.getElementById('lateToday').innerText = data.attendance?.late || 0;
            
            // Employee Chart
            if(employeeChart) employeeChart.destroy();
            employeeChart = new Chart(document.getElementById('employeeChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Inactive'],
                    datasets: [{
                        data: [data.employees?.active || 0, data.employees?.inactive || 0],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            
            // Attendance Chart
            if(attendanceChart) attendanceChart.destroy();
            attendanceChart = new Chart(document.getElementById('attendanceChart'), {
                type: 'bar',
                data: {
                    labels: ['Present', 'Absent', 'Late'],
                    datasets: [{
                        label: 'Count',
                        data: [data.attendance?.present || 0, data.attendance?.absent || 0, data.attendance?.late || 0],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            
            // Department Chart
            if(deptChart) deptChart.destroy();
            if(data.departments && Object.keys(data.departments).length > 0) {
                deptChart = new Chart(document.getElementById('deptChart'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.departments),
                        datasets: [{
                            label: 'Employees',
                            data: Object.values(data.departments),
                            backgroundColor: '#2c5aa0'
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
            
            // Leave Chart
            if(leaveChart) leaveChart.destroy();
            if(data.leaves && Object.keys(data.leaves).length > 0) {
                leaveChart = new Chart(document.getElementById('leaveChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(data.leaves),
                        datasets: [{
                            data: Object.values(data.leaves),
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        }
    } catch(e) {
        console.error('Error loading reports:', e);
    }
}

function exportReport(type) {
    window.open(`../api/export-${type}.php`, '_blank');
}

loadReports();
setInterval(loadReports, 30000);
</script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'hr') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <img src="assets/images/hr_logo.png" alt="KormoShathi" height="40" class="me-2">
                <span class="brand-text">KormoShathi</span>
                <small class="d-block text-muted">HR Portal</small>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a class="nav-link active" href="hr-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/employees.php">
                        <i class="fas fa-users"></i>Employee Management
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/attendance.php">
                        <i class="fas fa-fingerprint"></i>Attendance
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/leave.php">
                        <i class="fas fa-calendar-alt"></i>Leave Management
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/payroll.php">
                        <i class="fas fa-money-bill"></i>Payroll
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/performance.php">
                        <i class="fas fa-chart-line"></i>Performance
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/recruitment.php">
                        <i class="fas fa-briefcase"></i>Recruitment
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="modules/reports.php">
                        <i class="fas fa-chart-bar"></i>Reports
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-title">
                    <h4 class="mb-0">HR Dashboard</h4>
                    <small class="text-muted">Welcome back, <?php echo $_SESSION['first_name']; ?>!</small>
                </div>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></div>
                            <div class="user-role">HR Manager</div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card primary">
                            <div class="stat-number" id="totalEmployees">47</div>
                            <div class="stat-label">Total Employees</div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card success">
                            <div class="stat-number" id="presentToday">42</div>
                            <div class="stat-label">Present Today</div>
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card warning">
                            <div class="stat-number" id="onLeave">5</div>
                            <div class="stat-label">On Leave</div>
                            <div class="stat-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card info">
                            <div class="stat-number" id="pendingLeaves">3</div>
                            <div class="stat-label">Pending Leaves</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Quick Actions -->
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Attendance Overview -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Attendance Overview</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Activities</h5>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline">
                                    <div class="activity-item">
                                        <div class="activity-icon success">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">New employee registered</div>
                                            <div class="activity-desc">John Doe joined as Software Engineer</div>
                                            <div class="activity-time">2 hours ago</div>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon warning">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">Leave application submitted</div>
                                            <div class="activity-desc">Jane Smith applied for casual leave</div>
                                            <div class="activity-time">4 hours ago</div>
                                        </div>
                                    </div>
                                    <div class="activity-item">
                                        <div class="activity-icon info">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">Payroll processed</div>
                                            <div class="activity-desc">December payroll completed</div>
                                            <div class="activity-time">1 day ago</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <a href="modules/employees.php?action=add" class="quick-action-btn">
                                        <i class="fas fa-user-plus"></i>
                                        <span>Add Employee</span>
                                    </a>
                                    <a href="modules/attendance.php" class="quick-action-btn">
                                        <i class="fas fa-fingerprint"></i>
                                        <span>View Attendance</span>
                                    </a>
                                    <a href="modules/leave.php" class="quick-action-btn">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Manage Leaves</span>
                                    </a>
                                    <a href="modules/payroll.php" class="quick-action-btn">
                                        <i class="fas fa-calculator"></i>
                                        <span>Process Payroll</span>
                                    </a>
                                    <a href="modules/recruitment.php" class="quick-action-btn">
                                        <i class="fas fa-briefcase"></i>
                                        <span>Post Job</span>
                                    </a>
                                    <a href="modules/reports.php" class="quick-action-btn">
                                        <i class="fas fa-chart-bar"></i>
                                        <span>Generate Report</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Approvals -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pending Approvals</h5>
                            </div>
                            <div class="card-body">
                                <div class="pending-approvals">
                                    <div class="approval-item">
                                        <div class="approval-info">
                                            <div class="approval-title">Leave Application</div>
                                            <div class="approval-desc">Michael Brown - 3 days</div>
                                        </div>
                                        <div class="approval-actions">
                                            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="approval-item">
                                        <div class="approval-info">
                                            <div class="approval-title">Expense Claim</div>
                                            <div class="approval-desc">Sarah Wilson - $250</div>
                                        </div>
                                        <div class="approval-actions">
                                            <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/app.js"></script>
    <script>
        // Initialize attendance chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Present Employees',
                    data: [42, 45, 40, 43, 41, 15],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 50
                    }
                }
            }
        });

        // Load dashboard data
        async function loadHRDashboard() {
            try {
                const stats = await hrSystem.apiCall('dashboard/hr.php');
                if (stats) {
                    document.getElementById('totalEmployees').textContent = stats.totalEmployees || 47;
                    document.getElementById('presentToday').textContent = stats.presentToday || 42;
                    document.getElementById('onLeave').textContent = stats.onLeave || 5;
                    document.getElementById('pendingLeaves').textContent = stats.pendingLeaves || 3;
                }
            } catch (error) {
                console.error('Error loading HR dashboard:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadHRDashboard();
        });
    </script>
    <script src="js/app.js"></script>

</body>
</html>
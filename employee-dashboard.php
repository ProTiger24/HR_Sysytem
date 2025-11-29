<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="employee-dashboard">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="employee-dashboard.php">
                <i class="fas fa-user-tie me-2"></i>Employee Portal
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-4">
        <div class="row">
            <!-- Employee Info Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="user-avatar-large mb-3">
                            <?php 
                                $initials = substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1);
                                echo $initials;
                            ?>
                        </div>
                        <h4><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h4>
                        <p class="text-muted"><?php echo $_SESSION['position'] ?? 'Employee'; ?></p>
                        <div class="employee-info">
                            <p><strong>ID:</strong> <?php echo $_SESSION['employee_id']; ?></p>
                            <p><strong>Department:</strong> <?php echo $_SESSION['department'] ?? 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 id="attendanceStatus">-</h3>
                                        <p>Today's Status</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-check fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h3 id="leaveBalance">-</h3>
                                        <p>Leave Balance</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-umbrella-beach fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-fingerprint me-2"></i>Attendance</h5>
                    </div>
                    <div class="card-body text-center">
                        <div id="attendanceResult" class="mb-3"></div>
                        <button class="btn btn-success btn-lg me-3" onclick="markAttendance('check_in')">
                            <i class="fas fa-sign-in-alt me-2"></i>Check In
                        </button>
                        <button class="btn btn-warning btn-lg" onclick="markAttendance('check_out')">
                            <i class="fas fa-sign-out-alt me-2"></i>Check Out
                        </button>
                        
                        <div class="mt-4">
                            <div class="fingerprint-scanner" id="fingerprintScanner" onclick="markAttendanceWithFingerprint()">
                                <i class="fas fa-fingerprint"></i>
                                <p class="mt-2">Tap for Fingerprint</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div id="recentActivity">
                            <p class="text-muted">Loading recent activity...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        // Load employee dashboard data
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployeeDashboard();
        });

        async function loadEmployeeDashboard() {
            try {
                // Load attendance status
                const attendanceResponse = await fetch('api/attendance.php?action=today_status');
                const attendanceData = await attendanceResponse.json();
                
                if (attendanceData.success) {
                    document.getElementById('attendanceStatus').textContent = attendanceData.status;
                }

                // Load leave balance
                const leaveResponse = await fetch('api/leaves.php?action=balance');
                const leaveData = await leaveResponse.json();
                
                if (leaveData.success) {
                    document.getElementById('leaveBalance').textContent = leaveData.balance;
                }

                // Load recent activity
                loadRecentActivity();
                
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        async function markAttendance(type) {
            try {
                const response = await fetch('api/attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('hr_token')
                    },
                    body: JSON.stringify({
                        action: 'mark',
                        type: type
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    hrSystem.showNotification(result.message, 'success');
                    loadEmployeeDashboard();
                } else {
                    hrSystem.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error marking attendance:', error);
                hrSystem.showNotification('Failed to mark attendance', 'error');
            }
        }

        async function markAttendanceWithFingerprint() {
            const scanner = document.getElementById('fingerprintScanner');
            scanner.classList.add('scanning');
            
            try {
                const verification = await hrSystem.verifyFingerprint();
                
                if (verification && verification.success) {
                    await markAttendance('check_in');
                } else {
                    hrSystem.showNotification('Fingerprint not recognized', 'error');
                }
            } catch (error) {
                hrSystem.showNotification('Fingerprint login failed', 'error');
            } finally {
                scanner.classList.remove('scanning');
            }
        }

        async function loadRecentActivity() {
            try {
                const response = await fetch('api/employee.php?action=recent_activity');
                const data = await response.json();
                
                const container = document.getElementById('recentActivity');
                
                if (data.success && data.activities.length > 0) {
                    container.innerHTML = data.activities.map(activity => `
                        <div class="activity-item mb-2 p-2 border rounded">
                            <div class="d-flex justify-content-between">
                                <span>${activity.description}</span>
                                <small class="text-muted">${activity.time}</small>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">No recent activity</p>';
                }
            } catch (error) {
                console.error('Error loading recent activity:', error);
            }
        }
    </script>
    <script src="../js/app.js"></script>

</body>
</html>
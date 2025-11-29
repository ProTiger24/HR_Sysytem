<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <img src="../assets/images/logo.png" alt="KormoShathi" height="40" class="me-2">
                <span class="brand-text">KormoShathi</span>
                <small class="d-block text-muted"><?php echo $_SESSION['user_type'] === 'hr' ? 'HR Portal' : 'Employee Portal'; ?></small>
            </div>
            <nav class="sidebar-nav">
                <?php if ($_SESSION['user_type'] === 'hr'): ?>
                    <div class="nav-item">
                        <a class="nav-link" href="../hr-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link" href="employees.php">
                            <i class="fas fa-users"></i>Employee Management
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link active" href="attendance.php">
                            <i class="fas fa-fingerprint"></i>Attendance
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-item">
                        <a class="nav-link" href="../employee-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </div>
                    <div class="nav-item">
                        <a class="nav-link active" href="attendance.php">
                            <i class="fas fa-fingerprint"></i>Attendance
                        </a>
                    </div>
                <?php endif; ?>
                <!-- Other menu items -->
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-title">
                    <h4 class="mb-0">Attendance Management</h4>
                    <small class="text-muted">Fingerprint-based attendance system</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Fingerprint Scanner</h5>
                            </div>
                            <div class="card-body text-center">
                                <div class="fingerprint-scanner my-4" id="fingerprintScanner">
                                    <i class="fas fa-fingerprint fa-5x text-primary"></i>
                                </div>
                                <button class="btn btn-success btn-lg" onclick="markAttendance()">
                                    <i class="fas fa-hand-point-up me-2"></i>Scan Fingerprint
                                </button>
                                <div id="attendanceResult" class="mt-3"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Today's Attendance</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Check-in</th>
                                                <th>Check-out</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody id="todayAttendance">
                                            <!-- Attendance data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/app.js"></script>
</body>
</html>
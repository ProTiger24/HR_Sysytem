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
    <title>Reports - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <!-- Similar sidebar structure -->

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-title">
                    <h4 class="mb-0">Reports & Analytics</h4>
                    <small class="text-muted">Comprehensive HR reports and insights</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Report Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Generate Report</h5>
                            </div>
                            <div class="card-body">
                                <form id="reportFilterForm">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Report Type</label>
                                                <select class="form-select" name="report_type" required>
                                                    <option value="">Select Report Type</option>
                                                    <option value="attendance">Attendance Report</option>
                                                    <option value="leave">Leave Report</option>
                                                    <option value="payroll">Payroll Report</option>
                                                    <option value="performance">Performance Report</option>
                                                    <?php if ($_SESSION['user_type'] === 'hr'): ?>
                                                    <option value="employee">Employee Report</option>
                                                    <option value="recruitment">Recruitment Report</option>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" class="form-control" name="start_date">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" class="form-control" name="end_date">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Results -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Report Results</h5>
                            </div>
                            <div class="card-body">
                                <div id="reportResults">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                        <p>Select report type and generate to view results</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/app.js"></script>
</body>
</html>
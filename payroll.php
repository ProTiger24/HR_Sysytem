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
    <title>Payroll - KormoShathi</title>
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
                    <h4 class="mb-0">Payroll Management</h4>
                    <small class="text-muted">Salary processing and management</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <?php if ($_SESSION['user_type'] === 'hr'): ?>
                <!-- HR Payroll View -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Process Payroll</h5>
                            </div>
                            <div class="card-body">
                                <form id="payrollForm">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Pay Period Start</label>
                                                <input type="date" class="form-control" name="period_start" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Pay Period End</label>
                                                <input type="date" class="form-control" name="period_end" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Payment Date</label>
                                                <input type="date" class="form-control" name="payment_date" required>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Process Payroll</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <?php echo $_SESSION['user_type'] === 'hr' ? 'All Employee Payroll' : 'My Payslips'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <?php if ($_SESSION['user_type'] === 'hr'): ?>
                                                    <th>Employee</th>
                                                <?php endif; ?>
                                                <th>Pay Period</th>
                                                <th>Basic Salary</th>
                                                <th>Allowances</th>
                                                <th>Deductions</th>
                                                <th>Net Salary</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payrollData">
                                            <!-- Payroll data will be loaded here -->
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
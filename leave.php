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
    <title>Leave Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <!-- Similar sidebar structure as previous modules -->

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-title">
                    <h4 class="mb-0">Leave Management</h4>
                    <small class="text-muted">Manage employee leaves and approvals</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <?php if ($_SESSION['user_type'] === 'employee'): ?>
                <!-- Employee Leave View -->
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Apply for Leave</h5>
                            </div>
                            <div class="card-body">
                                <form id="leaveApplicationForm">
                                    <div class="mb-3">
                                        <label class="form-label">Leave Type</label>
                                        <select class="form-select" name="leave_type" required>
                                            <option value="">Select Leave Type</option>
                                            <option value="casual">Casual Leave</option>
                                            <option value="sick">Sick Leave</option>
                                            <option value="earned">Earned Leave</option>
                                            <option value="maternity">Maternity Leave</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Start Date</label>
                                                <input type="date" class="form-control" name="start_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">End Date</label>
                                                <input type="date" class="form-control" name="end_date" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reason</label>
                                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">My Leave Applications</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="myLeaveApplications">
                                            <!-- Leave applications will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- HR Leave Management View -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Pending Leave Approvals</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Leave Type</th>
                                                <th>Period</th>
                                                <th>Duration</th>
                                                <th>Reason</th>
                                                <th>Applied On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pendingLeaves">
                                            <!-- Pending leaves will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/app.js"></script>
</body>
</html>
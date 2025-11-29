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
    <title>Performance Management - KormoShathi</title>
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
                    <h4 class="mb-0">Performance Management</h4>
                    <small class="text-muted">Employee performance tracking and reviews</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <?php if ($_SESSION['user_type'] === 'hr'): ?>
                <!-- HR Performance View -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Schedule Performance Review</h5>
                            </div>
                            <div class="card-body">
                                <form id="scheduleReviewForm">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Employee</label>
                                                <select class="form-select" name="employee_id" required>
                                                    <option value="">Select Employee</option>
                                                    <!-- Employee options will be loaded dynamically -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Review Date</label>
                                                <input type="date" class="form-control" name="review_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Reviewer</label>
                                                <input type="text" class="form-control" value="<?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Schedule Review</button>
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
                                    <?php echo $_SESSION['user_type'] === 'hr' ? 'All Performance Reviews' : 'My Performance Reviews'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="performanceReviews">
                                    <!-- Performance reviews will be loaded here -->
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
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'hr') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - KormoShathi</title>
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
                    <h4 class="mb-0">Recruitment Management</h4>
                    <small class="text-muted">Hiring process and job postings</small>
                </div>
                <div class="user-menu">
                    <!-- User menu code -->
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Post New Job</h5>
                            </div>
                            <div class="card-body">
                                <form id="jobPostingForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Job Title</label>
                                                <input type="text" class="form-control" name="title" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department</label>
                                                <select class="form-select" name="department" required>
                                                    <option value="">Select Department</option>
                                                    <option value="IT">IT</option>
                                                    <option value="HR">HR</option>
                                                    <option value="Finance">Finance</option>
                                                    <option value="Marketing">Marketing</option>
                                                    <option value="Sales">Sales</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Job Description</label>
                                        <textarea class="form-control" name="description" rows="4" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Requirements</label>
                                        <textarea class="form-control" name="requirements" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Post Job</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Active Job Postings</h5>
                            </div>
                            <div class="card-body">
                                <div id="jobPostings">
                                    <!-- Job postings will be loaded here -->
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
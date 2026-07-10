<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get all submissions
$stmt = $db->prepare("SELECT s.*, u.first_name, u.last_name, u.employee_id, u.profile_picture 
                       FROM employee_submissions s 
                       JOIN users u ON s.employee_id = u.id 
                       ORDER BY s.created_at DESC");
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Submissions - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .submission-item {
            padding: 15px 20px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
            transition: all 0.3s ease;
        }
        .submission-item:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .submission-item.pending { border-left-color: #ffc107; background: #fffbf0; }
        .submission-item.reviewed { border-left-color: #17a2b8; background: #f0f9ff; }
        .submission-item.approved { border-left-color: #28a745; background: #f0fff4; }
        .submission-item.rejected { border-left-color: #dc3545; background: #fff5f5; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-badge.pending { background: #ffc107; color: #333; }
        .status-badge.reviewed { background: #17a2b8; color: white; }
        .status-badge.approved { background: #28a745; color: white; }
        .status-badge.rejected { background: #dc3545; color: white; }
        .feedback-textarea { resize: vertical; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; }
        .btn-primary:hover { transform: scale(1.02); }
        .employee-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder-small { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 16px; }
        .file-link { color: #2c5aa0; text-decoration: none; }
        .file-link:hover { text-decoration: underline; }
        .empty-state { text-align: center; padding: 50px 0; color: #6c757d; }
        .empty-state i { font-size: 48px; color: #dee2e6; margin-bottom: 15px; display: block; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-file-alt me-2"></i>Employee Submissions</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>All Submissions</h5>
        </div>
        <div class="card-body">
            <?php if (count($submissions) > 0): ?>
                <?php foreach ($submissions as $sub): ?>
                    <div class="submission-item <?php echo $sub['status']; ?>" id="submission-<?php echo $sub['id']; ?>">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="d-flex align-items-start">
                                    <?php if($sub['profile_picture'] && file_exists('../' . $sub['profile_picture'])): ?>
                                        <img src="../<?php echo $sub['profile_picture']; ?>" class="employee-avatar me-2">
                                    <?php else: ?>
                                        <div class="avatar-placeholder-small me-2">
                                            <?php echo strtoupper(substr($sub['first_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($sub['title']); ?></h6>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($sub['description']); ?></p>
                                        <div class="mb-1">
                                            <strong>Employee:</strong> <?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?>
                                            <span class="badge bg-secondary ms-2"><?php echo $sub['employee_id']; ?></span>
                                        </div>
                                        <?php if ($sub['file_path']): ?>
                                            <a href="../<?php echo $sub['file_path']; ?>" target="_blank" class="file-link">
                                                <i class="fas fa-download me-1"></i>Download File
                                            </a>
                                            <span class="text-muted ms-2 small">
                                                <i class="fas fa-file me-1"></i><?php echo $sub['file_name']; ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="mt-1">
                                            <span class="status-badge <?php echo $sub['status']; ?>">
                                                <?php echo ucfirst($sub['status']); ?>
                                            </span>
                                            <small class="text-muted ms-2">
                                                <i class="fas fa-clock me-1"></i><?php echo date('d M Y, h:i A', strtotime($sub['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <form class="feedback-form" data-id="<?php echo $sub['id']; ?>">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="pending" <?php echo $sub['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                                <option value="reviewed" <?php echo $sub['status'] == 'reviewed' ? 'selected' : ''; ?>>📝 Reviewed</option>
                                                <option value="approved" <?php echo $sub['status'] == 'approved' ? 'selected' : ''; ?>>✅ Approved</option>
                                                <option value="rejected" <?php echo $sub['status'] == 'rejected' ? 'selected' : ''; ?>>❌ Rejected</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-save me-1"></i>Update
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <textarea class="form-control form-control-sm feedback-textarea" name="feedback" rows="2" placeholder="Write feedback..."><?php echo htmlspecialchars($sub['feedback']); ?></textarea>
                                    </div>
                                    <?php if ($sub['feedback']): ?>
                                        <div class="mt-1 small text-muted">
                                            <i class="fas fa-comment me-1"></i>Feedback given
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No submissions from employees yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.feedback-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = this.dataset.id;
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // Show loading
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        btn.disabled = true;
        
        try {
            const response = await fetch('../api/review-submission.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, ...data })
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                location.reload();
            }
        } catch(e) {
            alert('Error updating submission');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
});
</script>
</body>
</html>

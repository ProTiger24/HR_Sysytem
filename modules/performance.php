<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$is_hr = ($user_type === 'hr');

// Get employees for HR
$employees = [];
if ($is_hr) {
    $stmt = $db->prepare("SELECT id, first_name, last_name, employee_id, department FROM users WHERE user_type = 'employee' AND status = 'active' ORDER BY first_name");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get performance reviews
if ($is_hr) {
    $stmt = $db->prepare("SELECT pr.*, 
                           e.first_name as emp_first, e.last_name as emp_last, e.employee_id,
                           r.first_name as rev_first, r.last_name as rev_last
                           FROM performance_reviews pr 
                           JOIN users e ON pr.employee_id = e.id 
                           JOIN users r ON pr.reviewer_id = r.id 
                           ORDER BY pr.created_at DESC");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare("SELECT pr.*, u.first_name as reviewer_first, u.last_name as reviewer_last 
                           FROM performance_reviews pr 
                           JOIN users u ON pr.reviewer_id = u.id 
                           WHERE pr.employee_id = ? 
                           ORDER BY pr.created_at DESC");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$back_link = $is_hr ? '../hr-dashboard.php' : '../employee-dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Reviews - KormoShathi</title>
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
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; }
        .btn-primary:hover { transform: scale(1.02); }
        .review-item {
            padding: 15px 20px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #6f42c1;
            transition: all 0.3s ease;
        }
        .review-item:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .review-item .stars { color: #ffc107; font-size: 18px; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-badge.completed { background: #28a745; color: white; }
        .status-badge.scheduled { background: #ffc107; color: #333; }
        .status-badge.cancelled { background: #dc3545; color: white; }
        .rating-box { display: inline-block; padding: 2px 10px; border-radius: 5px; background: #f0f0f0; }
        .review-title { font-weight: 600; color: #2c5aa0; }
        .empty-state { text-align: center; padding: 40px 0; color: #6c757d; }
        .empty-state i { font-size: 48px; color: #dee2e6; margin-bottom: 15px; display: block; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-star me-2"></i>Performance Reviews</h4>
    <a href="<?php echo $back_link; ?>"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="container">
    <?php if ($is_hr): ?>
    <!-- Add Performance Review Form - Only for HR -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus-circle me-2"></i>Add Performance Review</h5>
        </div>
        <div class="card-body">
            <form id="reviewForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Employee *</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?> (<?php echo $emp['employee_id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Review Title *</label>
                        <input type="text" class="form-control" name="review_title" placeholder="e.g., Q4 Performance Review" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Technical Skills (1-5)</label>
                        <input type="number" class="form-control" name="technical_skills" min="1" max="5" value="3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Communication (1-5)</label>
                        <input type="number" class="form-control" name="communication" min="1" max="5" value="3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Teamwork (1-5)</label>
                        <input type="number" class="form-control" name="teamwork" min="1" max="5" value="3">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Leadership (1-5)</label>
                        <input type="number" class="form-control" name="leadership" min="1" max="5" value="3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Problem Solving (1-5)</label>
                        <input type="number" class="form-control" name="problem_solving" min="1" max="5" value="3">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Overall Rating (1-5) *</label>
                        <input type="number" class="form-control" name="overall_rating" min="1" max="5" value="3" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Comments</label>
                    <textarea class="form-control" name="comments" rows="2" placeholder="Additional comments..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Strengths</label>
                    <textarea class="form-control" name="strengths" rows="2" placeholder="Employee strengths..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Areas for Improvement</label>
                    <textarea class="form-control" name="weaknesses" rows="2" placeholder="Areas for improvement..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Submit Review
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Reviews List -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i><?php echo $is_hr ? 'All Reviews' : 'My Performance Reviews'; ?></h5>
        </div>
        <div class="card-body">
            <div id="reviewsList">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex:1;">
                                    <div class="review-title"><?php echo htmlspecialchars($review['review_title'] ?? 'Performance Review'); ?></div>
                                    <?php if ($is_hr): ?>
                                        <div class="mb-1">
                                            <strong>Employee:</strong> <?php echo $review['emp_first'] . ' ' . $review['emp_last']; ?>
                                            <span class="badge bg-secondary ms-2"><?php echo $review['employee_id']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="mb-1">
                                        <span class="stars"><?php echo str_repeat('⭐', $review['overall_rating'] ?? 0) . str_repeat('☆', 5 - ($review['overall_rating'] ?? 0)); ?></span>
                                        <span class="rating-box"><?php echo $review['overall_rating'] ?? 0; ?>/5</span>
                                    </div>
                                    <?php if ($review['comments']): ?>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($review['comments']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($review['strengths']): ?>
                                        <div class="mb-1 small"><strong>Strengths:</strong> <?php echo htmlspecialchars($review['strengths']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($review['weaknesses']): ?>
                                        <div class="mb-1 small"><strong>Improvement:</strong> <?php echo htmlspecialchars($review['weaknesses']); ?></div>
                                    <?php endif; ?>
                                    <div class="mt-1">
                                        <span class="status-badge <?php echo $review['status'] ?? 'completed'; ?>">
                                            <?php echo ucfirst($review['status'] ?? 'Completed'); ?>
                                        </span>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-user me-1"></i><?php echo $is_hr ? ($review['rev_first'] . ' ' . $review['rev_last']) : ($review['reviewer_first'] . ' ' . $review['reviewer_last']); ?>
                                            <i class="fas fa-clock ms-2 me-1"></i><?php echo date('d M Y', strtotime($review['review_date'] ?? $review['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>No performance reviews yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($is_hr): ?>
<script>
document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Convert string values to numbers
    data.technical_skills = parseInt(data.technical_skills) || 3;
    data.communication = parseInt(data.communication) || 3;
    data.teamwork = parseInt(data.teamwork) || 3;
    data.leadership = parseInt(data.leadership) || 3;
    data.problem_solving = parseInt(data.problem_solving) || 3;
    data.overall_rating = parseInt(data.overall_rating) || 3;
    
    try {
        const response = await fetch('../api/performance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            location.reload();
        }
    } catch(e) {
        alert('Error submitting review');
    }
});
</script>
<?php endif; ?>
</body>
</html>

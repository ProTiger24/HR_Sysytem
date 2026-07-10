<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

$config_id = $_GET['id'] ?? 0;
require_once '../config/database.php';
$db = (new Database())->getConnection();

// If no config_id, get the latest active one
if (!$config_id) {
    $stmt = $db->prepare("SELECT id FROM ai_screening_config WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        header('Location: ai-shortlist.php?id=' . $config['id']);
        exit;
    }
}

// Get config details
$stmt = $db->prepare("SELECT * FROM ai_screening_config WHERE id = ?");
$stmt->execute([$config_id]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    // No config found, show message
    $has_config = false;
} else {
    $has_config = true;
    // Get shortlisted candidates
    $stmt = $db->prepare("SELECT * FROM ai_screening_results 
                          WHERE config_id = ? AND recommendation = 'shortlist' 
                          ORDER BY match_score DESC");
    $stmt->execute([$config_id]);
    $shortlisted = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all candidates
    $stmt = $db->prepare("SELECT * FROM ai_screening_results 
                          WHERE config_id = ? 
                          ORDER BY match_score DESC");
    $stmt->execute([$config_id]);
    $all_candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get interview details
    $interview_date = $config['interview_date'] ?? '';
    $interview_location = $config['interview_location'] ?? '';
    $interview_type = $config['interview_type'] ?? 'physical';
    $meeting_link = $config['meeting_link'] ?? '';
    $interviewer_name = $config['interviewer_name'] ?? 'HR Manager';
    $notes = $config['notes'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Screening Results - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { padding: 15px 20px; }
        .card-header.bg-shortlist { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; }
        .card-header.bg-all { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; }
        .recommendation-badge { font-size: 12px; padding: 3px 10px; border-radius: 20px; }
        .recommendation-badge.shortlist { background: #28a745; color: white; }
        .recommendation-badge.review { background: #ffc107; color: #333; }
        .recommendation-badge.reject { background: #dc3545; color: white; }
        .empty-state { text-align: center; padding: 40px 20px; color: #6c757d; }
        .empty-state i { font-size: 48px; color: #dee2e6; margin-bottom: 15px; display: block; }
        .checkbox-column { width: 40px; }
        .form-check-input { width: 18px; height: 18px; cursor: pointer; }
        .status-scheduled { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .interview-details-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #6f42c1; }
        .interview-details-box .detail-item { display: inline-block; margin-right: 20px; font-size: 14px; }
        .interview-details-box .detail-item i { color: #6f42c1; margin-right: 5px; }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-robot me-2"></i>AI Screening Results</h4>
    <div>
        <a href="ai-screening-form.php" class="me-2"><i class="fas fa-plus me-1"></i>New Screening</a>
        <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

<div class="container">
    <?php if ($has_config && $config): ?>
    <!-- Job Info -->
    <div class="card">
        <div class="card-header bg-shortlist">
            <h5 class="mb-0">
                <i class="fas fa-briefcase me-2"></i>
                <?php echo htmlspecialchars($config['job_title'] ?? 'Job'); ?>
                <span class="badge bg-light text-dark ms-2">
                    <?php echo isset($shortlisted) ? count($shortlisted) : 0; ?> Shortlisted
                </span>
                <span class="badge bg-light text-dark ms-1">
                    <?php echo isset($all_candidates) ? count($all_candidates) : 0; ?> Total
                </span>
                <small class="ms-3 opacity-75">
                    <?php echo date('d M Y', strtotime($config['start_date'])); ?> - 
                    <?php echo date('d M Y', strtotime($config['end_date'])); ?>
                </small>
            </h5>
        </div>
    </div>

    <!-- Interview Details -->
    <?php if (!empty($interview_date)): ?>
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #6f42c1 0%, #2c5aa0 100%); color: white;">
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Interview Details</h5>
        </div>
        <div class="card-body">
            <div class="interview-details-box">
                <div class="detail-item">
                    <i class="fas fa-calendar"></i> 
                    <strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($interview_date)); ?>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i> 
                    <strong>Location:</strong> <?php echo htmlspecialchars($interview_location ?: 'To be confirmed'); ?>
                </div>
                <div class="detail-item">
                    <i class="fas fa-video"></i> 
                    <strong>Type:</strong> <?php echo ucfirst($interview_type); ?>
                </div>
                <?php if ($meeting_link): ?>
                <div class="detail-item">
                    <i class="fas fa-link"></i> 
                    <strong>Meeting Link:</strong> <a href="<?php echo $meeting_link; ?>" target="_blank">Click here</a>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <i class="fas fa-user-tie"></i> 
                    <strong>Interviewer:</strong> <?php echo htmlspecialchars($interviewer_name); ?>
                </div>
                <?php if ($notes): ?>
                <div class="detail-item">
                    <i class="fas fa-sticky-note"></i> 
                    <strong>Notes:</strong> <?php echo htmlspecialchars($notes); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Interview Details Not Set!</strong> 
                Please go to <a href="ai-screening-form.php" class="alert-link">AI Screening Form</a> and set interview date, location, and other details.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Shortlisted Candidates -->
    <div class="card">
        <div class="card-header bg-shortlist">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Shortlisted Candidates</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($shortlisted)): ?>
            <form id="interviewForm">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="checkbox-column"><input type="checkbox" id="selectAll"></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Score</th>
                                <th>Skills</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shortlisted as $c): 
                                $isScheduled = ($c['status'] ?? '') == 'interview_scheduled';
                                $skills_str = $c['skills_found'] ?? '';
                                if (is_string($skills_str)) {
                                    $skills = array_map('trim', explode(',', $skills_str));
                                } else {
                                    $skills = is_array($skills_str) ? $skills_str : [];
                                }
                                $skills = array_filter($skills);
                            ?>
                            <tr class="candidate-row" id="row-<?php echo $c['id']; ?>" style="<?php echo $isScheduled ? 'background: #f0fff4;' : ''; ?>">
                                <td>
                                    <?php if (!$isScheduled): ?>
                                    <input type="checkbox" class="form-check-input candidate-checkbox" name="result_ids[]" value="<?php echo $c['id']; ?>">
                                    <?php else: ?>
                                    <span class="badge bg-success">✅ Sent</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($c['candidate_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['candidate_email']); ?></td>
                                <td><span class="badge bg-success" style="font-size:14px;"><?php echo $c['match_score']; ?>%</span></td>
                                <td>
                                    <?php 
                                    if (!empty($skills)) {
                                        foreach (array_slice($skills, 0, 4) as $s):
                                            $s = trim($s); 
                                            if ($s): ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($s); ?></span>
                                            <?php endif;
                                        endforeach; 
                                        if (count($skills) > 4): ?>
                                            <span class="badge bg-secondary">+<?php echo count($skills) - 4; ?></span>
                                        <?php endif;
                                    } else {
                                        echo '<span class="text-muted">No skills listed</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($isScheduled): ?>
                                    <span class="status-scheduled"><i class="fas fa-check-circle me-1"></i>Interview Scheduled</span>
                                    <?php else: ?>
                                    <span class="recommendation-badge shortlist">Shortlist</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($interview_date)): ?>
                <button type="submit" class="btn btn-success w-100 mt-3" id="sendBtn">
                    <i class="fas fa-envelope me-2"></i>Send Interview Invitations to Selected Candidates
                </button>
                <?php else: ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Interview Details Not Set!</strong> 
                    Please go to <a href="ai-screening-form.php" class="alert-link">AI Screening Form</a> and set interview details.
                </div>
                <?php endif; ?>
            </form>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <h5>No Shortlisted Candidates</h5>
                <p class="text-muted">Candidates will appear here after AI screening.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Candidates -->
    <div class="card">
        <div class="card-header bg-all">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Candidates</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($all_candidates)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Score</th>
                            <th>Recommendation</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_candidates as $c): 
                            $recClass = $c['recommendation'] ?? 'review';
                            $recLabel = ucfirst($recClass);
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['candidate_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($c['candidate_email']); ?></td>
                            <td>
                                <span class="badge <?php echo $recClass == 'shortlist' ? 'bg-success' : ($recClass == 'review' ? 'bg-warning' : 'bg-danger'); ?>" style="font-size:14px;">
                                    <?php echo $c['match_score'] ?? 0; ?>%
                                </span>
                            </td>
                            <td><span class="recommendation-badge <?php echo $recClass; ?>"><?php echo $recLabel; ?></span></td>
                            <td>
                                <span class="badge <?php echo ($c['status'] ?? 'pending') == 'interview_scheduled' ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $c['status'] ?? 'pending')); ?>
                                </span>
                            </td>
                            <td><small><?php echo date('d M Y', strtotime($c['created_at'] ?? 'now')); ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No candidates submitted yet</h5>
                <p class="text-muted">Share the resume submission link with candidates.</p>
                <a href="../candidate/submit-resume.php" target="_blank" class="btn btn-primary mt-2">
                    <i class="fas fa-link me-2"></i>Get Submission Link
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- No Config Found -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-robot" style="font-size: 48px; color: #2c5aa0;"></i>
            <h4 class="mt-3">No Active AI Screening</h4>
            <p class="text-muted">Start a new AI screening to automatically screen resumes.</p>
            <a href="ai-screening-form.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Start AI Screening
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.candidate-checkbox:not(:disabled)').forEach(cb => {
        cb.checked = this.checked;
        cb.closest('tr').style.background = this.checked ? '#e3f2fd' : '';
    });
});

// Individual checkbox highlight
document.querySelectorAll('.candidate-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        this.closest('tr').style.background = this.checked ? '#e3f2fd' : '';
    });
});

// Interview form submit
document.getElementById('interviewForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const selected = document.querySelectorAll('.candidate-checkbox:checked');
    if (selected.length === 0) {
        alert('⚠️ Please select at least one candidate');
        return;
    }
    
    const interviewData = {
        result_ids: Array.from(selected).map(cb => cb.value),
        interview_date: '<?php echo $interview_date; ?>',
        interview_location: '<?php echo htmlspecialchars($interview_location); ?>',
        interview_type: '<?php echo $interview_type; ?>',
        meeting_link: '<?php echo htmlspecialchars($meeting_link); ?>',
        interviewer_name: '<?php echo htmlspecialchars($interviewer_name); ?>',
        notes: '<?php echo htmlspecialchars($notes); ?>'
    };
    
    const btn = document.getElementById('sendBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    btn.disabled = true;
    
    try {
        const response = await fetch('../api/send-interview.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(interviewData)
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            location.reload();
        }
    } catch(e) {
        alert('❌ Error: ' + e.message);
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>
</body>
</html>

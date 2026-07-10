<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Screening - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #6f42c1 0%, #2c5aa0 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .form-label { font-weight: 600; color: #495057; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid #ddd; padding: 10px 15px; }
        .form-control:focus, .form-select:focus { border-color: #6f42c1; box-shadow: 0 0 0 0.2rem rgba(111,66,193,0.25); }
        .btn-primary { background: linear-gradient(135deg, #6f42c1 0%, #2c5aa0 100%); border: none; padding: 12px; font-weight: 600; border-radius: 8px; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .section-title { font-weight: 600; color: #2c5aa0; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin-bottom: 15px; }
        .help-text { font-size: 12px; color: #6c757d; }
        .rule-box { background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #6f42c1; }
        .rule-box .score-range { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .score-range .badge { font-size: 13px; padding: 5px 12px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-robot me-2"></i>AI Auto Job Screening</h4>
    <div>
        <a href="ai-shortlist.php" class="me-2"><i class="fas fa-star me-1"></i>Shortlist</a>
        <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus-circle me-2"></i>Setup AI Screening</h5>
        </div>
        <div class="card-body">
            <form id="aiScreeningForm">
                <!-- Job Information -->
                <div class="section-title">📋 Job Information</div>
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Job Title *</label>
                        <input type="text" class="form-control" name="job_title" placeholder="e.g., Senior PHP Developer" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Department *</label>
                        <select class="form-select" name="department" required>
                            <option value="">Select</option>
                            <option value="IT">IT</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Sales">Sales</option>
                        </select>
                    </div>
                </div>

                <!-- Screening Period -->
                <div class="section-title">📅 Screening Period</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date *</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date *</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>

                <!-- Job Requirements -->
                <div class="section-title">📋 Job Requirements</div>
                <div class="mb-3">
                    <label class="form-label">Required Skills *</label>
                    <textarea class="form-control" name="required_skills" rows="3" 
                              placeholder="PHP, Laravel, MySQL, JavaScript, Git, Docker, AWS" required></textarea>
                    <small class="help-text">Separate with commas. Example: PHP, Laravel, MySQL</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Priority Skills (Extra Weight)</label>
                    <textarea class="form-control" name="priority_skills" rows="2" 
                              placeholder="Laravel, Docker, AWS"></textarea>
                    <small class="help-text">These skills will get higher score. Separate with commas.</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Minimum Experience (Years)</label>
                        <input type="number" class="form-control" name="min_experience" value="0" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preferred Education</label>
                        <input type="text" class="form-control" name="preferred_education" placeholder="B.Sc in CSE/IT">
                    </div>
                </div>

                <!-- AI Screening Rules -->
                <div class="section-title">🎯 AI Screening Rules</div>
                <div class="rule-box mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto Shortlist Score</label>
                            <input type="number" class="form-control" name="auto_shortlist_score" value="80" min="0" max="100">
                            <small class="help-text text-success">Above this score: Auto Shortlist</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto Reject Score</label>
                            <input type="number" class="form-control" name="auto_reject_score" value="40" min="0" max="100">
                            <small class="help-text text-danger">Below this score: Auto Reject</small>
                        </div>
                    </div>
                    <div class="score-range">
                        <span class="badge bg-success">✅ Shortlist: ≥ 80%</span>
                        <span class="badge bg-warning text-dark">📝 Review: 40-80%</span>
                        <span class="badge bg-danger">❌ Reject: ≤ 40%</span>
                    </div>
                </div>

                <!-- Interview Details -->
                <div class="section-title">📞 Interview Details</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Interview Date</label>
                        <input type="datetime-local" class="form-control" name="interview_date">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Interview Location</label>
                        <input type="text" class="form-control" name="interview_location" placeholder="Dhaka, Bangladesh">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Interview Type</label>
                        <select class="form-select" name="interview_type">
                            <option value="physical">Physical</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3" id="meetingLinkDiv">
                        <label class="form-label">Meeting Link</label>
                        <input type="url" class="form-control" name="meeting_link" placeholder="https://meet.google.com/...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Interviewer</label>
                        <input type="text" class="form-control" name="interviewer_name" placeholder="HR Manager">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes for Candidates</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Bring your CV, portfolio..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    <i class="fas fa-rocket me-2"></i>Start AI Screening
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="interview_type"]').addEventListener('change', function() {
    document.getElementById('meetingLinkDiv').style.display = this.value === 'online' ? 'block' : 'block';
});

document.getElementById('aiScreeningForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    data.min_experience = parseInt(data.min_experience) || 0;
    data.auto_shortlist_score = parseInt(data.auto_shortlist_score) || 80;
    data.auto_reject_score = parseInt(data.auto_reject_score) || 40;
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Starting AI Screening...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('../api/ai-screening.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            // ✅ Redirect to shortlist with config ID
            window.location.href = 'ai-shortlist.php?id=' + result.config_id;
        }
    } catch(e) {
        alert('Error: ' + e.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>
</body>
</html>

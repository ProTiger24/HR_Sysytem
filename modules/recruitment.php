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
    <title>Recruitment - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; }
        .btn-primary:hover { transform: translateY(-2px); }
        .job-card { border-left: 4px solid #2c5aa0; margin-bottom: 15px; transition: 0.3s; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .job-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .badge-open { background: #28a745; color: white; padding: 5px 10px; border-radius: 5px; }
        .badge-closed { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-briefcase me-2"></i>Recruitment Management</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Post New Job</h5>
        </div>
        <div class="card-body">
            <form id="jobPostForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Job Title *</label>
                        <input type="text" class="form-control" name="job_title" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department *</label>
                        <select class="form-select" name="department" required>
                            <option value="">Select Department</option>
                            <option value="IT">IT</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Sales">Sales</option>
                            <option value="Operations">Operations</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Job Type</label>
                        <select class="form-select" name="job_type">
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Vacancies</label>
                        <input type="number" class="form-control" name="vacancies" value="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Job Description *</label>
                    <textarea class="form-control" name="job_description" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Requirements *</label>
                    <textarea class="form-control" name="requirements" rows="3" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary Range</label>
                        <input type="text" class="form-control" name="salary_range" placeholder="e.g., 30000-50000 BDT">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="Dhaka, Bangladesh">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Date to Apply</label>
                        <input type="date" class="form-control" name="last_date">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Post Job</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Active Job Postings</h5>
        </div>
        <div class="card-body">
            <div id="jobListings">
                <p class="text-muted text-center">Loading job postings...</p>
            </div>
        </div>
    </div>
</div>

<script>
async function loadJobs() {
    try {
        const response = await fetch('../api/jobs.php');
        const data = await response.json();
        const container = document.getElementById('jobListings');
        
        if(data.success && data.data) {
            if(data.data.length > 0) {
                container.innerHTML = data.data.map(job => `
                    <div class="job-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">${job.job_title}</h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-building me-1"></i>${job.department} | 
                                    <i class="fas fa-map-marker-alt me-1"></i>${job.location || 'N/A'}
                                </p>
                                <p class="small mb-1"><strong>Description:</strong> ${job.job_description?.substring(0, 150) || 'N/A'}</p>
                                <p class="small"><strong>Requirements:</strong> ${job.requirements?.substring(0, 100) || 'N/A'}</p>
                                <span class="badge ${job.status === 'open' ? 'badge-open' : 'badge-closed'}">${job.status}</span>
                                <span class="badge bg-info text-white">${job.vacancies} vacancies</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Posted: ${new Date(job.created_at).toLocaleDateString()}</small><br>
                                <small class="text-muted">Last Date: ${job.last_date ? new Date(job.last_date).toLocaleDateString() : 'N/A'}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-muted text-center">No job postings yet.</p>';
            }
        } else {
            container.innerHTML = `<p class="text-muted text-center">${data.message || 'Error loading jobs'}</p>`;
        }
    } catch(e) { 
        console.error(e);
        document.getElementById('jobListings').innerHTML = '<p class="text-muted text-center">Failed to load job postings.</p>';
    }
}

document.getElementById('jobPostForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const jobData = {
        job_title: formData.get('job_title'),
        department: formData.get('department'),
        job_type: formData.get('job_type'),
        vacancies: formData.get('vacancies'),
        job_description: formData.get('job_description'),
        requirements: formData.get('requirements'),
        salary_range: formData.get('salary_range'),
        location: formData.get('location'),
        last_date: formData.get('last_date'),
        status: formData.get('status')
    };
    
    try {
        const response = await fetch('../api/jobs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(jobData)
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            this.reset();
            loadJobs();
        }
    } catch(error) { 
        alert('Error posting job: ' + error);
    }
});

loadJobs();
setInterval(loadJobs, 30000);
</script>
</body>
</html>

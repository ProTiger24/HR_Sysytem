<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Resume - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); min-height: 100vh; display: flex; align-items: center; }
        .container { max-width: 550px; margin: auto; padding: 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 25px 20px; text-align: center; }
        .card-header h4 { font-weight: 700; margin: 0; }
        .card-header p { opacity: 0.8; margin: 5px 0 0; }
        .card-body { padding: 2rem; }
        .form-label { font-weight: 600; color: #495057; }
        .form-control { border-radius: 8px; border: 1px solid #ddd; padding: 10px 15px; }
        .form-control:focus { border-color: #2c5aa0; box-shadow: 0 0 0 0.2rem rgba(44,90,160,0.25); }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 12px; font-weight: 600; border-radius: 8px; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .file-upload { border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .file-upload:hover { border-color: #2c5aa0; background: #f8f9fa; }
        .file-upload i { font-size: 40px; color: #2c5aa0; }
        #fileInfo { display: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4><i class="fas fa-paper-plane me-2"></i>Submit Your Resume</h4>
            <p>Apply for current job openings</p>
        </div>
        <div class="card-body">
            <form id="resumeForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter your full name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" placeholder="Enter your phone number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Resume *</label>
                    <div class="file-upload" onclick="document.getElementById('resumeFile').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p class="mt-2 mb-0">Click to browse or drag & drop</p>
                        <small class="text-muted">PDF, DOC, DOCX (Max 10MB)</small>
                        <input type="file" class="d-none" name="resume" id="resumeFile" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div id="fileInfo" class="mt-2">
                        <div class="alert alert-info">
                            <i class="fas fa-file me-2"></i>
                            <span id="fileName">file.pdf</span>
                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="removeFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i>Submit Resume
                </button>
            </form>
            <div id="message" class="mt-3"></div>
        </div>
    </div>
</div>

<script>
document.getElementById('resumeFile').addEventListener('change', function() {
    if (this.files.length) {
        document.getElementById('fileInfo').style.display = 'block';
        document.getElementById('fileName').textContent = this.files[0].name;
        document.querySelector('.file-upload').style.display = 'none';
    }
});

function removeFile() {
    document.getElementById('resumeFile').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.querySelector('.file-upload').style.display = 'block';
}

document.getElementById('resumeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    btn.disabled = true;
    document.getElementById('message').innerHTML = '';
    
    try {
        const response = await fetch('../api/submit-resume.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        const alertClass = result.success ? 'alert-success' : 'alert-danger';
        document.getElementById('message').innerHTML = 
            `<div class="alert ${alertClass}">${result.message}</div>`;
        if (result.success) {
            this.reset();
            document.getElementById('fileInfo').style.display = 'none';
            document.querySelector('.file-upload').style.display = 'block';
        }
    } catch(e) {
        document.getElementById('message').innerHTML = 
            `<div class="alert alert-danger">Error submitting resume</div>`;
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>
</body>
</html>

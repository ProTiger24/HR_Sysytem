<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get employee submissions
$stmt = $db->prepare("SELECT * FROM employee_submissions WHERE employee_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Work - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1000px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; }
        .btn-primary:hover { transform: scale(1.02); }
        .submission-item {
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 12px;
            background: #f8f9fa;
            border-radius: 8px;
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
        .file-upload-area {
            border: 2px dashed #ddd;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            transition: 0.3s;
            cursor: pointer;
        }
        .file-upload-area:hover { border-color: #2c5aa0; background: #f8f9fa; }
        .file-upload-area.dragover { border-color: #2c5aa0; background: #e3f2fd; }
        .feedback-box {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 8px;
            border-left: 3px solid #2c5aa0;
        }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-upload me-2"></i>Submit Work</h4>
    <a href="../employee-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="container">
    <!-- Submit Form -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus-circle me-2"></i>Submit New Work</h5>
        </div>
        <div class="card-body">
            <form id="submitForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title *</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter work title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Describe your work"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Attach File *</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #2c5aa0;"></i>
                        <p class="mt-2">Drag & drop your file here or click to browse</p>
                        <p class="text-muted small">Supported: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG (Max 10MB)</p>
                        <input type="file" class="d-none" name="file" id="fileInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" required>
                    </div>
                    <div id="fileInfo" class="mt-2" style="display:none;">
                        <div class="alert alert-info">
                            <i class="fas fa-file me-2"></i>
                            <span id="fileName">file.pdf</span>
                            <span class="badge bg-secondary ms-2" id="fileSize">0 KB</span>
                            <button type="button" class="btn btn-sm btn-outline-danger float-end" onclick="removeFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i>Submit Work
                </button>
            </form>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>My Submissions</h5>
        </div>
        <div class="card-body">
            <div id="submissionsList">
                <?php if (count($submissions) > 0): ?>
                    <?php foreach ($submissions as $sub): ?>
                        <div class="submission-item <?php echo $sub['status']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex:1;">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($sub['title']); ?></h6>
                                    <p class="mb-1 small"><?php echo htmlspecialchars($sub['description']); ?></p>
                                    <?php if ($sub['file_path']): ?>
                                        <a href="../<?php echo $sub['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>Download File
                                        </a>
                                    <?php endif; ?>
                                    <div class="mt-1">
                                        <span class="status-badge <?php echo $sub['status']; ?>">
                                            <?php echo ucfirst($sub['status']); ?>
                                        </span>
                                        <small class="text-muted ms-2">
                                            <i class="fas fa-clock me-1"></i><?php echo date('d M Y, h:i A', strtotime($sub['created_at'])); ?>
                                        </small>
                                    </div>
                                    <?php if ($sub['feedback']): ?>
                                        <div class="feedback-box mt-2">
                                            <strong><i class="fas fa-comment text-primary me-1"></i>Feedback:</strong>
                                            <p class="mb-0"><?php echo htmlspecialchars($sub['feedback']); ?></p>
                                            <small class="text-muted">
                                                Reviewed on <?php echo date('d M Y, h:i A', strtotime($sub['reviewed_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No submissions yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// File upload drag and drop
const uploadArea = document.getElementById('fileUploadArea');
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

uploadArea.addEventListener('click', () => fileInput.click());

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', function() {
    if (this.files.length) {
        updateFileInfo(this.files[0]);
    }
});

function updateFileInfo(file) {
    fileInfo.style.display = 'block';
    fileName.textContent = file.name;
    const size = (file.size / 1024).toFixed(1);
    fileSize.textContent = size > 1024 ? (size / 1024).toFixed(1) + ' MB' : size + ' KB';
    uploadArea.style.display = 'none';
}

function removeFile() {
    fileInput.value = '';
    fileInfo.style.display = 'none';
    uploadArea.style.display = 'block';
}

// Submit form
document.getElementById('submitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/submit-work.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            location.reload();
        }
    } catch(e) {
        alert('Error submitting work');
    }
});
</script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$is_hr = ($_SESSION['user_type'] == 'hr');
$back_link = $is_hr ? 'hr-dashboard.php' : 'employee-dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 600px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; transition: 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; transition: 0.3s; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .profile-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; display: block; border: 3px solid #2c5aa0; }
        .avatar-placeholder { width: 150px; height: 150px; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: bold; color: white; margin: 0 auto 20px; border: 3px solid #2c5aa0; }
        .form-control { border-radius: 8px; border: 1px solid #ddd; padding: 10px; }
        .form-control:focus { border-color: #2c5aa0; box-shadow: 0 0 0 0.2rem rgba(44,90,160,0.25); }
        .signature-preview { max-height: 80px; border: 1px solid #ddd; padding: 5px; border-radius: 5px; background: white; }
        .text-start p { margin-bottom: 8px; }
        .section-title { font-weight: 600; color: #2c5aa0; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-user-circle me-2"></i>My Profile</h4>
    <a href="<?php echo $back_link; ?>">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="container">
    <!-- Profile Information -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Profile Information</h5>
        </div>
        <div class="card-body text-center">
            <?php if(!empty($user['profile_picture']) && file_exists('/opt/lampp/htdocs/kormoshathi/' . $user['profile_picture'])): ?>
                <img src="/kormoshathi/<?php echo $user['profile_picture']; ?>" class="profile-img" id="profileImage">
            <?php else: ?>
                <div class="avatar-placeholder" id="profileImage">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <form id="profilePicForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="file" class="form-control" name="profile_picture" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload Profile Picture
                </button>
            </form>
            
            <hr>
            
            <div class="text-start">
                <div class="section-title">📋 Personal Information</div>
                <p><strong>Name:</strong> <?php echo $user['first_name'] . ' ' . $user['last_name']; ?></p>
                <p><strong>Employee ID:</strong> <?php echo $user['employee_id']; ?></p>
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user['phone'] ?? 'N/A'; ?></p>
                <p><strong>Department:</strong> <?php echo $user['department'] ?? 'N/A'; ?></p>
                <p><strong>Position:</strong> <?php echo $user['position'] ?? 'N/A'; ?></p>
                <p><strong>Blood Group:</strong> <?php echo $user['blood_group'] ?? 'N/A'; ?></p>
                <p><strong>Role:</strong> <?php echo ucfirst($user['user_type']); ?></p>
                <p><strong>Join Date:</strong> <?php echo date('d M Y', strtotime($user['join_date'] ?? 'now')); ?></p>
            </div>
        </div>
    </div>

    <!-- Signature Upload Section (Only for HR) -->
    <?php if ($is_hr): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-signature me-2"></i>HR Signature</h5>
        </div>
        <div class="card-body">
            <form id="signatureForm" enctype="multipart/form-data">
                <div class="mb-3 text-center">
                    <?php if(file_exists('/opt/lampp/htdocs/kormoshathi/uploads/signature.png')): ?>
                        <img src="/kormoshathi/uploads/signature.png" class="signature-preview" alt="Current Signature">
                        <p class="text-muted small mt-1">Current Signature</p>
                    <?php else: ?>
                        <p class="text-muted">No signature uploaded</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <input type="file" class="form-control" name="signature" accept="image/png,image/jpg,image/jpeg" required>
                    <small class="text-muted">Upload signature image (PNG, JPG) - transparent background preferred</small>
                </div>
                <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-upload me-2"></i>Upload Signature
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Profile Picture Upload
document.getElementById('profilePicForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    btn.disabled = true;
    
    try {
        const response = await fetch('api/upload-profile.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            const img = document.getElementById('profileImage');
            if (img.tagName === 'IMG') {
                img.src = '/kormoshathi/' + result.image_path + '?t=' + new Date().getTime();
            } else {
                img.outerHTML = `<img src="/kormoshathi/${result.image_path}?t=${new Date().getTime()}" class="profile-img" id="profileImage">`;
            }
            alert('✅ Profile picture updated successfully!');
        } else {
            alert('✗ Error: ' + result.message);
        }
    } catch(error) {
        alert('Connection error! Please try again.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Signature Upload (Only for HR)
document.getElementById('signatureForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
    btn.disabled = true;
    
    try {
        const response = await fetch('api/upload-signature.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        alert(result.message);
        if(result.success) location.reload();
    } catch(e) {
        alert('Error uploading signature');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});
</script>
</body>
</html>

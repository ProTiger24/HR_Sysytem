<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if this is a forced password change
$force_change = isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] === true;
$user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$user_type = $_SESSION['user_type'] ?? 'employee';
$dashboard_link = ($user_type === 'hr') ? 'hr-dashboard.php' : 'employee-dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container { max-width: 500px; margin: auto; padding: 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        .card-header .icon-circle {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .card-header .icon-circle i { font-size: 25px; color: white; }
        .card-header h4 { margin: 0; font-weight: 700; }
        .card-header p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
        .card-body { padding: 2rem; }
        .form-label { font-weight: 600; color: #495057; }
        .form-control { border-radius: 8px; border: 1px solid #ddd; padding: 10px 15px; }
        .form-control:focus { border-color: #2c5aa0; box-shadow: 0 0 0 0.2rem rgba(44,90,160,0.25); }
        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .password-wrapper { position: relative; }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            background: none;
            border: none;
        }
        .password-toggle:hover { color: #2c5aa0; }
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            border-radius: 5px;
            font-size: 14px;
            color: #856404;
        }
        .alert-warning i { margin-right: 8px; }
        .password-requirements {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            padding-left: 5px;
        }
        .requirement-item { display: flex; align-items: center; gap: 8px; padding: 2px 0; }
        .requirement-item i { width: 16px; font-size: 12px; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        .back-link { color: #2c5aa0; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <div class="icon-circle">
                    <i class="fas fa-lock"></i>
                </div>
                <h4><?php echo $force_change ? '🔒 Change Your Password' : 'Change Password'; ?></h4>
                <p><?php echo $force_change ? 'For security, please change your default password.' : 'Update your password'; ?></p>
            </div>
            <div class="card-body">
                <?php if ($force_change): ?>
                <div class="alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Security Alert:</strong> You are using the default password (123456).
                    Please change it now for security reasons.
                </div>
                <?php endif; ?>

                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="current_password" id="currentPassword" placeholder="Enter current password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('currentPassword', 'toggleCurrent')">
                                <i class="fas fa-eye" id="toggleCurrent"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="Enter new password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('newPassword', 'toggleNew')">
                                <i class="fas fa-eye" id="toggleNew"></i>
                            </button>
                        </div>
                        <!-- Password Requirements -->
                        <div class="password-requirements" id="passwordRequirements">
                            <div class="requirement-item" id="reqLength">
                                <i class="fas fa-times-circle text-danger"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement-item" id="reqUpper">
                                <i class="fas fa-times-circle text-danger"></i>
                                <span>At least 1 uppercase letter</span>
                            </div>
                            <div class="requirement-item" id="reqLower">
                                <i class="fas fa-times-circle text-danger"></i>
                                <span>At least 1 lowercase letter</span>
                            </div>
                            <div class="requirement-item" id="reqNumber">
                                <i class="fas fa-times-circle text-danger"></i>
                                <span>At least 1 number</span>
                            </div>
                            <div class="requirement-item" id="reqSpecial">
                                <i class="fas fa-times-circle text-danger"></i>
                                <span>At least 1 special character (!@#$%^&*)</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="confirm_password" id="confirmPassword" placeholder="Confirm new password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', 'toggleConfirm')">
                                <i class="fas fa-eye" id="toggleConfirm"></i>
                            </button>
                        </div>
                        <div id="passwordMatch" class="mt-1" style="font-size: 13px;"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">
                        <i class="fas fa-save me-2"></i>Update Password
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?php echo $dashboard_link; ?>" class="back-link">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            if (input.type === 'password') {
                input.type = 'text';
                toggle.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                toggle.className = 'fas fa-eye';
            }
        }

        // Password strength validation
        document.getElementById('newPassword').addEventListener('input', function() {
            const password = this.value;
            
            const requirements = [
                { id: 'reqLength', test: password.length >= 8 },
                { id: 'reqUpper', test: /[A-Z]/.test(password) },
                { id: 'reqLower', test: /[a-z]/.test(password) },
                { id: 'reqNumber', test: /[0-9]/.test(password) },
                { id: 'reqSpecial', test: /[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password) }
            ];

            requirements.forEach(req => {
                const el = document.getElementById(req.id);
                const icon = el.querySelector('i');
                const span = el.querySelector('span');
                if (req.test) {
                    icon.className = 'fas fa-check-circle text-success';
                    span.style.color = '#28a745';
                } else {
                    icon.className = 'fas fa-times-circle text-danger';
                    span.style.color = '#dc3545';
                }
            });
        });

        // Password match validation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const password = document.getElementById('newPassword').value;
            const confirm = this.value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirm.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirm) {
                matchDiv.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Passwords match';
                matchDiv.style.color = '#28a745';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Passwords do not match';
                matchDiv.style.color = '#dc3545';
            }
        });

        // Form submission
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                current_password: formData.get('current_password'),
                new_password: formData.get('new_password'),
                confirm_password: formData.get('confirm_password')
            };

            // Check if passwords match
            if (data.new_password !== data.confirm_password) {
                alert('✗ Passwords do not match!');
                return;
            }

            // Check password strength
            const password = data.new_password;
            if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || 
                !/[0-9]/.test(password) || !/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/.test(password)) {
                alert('✗ Password does not meet security requirements!');
                return;
            }

            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('api/auth.php?action=change_password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('✅ Password changed successfully!');
                    window.location.href = '<?php echo $dashboard_link; ?>';
                } else {
                    alert('✗ ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Connection error. Please try again.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>

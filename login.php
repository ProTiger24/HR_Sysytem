<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'hr') {
        header('Location: hr-dashboard.php');
    } else {
        header('Location: employee-dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        /* Navbar Style - Same as other pages */
        .navbar {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h4 {
            margin: 0;
            font-weight: 600;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Login Header - Same as Navbar Gradient */
        .login-header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            padding: 30px 20px 25px;
            text-align: center;
            color: white;
            border-radius: 15px 15px 0 0;
            margin: -1px -1px 0 -1px;
        }
        .login-header .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }
        .login-header .icon-circle i {
            font-size: 30px;
            color: white;
        }
        .login-header h3 {
            font-weight: 700;
            margin-bottom: 5px;
            color: white;
        }
        .login-header p {
            color: rgba(255,255,255,0.8);
            margin-bottom: 0;
            font-size: 14px;
        }
        
        .login-container {
            max-width: 480px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-body {
            padding: 2rem;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #2c5aa0;
            box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Security Notice */
        .security-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 13px;
            color: #856404;
        }
        .security-notice i {
            margin-right: 8px;
        }
        
        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }
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
        .password-toggle:hover {
            color: #2c5aa0;
        }
        
        /* Footer Links - Navbar Style */
        .footer-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .footer-links a {
            color: #2c5aa0;
            text-decoration: none;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 6px;
            transition: 0.3s;
            font-size: 14px;
        }
        .footer-links a:hover {
            background: rgba(44, 90, 160, 0.1);
        }
        .footer-links .divider {
            color: #ddd;
        }
        
        .contact-info {
            text-align: center;
            padding: 12px 20px;
            background: #f8f9fa;
        }
        .contact-info small {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h4><i class="fas fa-users me-2"></i>kormoshathi</h4>
        <a href="index.php"><i class="fas fa-home me-1"></i>Home</a>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Login Header - Same as Navbar Gradient -->
            <div class="login-header">
                <div class="icon-circle">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Welcome Back</h3>
                <p>Login to your account</p>
            </div>
            
            <!-- Login Body -->
            <div class="login-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" name="password" id="passwordInput" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Password Security:</strong> Default password is <strong>'123456'</strong>.
                        For security, you will be required to change your password after first login.
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
            
            <!-- Footer Links - Navbar Style (No Register) -->
            <div class="footer-links">
                <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Back to Home</a>
            </div>
            
            <!-- Contact Info -->
            <div class="contact-info">
                <small><i class="fas fa-headset me-1"></i>Contact HR for account credentials</small>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loginData = {
                email: formData.get('email'),
                password: formData.get('password')
            };
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Logging in...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(loginData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (result.force_password_change) {
                        alert('⚠️ Please change your password for security reasons.');
                        window.location.href = 'change-password.php';
                    } else {
                        alert('✓ Login successful! Redirecting...');
                        if (result.user_type === 'hr') {
                            window.location.href = 'hr-dashboard.php';
                        } else {
                            window.location.href = 'employee-dashboard.php';
                        }
                    }
                } else {
                    alert('✗ ' + result.message);
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('Connection error. Please try again.');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
        
        // Enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const form = document.getElementById('loginForm');
                if (document.activeElement === form.querySelector('input')) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
    </script>
</body>
</html>

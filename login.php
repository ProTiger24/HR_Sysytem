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
   <link href="css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="d-flex align-items-center">
                    <img src="../images/officeLogo.avif" alt="KormoShathi" height="40" class="me-2">
                    <span class="brand-text">KormoShathi</span>
                </div>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="register.php">Register</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h3><i class="fas fa-sign-in-alt me-2"></i>Login to KormoShathi</h3>
                <p>Access your HR management portal</p>
            </div>
            <div class="login-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php" class="text-primary">Register here</a></p>
                </div>
                <div class="login-footer text-center mt-4">
                    <div class="fingerprint-login">
                        <p class="text-muted">Or login with fingerprint</p>
                        <div class="fingerprint-scanner-small" id="fingerprintLogin">
                            <i class="fas fa-fingerprint"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const loginData = {
                email: formData.get('email'),
                password: formData.get('password')
            };

            try {
                const response = await fetch('auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(loginData)
                });

                const result = await response.json();
                
                if (response.ok) {
                    hrSystem.showNotification('Login successful! Redirecting...', 'success');
                    
                    // Store user data
                    localStorage.setItem('hr_token', result.token);
                    localStorage.setItem('user_type', result.user_type);
                    localStorage.setItem('user_data', JSON.stringify(result.user_data));
                    
                    setTimeout(() => {
                        if (result.user_type === 'hr') {
                            window.location.href = 'hr-dashboard.php';
                        } else {
                            window.location.href = 'employee-dashboard.php';
                        }
                    }, 1000);
                } else {
                    hrSystem.showNotification(result.message, 'error');
                }
            } catch (error) {
                
                hrSystem.showNotification('Login failed. Please try again.', 'error');
            }
        });

        // Fingerprint login
        document.getElementById('fingerprintLogin').addEventListener('click', async function() {
            const scanner = this;
            scanner.classList.add('scanning');
            
            try {
                const verification = await hrSystem.verifyFingerprint();
                
                if (verification && verification.success) {
                    hrSystem.showNotification('Fingerprint verified! Logging in...', 'success');
                    
                    localStorage.setItem('hr_token', verification.token);
                    localStorage.setItem('user_type', verification.user_type);
                    localStorage.setItem('user_data', JSON.stringify(verification.user_data));
                    
                    setTimeout(() => {
                        if (verification.user_type === 'hr') {
                            window.location.href = 'hr-dashboard.php';
                        } else {
                            window.location.href = 'employee-dashboard.php';
                        }
                    }, 1000);
                } else {
                    hrSystem.showNotification('Fingerprint not recognized', 'error');
                }
            } catch (error) {
                 console.error('Login error:', error);
                hrSystem.showNotification('Fingerprint login failed', 'error');
            } finally {
                scanner.classList.remove('scanning');
            }
        });
    </script>
  

</body>
</html>
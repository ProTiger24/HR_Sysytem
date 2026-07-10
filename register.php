<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            padding: 2rem;
            max-width: 550px;
            margin: 80px auto;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="text-center mb-4">
                <h3><i class="fas fa-user-plus me-2"></i>Create Account</h3>
                <p class="text-muted">Join KormoShathi as Employee or HR</p>
            </div>
            
            <form id="registerForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" name="email" placeholder="example@company.com" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" placeholder="+8801XXXXXXXXX">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department">
                            <option value="">Select Department</option>
                            <option value="IT">IT</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Sales">Sales</option>
                            <option value="Operations">Operations</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" placeholder="e.g., Software Engineer">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Register as *</label>
                    <select class="form-select" name="user_type" required>
                        <option value="employee">Employee</option>
                        <option value="hr">HR Manager</option>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" placeholder="Min 6 characters" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" required>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the Terms & Conditions and Privacy Policy
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }
            
            if (password.length < 6) {
                alert('Password must be at least 6 characters!');
                return;
            }
            
            const registerData = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                department: formData.get('department'),
                position: formData.get('position'),
                password: password,
                user_type: formData.get('user_type')
            };
            
            try {
                const response = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(registerData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✓ Registration successful!\n\nYour Employee ID: ' + result.employee_id + '\n\nPlease login with your email and password.');
                    window.location.href = 'login.php';
                } else {
                    alert('✗ Registration failed: ' + result.message);
                }
            } catch (error) {
                console.error('Registration error:', error);
                alert('Connection error. Please try again.');
            }
        });
    </script>
</body>
</html>
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
    <title>Add Employee - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 700px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 12px; font-weight: 600; border-radius: 8px; transition: 0.3s; color: white; cursor: pointer; width: 100%; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .form-control, .form-select { border-radius: 8px; padding: 10px; border: 1px solid #ddd; width: 100%; }
        .form-control:focus, .form-select:focus { border-color: #2c5aa0; box-shadow: 0 0 0 0.2rem rgba(44,90,160,0.25); }
        .mb-3 { margin-bottom: 15px; }
        .form-label { font-weight: 600; margin-bottom: 5px; display: block; color: #495057; }
        .text-muted { color: #6c757d; font-size: 12px; }
        .row { display: flex; flex-wrap: wrap; gap: 15px; }
        .col-md-6 { flex: 0 0 calc(50% - 15px); }
        .col-md-4 { flex: 0 0 calc(33.33% - 15px); }
        .col-md-12 { flex: 0 0 100%; }
        @media (max-width: 768px) { .col-md-6, .col-md-4, .col-md-12 { flex: 0 0 100%; } }
        .section-title { font-weight: 600; color: #2c5aa0; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; margin-bottom: 15px; }
        .help-text { font-size: 12px; color: #6c757d; margin-top: 3px; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-user-plus me-2"></i>Add New Employee</h4>
    <a href="employees.php"><i class="fas fa-arrow-left me-2"></i>Back to Employees</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Employee Information</h5>
        </div>
        <div class="card-body">
            <form id="addEmployeeForm">
                <!-- Basic Information -->
                <div class="section-title">📋 Basic Information</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" name="first_name" required placeholder="Enter first name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" name="last_name" required placeholder="Enter last name">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address *</label>
                    <input type="email" class="form-control" name="email" required placeholder="Enter email address">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" placeholder="Enter phone number">
                </div>

                <!-- Job Information -->
                <div class="section-title">💼 Job Information</div>
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" class="form-control" name="salary" placeholder="0.00" step="0.01">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="join_date">
                    </div>
                </div>

                <!-- Personal Information (New Fields) -->
                <div class="section-title">🧑‍💼 Personal Information</div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Blood Group</label>
                        <select class="form-select" name="blood_group">
                            <option value="">Select</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" class="form-control" name="emergency_contact" placeholder="Emergency phone number">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="2" placeholder="Current address"></textarea>
                </div>

                <!-- Account Information -->
                <div class="section-title">🔐 Account Information</div>
                <div class="mb-3">
                    <label class="form-label">User Type *</label>
                    <select class="form-select" name="user_type" required>
                        <option value="employee">Employee</option>
                        <option value="hr">HR Manager</option>
                    </select>
                    <small class="help-text">Select "HR Manager" to give HR access to this user</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="text" class="form-control" name="password" value="123456" required>
                    <small class="help-text">Default password: 123456 (User can change later)</small>
                </div>

                <button type="submit" class="btn-primary"><i class="fas fa-save me-2"></i>Add Employee</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('addEmployeeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const employeeData = {
        first_name: formData.get('first_name'),
        last_name: formData.get('last_name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        department: formData.get('department'),
        position: formData.get('position'),
        salary: formData.get('salary'),
        join_date: formData.get('join_date'),
        user_type: formData.get('user_type'),
        password: formData.get('password'),
        blood_group: formData.get('blood_group'),
        emergency_contact: formData.get('emergency_contact'),
        date_of_birth: formData.get('date_of_birth'),
        address: formData.get('address')
    };
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('../api/add-employee.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(employeeData)
        });
        const result = await response.json();
        if (result.success) {
            alert('✓ ' + (result.user_type === 'hr' ? 'HR' : 'Employee') + ' added successfully! ID: ' + result.employee_id);
            window.location.href = 'employees.php';
        } else {
            alert('✗ Error: ' + result.message);
        }
    } catch(error) {
        alert('Connection error! Please try again.');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});
</script>
</body>
</html>

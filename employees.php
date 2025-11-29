<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'hr') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users me-2 fs-4 text-primary"></i>
                    <div>
                        <span class="brand-text fw-bold">KormoShathi</span>
                        <small class="d-block text-muted">HR Portal</small>
                    </div>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a class="nav-link" href="../hr-dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link active" href="employees.php">
                        <i class="fas fa-users me-2"></i>Employee Management
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="attendance.php">
                        <i class="fas fa-fingerprint me-2"></i>Attendance
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="leave.php">
                        <i class="fas fa-calendar-alt me-2"></i>Leave Management
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="payroll.php">
                        <i class="fas fa-money-bill me-2"></i>Payroll
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="performance.php">
                        <i class="fas fa-chart-line me-2"></i>Performance
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="recruitment.php">
                        <i class="fas fa-briefcase me-2"></i>Recruitment
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-nav">
                <div class="nav-title">
                    <h4 class="mb-0">Employee Management</h4>
                    <small class="text-muted">Manage all employee information</small>
                </div>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></div>
                            <div class="user-role">HR Manager</div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <h3>Employee Directory</h3>
                            <p class="text-muted mb-0">Manage all employee records and information</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                            <i class="fas fa-user-plus me-2"></i>Add New Employee
                        </button>
                    </div>
                </div>

                <!-- Employee Statistics -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card primary">
                            <div class="stat-number">47</div>
                            <div class="stat-label">Total Employees</div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card success">
                            <div class="stat-number">42</div>
                            <div class="stat-label">Active Employees</div>
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card warning">
                            <div class="stat-number">5</div>
                            <div class="stat-label">On Probation</div>
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card info">
                            <div class="stat-number">3</div>
                            <div class="stat-label">New This Month</div>
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select" id="departmentFilter">
                                    <option value="">All Departments</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Sales">Sales</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="probation">Probation</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search employees..." id="searchInput">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-outline-secondary w-100" id="resetFilters">
                                    <i class="fas fa-refresh me-2"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">All Employees</h5>
                        <div class="export-buttons">
                            <button class="btn btn-sm btn-outline-success me-2">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Position</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="employeesContainer">
                                    <!-- Employees will be loaded here dynamically -->
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2 mb-0">Loading employees...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <nav aria-label="Employee pagination">
                            <ul class="pagination justify-content-center mt-4">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="text" class="form-control" name="position" placeholder="e.g., Software Engineer">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Join Date</label>
                                    <input type="date" class="form-control" name="join_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Employment Type</label>
                                    <select class="form-select" name="employment_type">
                                        <option value="full-time">Full Time</option>
                                        <option value="part-time">Part Time</option>
                                        <option value="contract">Contract</option>
                                        <option value="intern">Intern</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Salary</label>
                                    <input type="number" class="form-control" name="salary" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active">Active</option>
                                        <option value="probation">Probation</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Enter full address"></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="sendWelcomeEmail">
                            <label class="form-check-label" for="sendWelcomeEmail">
                                Send welcome email with login credentials
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addEmployee()">
                        <i class="fas fa-save me-2"></i>Save Employee
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="notificationContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/app.js"></script>
    <script>
        // Load employees when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployees();
        });

        // Load employees function
        async function loadEmployees() {
            try {
                // Simulate API call - replace with actual API
                const employees = [
                    {
                        id: 1,
                        employee_id: 'EMP2024001',
                        first_name: 'John',
                        last_name: 'Doe',
                        department: 'IT',
                        position: 'Software Engineer',
                        email: 'john.doe@company.com',
                        phone: '+8801710000001',
                        status: 'active',
                        join_date: '2024-01-15'
                    },
                    {
                        id: 2,
                        employee_id: 'EMP2024002',
                        first_name: 'Jane',
                        last_name: 'Smith',
                        department: 'Marketing',
                        position: 'Marketing Manager',
                        email: 'jane.smith@company.com',
                        phone: '+8801710000002',
                        status: 'active',
                        join_date: '2024-01-10'
                    },
                    {
                        id: 3,
                        employee_id: 'EMP2024003',
                        first_name: 'Michael',
                        last_name: 'Brown',
                        department: 'Finance',
                        position: 'Accountant',
                        email: 'michael.brown@company.com',
                        phone: '+8801710000003',
                        status: 'probation',
                        join_date: '2024-02-01'
                    },
                    {
                        id: 4,
                        employee_id: 'EMP2024004',
                        first_name: 'Sarah',
                        last_name: 'Johnson',
                        department: 'HR',
                        position: 'HR Executive',
                        email: 'sarah.johnson@company.com',
                        phone: '+8801710000004',
                        status: 'active',
                        join_date: '2024-01-20'
                    }
                ];

                const container = document.getElementById('employeesContainer');
                
                if (employees.length > 0) {
                    container.innerHTML = employees.map(emp => `
                        <tr>
                            <td>
                                <strong>${emp.employee_id}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar-small me-3">
                                        ${emp.first_name[0]}${emp.last_name[0]}
                                    </div>
                                    <div>
                                        <strong>${emp.first_name} ${emp.last_name}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">${emp.department}</span>
                            </td>
                            <td>${emp.position}</td>
                            <td>${emp.email}</td>
                            <td>${emp.phone}</td>
                            <td>
                                <span class="badge bg-${getStatusBadge(emp.status)}">
                                    ${emp.status}
                                </span>
                            </td>
                            <td>${new Date(emp.join_date).toLocaleDateString()}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewEmployee(${emp.id})" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" onclick="editEmployee(${emp.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${emp.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-users fs-1 text-muted mb-3"></i>
                                <p class="mb-0">No employees found</p>
                                <small class="text-muted">Add your first employee to get started</small>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                hrSystem.showNotification('Failed to load employees', 'error');
            }
        }

        function getStatusBadge(status) {
            switch(status) {
                case 'active': return 'success';
                case 'probation': return 'warning';
                case 'inactive': return 'danger';
                default: return 'secondary';
            }
        }

        // Add employee function
        async function addEmployee() {
            const form = document.getElementById('addEmployeeForm');
            const formData = new FormData(form);

            const employeeData = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                department: formData.get('department'),
                position: formData.get('position'),
                join_date: formData.get('join_date'),
                employment_type: formData.get('employment_type'),
                salary: formData.get('salary'),
                status: formData.get('status'),
                address: formData.get('address')
            };

            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                hrSystem.showNotification('Employee added successfully!', 'success');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
                modal.hide();
                
                // Reset form
                form.reset();
                
                // Reload employees
                loadEmployees();
                
            } catch (error) {
                console.error('Error adding employee:', error);
                hrSystem.showNotification('Failed to add employee', 'error');
            }
        }

        // Employee actions
        function viewEmployee(id) {
            hrSystem.showNotification(`Viewing employee details for ID: ${id}`, 'info');
            // Implement view functionality
        }

        function editEmployee(id) {
            hrSystem.showNotification(`Editing employee with ID: ${id}`, 'info');
            // Implement edit functionality
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
                hrSystem.showNotification(`Employee ${id} deleted successfully`, 'success');
                // Implement delete functionality
            }
        }

        // Filter functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterEmployees(searchTerm);
        });

        document.getElementById('departmentFilter').addEventListener('change', filterEmployees);
        document.getElementById('statusFilter').addEventListener('change', filterEmployees);

        function filterEmployees() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const departmentFilter = document.getElementById('departmentFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            // Implement filtering logic here
            console.log('Filtering employees:', { searchTerm, departmentFilter, statusFilter });
        }

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('departmentFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterEmployees();
        });
    </script>
    <script src="../js/app.js"></script>
</body>
</html>
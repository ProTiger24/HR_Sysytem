// HR Management System Main Class
class HRManagementSystem {
    constructor() {
        this.apiBase = 'api/';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthentication();
        this.loadFingerprintJS();
        console.log('HR System Initialized');
    }

    setupEventListeners() {
        // Navigation


        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.target.getAttribute('href') || e.target.getAttribute('data-page');
                if (page) {
                    this.navigateTo(page);
                }
            });
        });

        // Logout
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        // Auto-save forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('input', this.debounce(() => {
                this.autoSaveForm(form);
            }, 1000));
        });
    }

    navigateTo(page) {
        window.location.href = page;
    }

    async checkAuthentication() {
        const token = localStorage.getItem('hr_token');
        const currentPage = window.location.pathname.split('/').pop();

        // Pages that don't require authentication
        const publicPages = ['index.php', 'login.php', 'register.php', ''];

        if (!token && !publicPages.includes(currentPage)) {
            window.location.href = 'login.php';
            return;
        }

        if (token && (currentPage === 'login.php' || currentPage === 'register.php')) {
            const userType = localStorage.getItem('user_type');
            try {
                const valid = await this.verifyToken();
                if (valid) {
                    if (userType === 'hr') {
                        window.location.href = 'hr-dashboard.php';
                    } else {
                        window.location.href = 'employee-dashboard.php';
                    }
                } else {
                    this.logout();
                }
            } catch (error) {
                this.logout();
            }
        }
    }

    async verifyToken() {
        try {
            const response = await this.apiCall('auth.php?action=verify');
            return response && response.success;
        } catch (error) {
            return false;
        }
    }

    async apiCall(endpoint, method = 'GET', data = null) {
        try {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('hr_token')
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(this.apiBase + endpoint, options);

            if (!response.ok) {
                if (response.status === 401) {
                    this.logout();
                    throw new Error('Session expired. Please login again.');
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentLength = response.headers.get('content-length');
            if (contentLength === '0') {
                return { success: true, message: 'Operation completed successfully' };
            }

            return await response.json();
        } catch (error) {
            console.error('API Call error:', error);
            this.showNotification(error.message || 'Error connecting to server', 'error');
            return null;
        }
    }

    showNotification(message, type = 'info') {
        let container = document.getElementById('notificationContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notificationContainer';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }

        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            margin-bottom: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        `;

        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };

        notification.innerHTML = `
            <i class="fas ${icons[type] || 'fa-info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        container.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    async loadFingerprintJS() {
        console.log('FingerprintJS loaded');
    }

    async registerFingerprint(employeeId) {
        try {
            await new Promise(resolve => setTimeout(resolve, 2000));
            const fingerprintData = 'fingerprint_' + Math.random().toString(36).substr(2, 9);

            const response = await this.apiCall('fingerprint.php', 'POST', {
                employee_id: employeeId,
                fingerprint_data: fingerprintData
            });

            return response;
        } catch (error) {
            console.error('Fingerprint registration failed:', error);
            this.showNotification('Fingerprint registration failed', 'error');
            return null;
        }
    }

    async verifyFingerprint() {
        try {
            await new Promise(resolve => setTimeout(resolve, 2000));

            return {
                success: true,
                employee_id: 1,
                user_type: 'employee',
                token: 'demo_token_' + Date.now(),
                user_data: {
                    id: 1,
                    employee_id: 'EMP001',
                    first_name: 'Demo',
                    last_name: 'User',
                    email: 'demo@kormoshathi.com'
                }
            };
        } catch (error) {
            console.error('Fingerprint verification failed:', error);
            return null;
        }
    }

    logout() {
        if (!confirm('Are you sure you want to logout?')) {
            return;
        }

        localStorage.removeItem('hr_token');
        localStorage.removeItem('user_type');
        localStorage.removeItem('user_data');
        sessionStorage.clear();

        fetch('api/auth.php?action=logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        }).finally(() => {
            window.location.href = 'login.php';
        });
    }

    // Utility Methods
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    formatDateTime(date) {
        return new Date(date).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-BD', {
            style: 'currency',
            currency: 'BDT'
        }).format(amount);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    // Validation Methods
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhone(phone) {
        const phoneRegex = /^[0-9+\-\s()]{10,}$/;
        return phoneRegex.test(phone);
    }

    validatePassword(password) {
        return password.length >= 6;
    }

    sanitizeInput(input) {
        const div = document.createElement('div');
        div.textContent = input;
        return div.innerHTML;
    }

    // Storage Methods
    setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    getStorage(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return null;
        }
    }

    clearStorage() {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.error('Error clearing localStorage:', error);
            return false;
        }
    }

    // Auto-save form data
    autoSaveForm(form) {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        this.setStorage(`form_${form.id}_autosave`, data);
    }

    loadFormAutoSave(form) {
        const saved = this.getStorage(`form_${form.id}_autosave`);
        if (saved) {
            Object.keys(saved).forEach(key => {
                const element = form.querySelector(`[name="${key}"]`);
                if (element) {
                    element.value = saved[key];
                }
            });
        }
    }

    clearFormAutoSave(form) {
        localStorage.removeItem(`form_${form.id}_autosave`);
    }

    // Employee Management
    async loadEmployees() {
        try {
            const employees = await this.apiCall('employees.php');
            const container = document.getElementById('employeesContainer');

            if (employees && container) {
                container.innerHTML = employees.map(emp => `
                    <tr>
                        <td>${emp.employee_id}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar-small me-3">
                                    ${emp.first_name[0]}${emp.last_name[0]}
                                </div>
                                <div>
                                    <strong>${emp.first_name} ${emp.last_name}</strong>
                                    <br><small class="text-muted">${emp.position}</small>
                                </div>
                            </div>
                        </td>
                        <td>${emp.department || 'N/A'}</td>
                        <td>${emp.email}</td>
                        <td>${emp.phone || 'N/A'}</td>
                        <td>
                            <span class="badge bg-${emp.status === 'active' ? 'success' : 'danger'}">
                                ${emp.status}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEmployee(${emp.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="editEmployee(${emp.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${emp.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading employees:', error);
        }
    }

    // Attendance Management
    async markAttendance(employeeId, type = 'check_in') {
        try {
            const result = await this.apiCall('attendance.php', 'POST', {
                employee_id: employeeId,
                type: type
            });

            if (result) {
                this.showNotification(result.message, 'success');
                this.loadTodayAttendance();
            }
        } catch (error) {
            console.error('Error marking attendance:', error);
            this.showNotification('Failed to mark attendance', 'error');
        }
    }

    async loadTodayAttendance() {
        try {
            const attendance = await this.apiCall('attendance.php');
            const container = document.getElementById('todayAttendance');

            if (attendance && container) {
                container.innerHTML = attendance.map(record => `
                    <tr>
                        <td>${record.first_name} ${record.last_name}</td>
                        <td>${record.check_in || '-'}</td>
                        <td>${record.check_out || '-'}</td>
                        <td>
                            <span class="badge bg-${record.status === 'present' ? 'success' : 'warning'}">
                                ${record.status}
                            </span>
                        </td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading attendance:', error);
        }
    }

    // Dashboard Stats
    async loadDashboardStats() {
        try {
            const stats = await this.apiCall('dashboard.php');

            if (stats) {
                document.getElementById('totalEmployees').textContent = stats.totalEmployees || 0;
                document.getElementById('presentToday').textContent = stats.presentToday || 0;
                document.getElementById('onLeave').textContent = stats.onLeave || 0;
                document.getElementById('pendingLeaves').textContent = stats.pendingLeaves || 0;
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            // Set default values
            document.getElementById('totalEmployees').textContent = '0';
            document.getElementById('presentToday').textContent = '0';
            document.getElementById('onLeave').textContent = '0';
            document.getElementById('pendingLeaves').textContent = '0';
        }
    }
}

// Global HR System Instance
const hrSystem = new HRManagementSystem();

// Dashboard Functions
async function loadDashboardStats() {
    try {
        const stats = {
            totalEmployees: 47,
            presentToday: 42,
            onLeave: 5,
            pendingLeaves: 3
        };

        document.getElementById('totalEmployees').textContent = stats.totalEmployees;
        document.getElementById('presentToday').textContent = stats.presentToday;
        document.getElementById('onLeave').textContent = stats.onLeave;
        document.getElementById('pendingLeaves').textContent = stats.pendingLeaves;

    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

// Employee Functions
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
        password: 'default123'
    };

    try {
        const response = await fetch('api/employees.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(employeeData)
        });

        const result = await response.json();

        if (response.ok) {
            hrSystem.showNotification('Employee added successfully!', 'success');
            form.reset();
            const modal = bootstrap.Modal.getInstance(document.getElementById('addEmployeeModal'));
            if (modal) modal.hide();
            hrSystem.loadEmployees();
        } else {
            hrSystem.showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error adding employee:', error);
        hrSystem.showNotification('Failed to add employee', 'error');
    }
}

// Attendance Functions
async function markAttendance() {
    const scanner = document.getElementById('fingerprintScanner');
    const resultDiv = document.getElementById('attendanceResult');

    if (scanner) scanner.classList.add('scanning');
    if (resultDiv) resultDiv.innerHTML = '<div class="alert alert-info">Scanning fingerprint...</div>';

    try {
        const verification = await hrSystem.verifyFingerprint();

        if (verification && verification.success) {
            await hrSystem.markAttendance(verification.employee_id, 'check_in');
            if (resultDiv) {
                resultDiv.innerHTML = '<div class="alert alert-success">Attendance marked successfully!</div>';
            }
        } else {
            if (resultDiv) {
                resultDiv.innerHTML = '<div class="alert alert-danger">Fingerprint not recognized</div>';
            }
        }
    } catch (error) {
        if (resultDiv) {
            resultDiv.innerHTML = '<div class="alert alert-danger">Failed to mark attendance</div>';
        }
    } finally {
        if (scanner) scanner.classList.remove('scanning');
    }
}

// Utility Functions
function viewEmployee(id) {
    hrSystem.showNotification(`Viewing employee ${id}`, 'info');
    // Implement view employee functionality
}

function editEmployee(id) {
    hrSystem.showNotification(`Editing employee ${id}`, 'info');
    // Implement edit employee functionality
}

function deleteEmployee(id) {
    if (confirm('Are you sure you want to delete this employee?')) {
        hrSystem.showNotification(`Employee ${id} deleted`, 'success');
        // Implement delete employee functionality
    }
}

// Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Smooth scrolling
function initializeSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Page initialization
function initializePage() {
    const currentPage = window.location.pathname.split('/').pop();

    initializeFormValidation();
    initializeSmoothScroll();

    switch (currentPage) {
        case 'hr-dashboard.php':
            hrSystem.loadDashboardStats();
            break;
        case 'employee-dashboard.php':
            // Load employee dashboard data
            loadEmployeeDashboard();
            break;
        case 'employees.php':
            hrSystem.loadEmployees();
            break;
        case 'attendance.php':
            hrSystem.loadTodayAttendance();
            break;
        case 'login.php':
        case 'register.php':
            // Load form autosave
            const form = document.querySelector('form');
            if (form) {
                hrSystem.loadFormAutoSave(form);
            }
            break;
    }
}

// Employee Dashboard Functions
async function loadEmployeeDashboard() {
    try {
        // Load attendance status
        const attendanceResponse = await fetch('api/attendance.php?action=today_status');
        const attendanceData = await attendanceResponse.json();

        if (attendanceData.success) {
            document.getElementById('attendanceStatus').textContent = attendanceData.status;
        }

        // Load leave balance
        const leaveResponse = await fetch('api/leaves.php?action=balance');
        const leaveData = await leaveResponse.json();

        if (leaveData.success) {
            document.getElementById('leaveBalance').textContent = leaveData.balance;
        }

        loadRecentActivity();

    } catch (error) {
        console.error('Error loading dashboard:', error);
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('api/employee.php?action=recent_activity');
        const data = await response.json();

        const container = document.getElementById('recentActivity');

        if (data.success && data.activities.length > 0) {
            container.innerHTML = data.activities.map(activity => `
                <div class="activity-item mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between">
                        <span>${activity.description}</span>
                        <small class="text-muted">${activity.time}</small>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-muted">No recent activity</p>';
        }
    } catch (error) {
        console.error('Error loading recent activity:', error);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializePage();

    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HRManagementSystem, hrSystem };
}

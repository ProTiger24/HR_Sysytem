<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employee') {
    header('Location: login.php');
    exit;
}

// Get profile picture from database
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare("SELECT profile_picture FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && $user['profile_picture']) {
    $_SESSION['profile_picture'] = $user['profile_picture'];
}

// Get unread notifications count for badge
$stmt = $db->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; }
        .btn-outline-light { background: transparent; border: 1px solid white; padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .btn-outline-light:hover { background: white; color: #2c5aa0; }
        .btn-danger { background: #dc3545; border: none; padding: 8px 16px; border-radius: 8px; }
        .btn-danger:hover { background: #c82333; }
        .btn-outline-success { background: transparent; border: 1px solid #28a745; color: #28a745; }
        .btn-outline-success:hover { background: #28a745; color: white; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .profile-card { background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; display: block; }
        .avatar-placeholder { width: 100px; height: 100px; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: white; margin: 0 auto 15px; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .bg-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .bg-info { background: linear-gradient(135deg, #17a2b8, #0dcaf0); }
        .text-white { color: white; }
        .btn-attendance { padding: 12px 30px; font-size: 16px; border-radius: 50px; margin: 10px; border: none; cursor: pointer; transition: 0.3s; }
        .btn-attendance:hover { transform: scale(1.05); }
        .fingerprint {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto 0;
            cursor: pointer;
            color: white;
            font-size: 40px;
            transition: 0.3s;
            border: 4px solid transparent;
        }
        .fingerprint:hover { transform: scale(1.1); border-color: #28a745; }
        .scanning { animation: pulse 1s infinite; border-color: #ffc107; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        .row { display: flex; flex-wrap: wrap; gap: 20px; }
        .col-4 { flex: 0 0 calc(33.33% - 20px); }
        .col-8 { flex: 0 0 calc(66.66% - 20px); }
        .col-6 { flex: 0 0 calc(50% - 20px); }
        @media (max-width: 768px) { .col-4, .col-8, .col-6 { flex: 0 0 100%; } }
        .me-2 { margin-right: 10px; }
        .me-1 { margin-right: 5px; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn-outline-primary { background: transparent; border: 1px solid #2c5aa0; color: #2c5aa0; }
        .btn-outline-primary:hover { background: #2c5aa0; color: white; }
        .text-muted { color: #6c757d; }
        .notification-badge { 
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 50%;
            background: #dc3545;
            color: white;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }
        .notification-bell {
            position: relative;
            display: inline-block;
        }
        .activity-item {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid #2c5aa0;
            background: #f8f9fa;
        }
        .activity-item:hover { transform: translateX(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .activity-item.attendance { border-left-color: #28a745; background: #f0fff4; }
        .activity-item.leave-pending { border-left-color: #ffc107; background: #fffbf0; }
        .activity-item.leave-approved { border-left-color: #28a745; background: #f0fff4; }
        .activity-item.leave-rejected { border-left-color: #dc3545; background: #fff5f5; }
        .activity-item.performance { border-left-color: #6f42c1; background: #f8f0ff; }
        .activity-item.notice { border-left-color: #17a2b8; background: #f0f9ff; }
        .activity-item.notification { border-left-color: #fd7e14; background: #fff8f0; }
        .activity-text { flex: 1; font-size: 14px; color: #333; }
        .activity-icon { font-size: 18px; margin-right: 12px; min-width: 30px; text-align: center; }
        .activity-time { font-size: 12px; color: #6c757d; white-space: nowrap; margin-left: 15px; background: white; padding: 2px 12px; border-radius: 20px; }
        .activity-status-badge { font-size: 11px; padding: 2px 10px; border-radius: 20px; margin-right: 10px; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .navbar-buttons { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .btn-outline-light { font-size: 14px; }
        
        /* Fingerprint Modal */
        .fingerprint-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .fingerprint-modal.active {
            display: flex;
        }
        .fingerprint-modal .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .fingerprint-modal .modal-content .fingerprint-icon {
            font-size: 80px;
            color: #2c5aa0;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }
        .fingerprint-modal .modal-content .status-text {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .fingerprint-modal .modal-content .sub-text {
            font-size: 14px;
            color: #6c757d;
        }
        .fingerprint-modal .modal-content .btn-close-modal {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h4><i class="fas fa-user-tie me-2"></i>Employee Portal</h4>
        <div class="navbar-buttons">
            <a href="#" class="btn btn-outline-light me-1" onclick="toggleNotifications(event)" title="Notifications">
                <span class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="noticeBadge" style="<?php echo $unread_count > 0 ? 'display:inline;' : 'display:none;'; ?>"><?php echo $unread_count; ?></span>
                </span>
            </a>
            <a href="modules/submit-work.php" class="btn btn-outline-light me-1">
                <i class="fas fa-upload me-1"></i>Submit Work
            </a>
            <a href="modules/leave.php" class="btn btn-outline-light me-1">
                <i class="fas fa-calendar-alt me-1"></i>Leave
            </a>
            <a href="modules/performance.php" class="btn btn-outline-success me-1">
                <i class="fas fa-chart-line me-1"></i>Performance
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-4">
                <div class="profile-card">
                    <?php if(isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture']) && file_exists('/opt/lampp/htdocs/kormoshathi/' . $_SESSION['profile_picture'])): ?>
                        <img src="/kormoshathi/<?php echo $_SESSION['profile_picture']; ?>" class="avatar" id="profileImage">
                    <?php else: ?>
                        <div class="avatar-placeholder" id="profileImage">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <h4><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h4>
                    <p class="text-muted"><?php echo $_SESSION['position'] ?? 'Employee'; ?></p>
                    <hr>
                    <p><strong>Employee ID:</strong> <?php echo $_SESSION['employee_id']; ?></p>
                    <p><strong>Department:</strong> <?php echo $_SESSION['department'] ?? 'N/A'; ?></p>
                    <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                    <a href="profile.php" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-camera me-1"></i>Update Photo
                    </a>
                </div>
            </div>
            <div class="col-8">
                <div class="row">
                    <div class="col-6">
                        <div class="stat-card bg-success text-white text-center">
                            <h5>Today's Status</h5>
                            <h2 id="attendanceStatus">Not Checked In</h2>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card bg-info text-white text-center">
                            <h5>Leave Balance</h5>
                            <h2 id="leaveBalance">20</h2>
                        </div>
                    </div>
                </div>
                
                <!-- Mark Attendance with Fingerprint -->
                <div class="stat-card text-center">
                    <h5><i class="fas fa-fingerprint me-2"></i>Mark Attendance</h5>
                    <div class="row">
                        <div class="col-6">
                            <button class="btn-attendance btn-success" onclick="markAttendance('check_in')">
                                <i class="fas fa-sign-in-alt me-2"></i>Check In
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn-attendance btn-warning" onclick="markAttendance('check_out')">
                                <i class="fas fa-sign-out-alt me-2"></i>Check Out
                            </button>
                        </div>
                    </div>
                    <div class="fingerprint" id="fingerprintScanner" onclick="openFingerprintModal()">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <small class="text-muted mt-2 d-block">Click fingerprint to scan</small>
                </div>
                
                <!-- Recent Activity -->
                <div class="stat-card">
                    <div class="activity-header">
                        <h5><i class="fas fa-history me-2 text-primary"></i>Recent Activity (Last 24 Hours)</h5>
                        <span class="activity-count" id="activityCount">0 activities</span>
                    </div>
                    <div id="recentActivity"><p class="text-muted text-center py-3">No recent activity</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fingerprint Modal -->
    <div class="fingerprint-modal" id="fingerprintModal">
        <div class="modal-content">
            <div class="fingerprint-icon">
                <i class="fas fa-fingerprint" id="fingerprintIcon"></i>
            </div>
            <div class="status-text" id="fingerprintStatus">Place your finger</div>
            <div class="sub-text" id="fingerprintSub">Use your device's fingerprint scanner</div>
            <div id="fingerprintResult" class="mt-2"></div>
            <button class="btn btn-secondary btn-close-modal" onclick="closeFingerprintModal()">Close</button>
        </div>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-bell me-2"></i>Notifications</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="notificationList">
                    <p class="text-muted text-center py-3">Loading notifications...</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary" onclick="markAllAsReadAndClose()">
                        <i class="fas fa-check-double me-1"></i>OK (Mark All as Read)
                    </button>
                    <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let realtimeInterval;
        let notificationModal;
        let fingerprintScanning = false;

        document.addEventListener('DOMContentLoaded', function() {
            notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
        });

        // ============================================
        // FINGERPRINT ATTENDANCE
        // ============================================
        function openFingerprintModal() {
            document.getElementById('fingerprintModal').classList.add('active');
            document.getElementById('fingerprintStatus').textContent = 'Scanning...';
            document.getElementById('fingerprintSub').textContent = 'Please place your finger on the scanner';
            document.getElementById('fingerprintResult').innerHTML = '';
            document.getElementById('fingerprintIcon').style.color = '#ffc107';
            
            // Simulate fingerprint scanning
            fingerprintScanning = true;
            setTimeout(() => {
                if (fingerprintScanning) {
                    // Simulate successful scan
                    document.getElementById('fingerprintStatus').textContent = '✅ Fingerprint Matched!';
                    document.getElementById('fingerprintSub').textContent = 'Attendance marked successfully';
                    document.getElementById('fingerprintIcon').style.color = '#28a745';
                    document.getElementById('fingerprintResult').innerHTML = 
                        '<div class="alert alert-success">✅ Checked In at ' + new Date().toLocaleTimeString() + '</div>';
                    
                    // Actually mark attendance
                    markAttendance('check_in');
                    fingerprintScanning = false;
                    
                    setTimeout(() => {
                        closeFingerprintModal();
                    }, 2000);
                }
            }, 3000);
        }

        function closeFingerprintModal() {
            fingerprintScanning = false;
            document.getElementById('fingerprintModal').classList.remove('active');
        }

        // ============================================
        // ATTENDANCE FUNCTIONS
        // ============================================
        async function markAttendance(type) {
            try {
                const response = await fetch('api/attendance.php', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' }, 
                    body: JSON.stringify({ type: type }) 
                });
                const result = await response.json();
                
                if (!result.success) {
                    alert(result.message);
                }
                
                if (result.success) {
                    loadDashboardData();
                    loadLeaveBalance();
                    loadRecentActivity();
                }
            } catch(e) { 
                console.error('Error marking attendance:', e);
            }
        }

        // ============================================
        // DASHBOARD FUNCTIONS
        // ============================================
        async function loadDashboardData() {
            try {
                const attResponse = await fetch('api/attendance.php?action=today_status');
                const attData = await attResponse.json();
                if (attData.success) {
                    document.getElementById('attendanceStatus').innerHTML = attData.status;
                }
            } catch(e) { 
                console.error('Error loading dashboard:', e); 
            }
        }

        async function loadLeaveBalance() {
            try {
                const response = await fetch('api/leave-balance.php');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('leaveBalance').innerHTML = data.balance;
                }
            } catch(e) {
                console.error('Error loading leave balance:', e);
            }
        }

        // ============================================
        // RECENT ACTIVITY
        // ============================================
        async function loadRecentActivity() {
            try {
                const response = await fetch('api/employee-activity.php');
                const data = await response.json();
                const container = document.getElementById('recentActivity');
                const countElement = document.getElementById('activityCount');

                if(data.success && data.activities && data.activities.length > 0) {
                    countElement.textContent = data.activities.length + ' activities';
                    container.innerHTML = data.activities.map(act => {
                        let icon = 'fa-clock';
                        let styleClass = '';
                        let statusBadge = '';

                        if (act.type === 'attendance') {
                            icon = 'fa-fingerprint';
                            styleClass = 'attendance';
                        } else if (act.type === 'leave') {
                            if (act.description.includes('Approved')) {
                                icon = 'fa-check-circle';
                                styleClass = 'leave-approved';
                                statusBadge = '<span class="activity-status-badge bg-success text-white">Approved</span>';
                            } else if (act.description.includes('Rejected')) {
                                icon = 'fa-times-circle';
                                styleClass = 'leave-rejected';
                                statusBadge = '<span class="activity-status-badge bg-danger text-white">Rejected</span>';
                            } else {
                                icon = 'fa-clock';
                                styleClass = 'leave-pending';
                                statusBadge = '<span class="activity-status-badge bg-warning text-dark">Pending</span>';
                            }
                        } else if (act.type === 'performance') {
                            icon = 'fa-star';
                            styleClass = 'performance';
                        } else if (act.type === 'notice') {
                            icon = 'fa-bullhorn';
                            styleClass = 'notice';
                        } else if (act.type === 'notification') {
                            icon = 'fa-bell';
                            styleClass = 'notification';
                        }

                        return `
                            <div class="activity-item ${styleClass}">
                                <div class="d-flex align-items-center" style="flex:1;">
                                    <span class="activity-icon">
                                        <i class="fas ${icon}"></i>
                                    </span>
                                    <span class="activity-text">
                                        ${act.description}
                                        ${statusBadge}
                                    </span>
                                </div>
                                <span class="activity-time">${act.time}</span>
                            </div>
                        `;
                    }).join('');
                } else {
                    countElement.textContent = '0 activities';
                    container.innerHTML = '<p class="text-muted text-center py-3">No recent activity in the last 24 hours</p>';
                }
            } catch(e) {
                console.error('Error loading activity:', e);
                document.getElementById('recentActivity').innerHTML = '<p class="text-muted text-center py-3">Error loading activity</p>';
            }
        }

        // ============================================
        // NOTIFICATIONS
        // ============================================
        async function loadNotificationCount() {
            try {
                const response = await fetch('api/notifications.php?count=1');
                const data = await response.json();
                const badge = document.getElementById('noticeBadge');
                if(data.success && data.unread > 0) {
                    badge.style.display = 'inline';
                    badge.textContent = data.unread;
                } else {
                    badge.style.display = 'none';
                }
            } catch(e) {
                console.error('Error loading notification count:', e);
            }
        }

        async function toggleNotifications(event) {
            event.preventDefault();
            await loadNotifications();
            notificationModal.show();
        }

        async function loadNotifications() {
            try {
                const response = await fetch('api/notifications.php');
                const data = await response.json();
                const container = document.getElementById('notificationList');

                const badge = document.getElementById('noticeBadge');
                if(data.success && data.unread > 0) {
                    badge.style.display = 'inline';
                    badge.textContent = data.unread;
                } else {
                    badge.style.display = 'none';
                }

                if(data.success && data.data && data.data.length > 0) {
                    container.innerHTML = data.data.map(n => {
                        let isRead = n.is_read ? 'read' : 'unread';
                        let icon = 'fa-info-circle';
                        let iconColor = '#2c5aa0';
                        let styleClass = 'info';

                        if (n.type === 'info') {
                            icon = 'fa-info-circle';
                            iconColor = '#2c5aa0';
                            styleClass = 'info';
                        } else if (n.type === 'success') {
                            icon = 'fa-check-circle';
                            iconColor = '#28a745';
                            styleClass = 'success';
                        } else if (n.type === 'danger') {
                            icon = 'fa-exclamation-circle';
                            iconColor = '#dc3545';
                            styleClass = 'danger';
                        } else if (n.type === 'warning') {
                            icon = 'fa-exclamation-triangle';
                            iconColor = '#ffc107';
                            styleClass = 'warning';
                        }

                        if (n.title.includes('Leave')) {
                            if (n.message.includes('approved')) {
                                styleClass = 'leave-approved';
                            } else if (n.message.includes('rejected')) {
                                styleClass = 'leave-rejected';
                            } else {
                                styleClass = 'leave-pending';
                            }
                        }

                        return `
                            <div class="notification-item ${isRead} ${styleClass}" onclick="markAsRead(${n.id})">
                                <div class="d-flex align-items-start">
                                    <span class="notification-icon-modal">
                                        <i class="fas ${icon}" style="color: ${iconColor};"></i>
                                    </span>
                                    <div class="flex-grow-1">
                                        <strong>${n.title}</strong>
                                        <p class="mb-0">${n.message}</p>
                                        <small class="notification-time">${new Date(n.created_at).toLocaleString()}</small>
                                    </div>
                                    ${!n.is_read ? '<span class="badge bg-primary ms-2">New</span>' : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash" style="font-size: 48px; color: #dee2e6;"></i>
                            <p class="text-muted mt-3">No notifications</p>
                        </div>
                    `;
                }
            } catch(e) {
                console.error('Error loading notifications:', e);
                document.getElementById('notificationList').innerHTML = '<p class="text-muted text-center py-3">Error loading notifications</p>';
            }
        }

        async function markAsRead(id) {
            try {
                await fetch('api/notifications.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                await loadNotifications();
                await loadNotificationCount();
            } catch(e) {
                console.error('Error marking as read:', e);
            }
        }

        async function markAllAsReadAndClose() {
            try {
                await fetch('api/notifications.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: 0 })
                });
                await loadNotifications();
                await loadNotificationCount();
                notificationModal.hide();
            } catch(e) {
                console.error('Error marking all as read:', e);
            }
        }

        // ============================================
        // START REALTIME UPDATES
        // ============================================
        function startRealtimeUpdates() { 
            loadDashboardData();
            loadLeaveBalance();
            loadRecentActivity();
            loadNotificationCount();
            realtimeInterval = setInterval(() => {
                loadDashboardData();
                loadLeaveBalance();
                loadRecentActivity();
                loadNotificationCount();
            }, 5000);
        }

        startRealtimeUpdates();
    </script>
</body>
</html>

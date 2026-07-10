<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard - KormoShathi</title>
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
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .profile-card { background: white; border-radius: 15px; padding: 25px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; display: block; }
        .avatar-placeholder { width: 100px; height: 100px; background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: white; margin: 0 auto 15px; }
        .stat-card { background: white; border-radius: 15px; padding: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .stat-number { font-size: 36px; font-weight: bold; margin-bottom: 10px; }
        .row { display: flex; flex-wrap: wrap; gap: 20px; }
        .col-4 { flex: 0 0 calc(33.33% - 20px); }
        .col-8 { flex: 0 0 calc(66.66% - 20px); }
        .col-3 { flex: 0 0 calc(25% - 20px); }
        @media (max-width: 768px) { .col-4, .col-8, .col-3 { flex: 0 0 100%; } }
        .bg-primary { background: linear-gradient(135deg, #2c5aa0, #1e3d72); color: white; }
        .bg-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .bg-warning { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .bg-danger { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .btn { padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; transition: 0.3s; text-decoration: none; display: inline-block; text-align: center; }
        .btn-primary { background: #2c5aa0; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn:hover { transform: scale(1.05); }
        .w-100 { width: 100%; }
        .me-2 { margin-right: 10px; }
        .me-1 { margin-right: 5px; }
        .mt-2 { margin-top: 10px; }
        .text-muted { color: #6c757d; }
        .btn-outline-primary { background: transparent; border: 1px solid #2c5aa0; color: #2c5aa0; }
        .btn-outline-primary:hover { background: #2c5aa0; color: white; }
        .notification-badge { position: relative; top: -8px; right: 8px; font-size: 10px; padding: 2px 6px; }
        
        .navbar-buttons { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
        }
        .quick-actions-grid .btn { font-size: 13px; padding: 10px 8px; }
        
        .notification-item {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid #2c5aa0;
            background: #f8f9fa;
            cursor: pointer;
        }
        .notification-item:hover { transform: translateX(5px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .notification-item.unread { background: #e3f2fd; border-left-color: #2196f3; }
        .notification-item.read { background: #f8f9fa; border-left-color: #28a745; }
        .notification-item.leave-pending { border-left-color: #ffc107; background: #fffbf0; }
        .notification-item.leave-approved { border-left-color: #28a745; background: #f0fff4; }
        .notification-item.leave-rejected { border-left-color: #dc3545; background: #fff5f5; }
        .notification-item.info { border-left-color: #2c5aa0; background: #f0f4ff; }
        .notification-item.success { border-left-color: #28a745; background: #f0fff4; }
        .notification-item.danger { border-left-color: #dc3545; background: #fff5f5; }
        .notification-item.warning { border-left-color: #ffc107; background: #fffbf0; }
        .notification-text { flex: 1; font-size: 14px; color: #333; display: flex; align-items: center; gap: 10px; }
        .notification-icon { font-size: 18px; min-width: 30px; text-align: center; }
        .notification-time { font-size: 12px; color: #6c757d; white-space: nowrap; margin-left: 15px; background: white; padding: 2px 12px; border-radius: 20px; }
        .notification-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e9ecef; }
        .notification-header h5 { margin: 0; color: #2c5aa0; }
        .notification-count { font-size: 13px; color: #6c757d; background: #e9ecef; padding: 2px 12px; border-radius: 20px; }
        .notification-empty { text-align: center; padding: 30px 0; color: #6c757d; }
        .notification-empty i { font-size: 40px; color: #dee2e6; margin-bottom: 10px; display: block; }
        .btn-id-card {
            background: linear-gradient(135deg, #6f42c1 0%, #2c5aa0 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .btn-id-card:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h4><i class="fas fa-users me-2"></i>HR Portal</h4>
        <div class="navbar-buttons">
            <a href="modules/notice-board.php" class="btn btn-outline-light me-1">
                <i class="fas fa-bullhorn me-1"></i>Notice
            </a>
            <a href="#" class="btn btn-outline-light me-1" onclick="loadNotifications()" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="badge bg-danger notification-badge" id="notificationBadge" style="display:none;">0</span>
            </a>
            <a href="profile.php" class="btn btn-outline-light me-1">
                <i class="fas fa-user-circle me-1"></i>Profile
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
                        <img src="/kormoshathi/<?php echo $_SESSION['profile_picture']; ?>" class="avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <h4><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h4>
                    <p class="text-muted">HR Manager</p>
                    <hr>
                    <p><strong>HR ID:</strong> <?php echo $_SESSION['employee_id']; ?></p>
                    <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                    <a href="profile.php" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-camera me-1"></i>Update Photo
                    </a>
                </div>
            </div>
            <div class="col-8">
                <div class="row">
                    <div class="col-3"><div class="stat-card bg-primary"><div class="stat-number" id="totalEmployees">0</div><p>Total Employees</p></div></div>
                    <div class="col-3"><div class="stat-card bg-success"><div class="stat-number" id="presentToday">0</div><p>Present Today</p></div></div>
                    <div class="col-3"><div class="stat-card bg-warning"><div class="stat-number" id="onLeave">0</div><p>On Leave</p></div></div>
                    <div class="col-3"><div class="stat-card bg-danger"><div class="stat-number" id="pendingLeaves">0</div><p>Pending Leaves</p></div></div>
                </div>
                <div class="stat-card">
                    <h5><i class="fas fa-chart-line me-2"></i>Quick Actions</h5>
                    <div class="quick-actions-grid">
                        <a href="modules/add-employee.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Add Employee</a>
                        <a href="modules/employees.php" class="btn btn-success"><i class="fas fa-users me-2"></i>Employees</a>
                        <a href="modules/attendance.php" class="btn btn-warning"><i class="fas fa-fingerprint me-2"></i>Attendance</a>
                        <a href="modules/leave.php" class="btn btn-info"><i class="fas fa-calendar-alt me-2"></i>Leaves</a>
                        <a href="modules/payroll.php" class="btn btn-primary"><i class="fas fa-money-bill me-2"></i>Payroll</a>
                        <a href="modules/payroll-config.php" class="btn btn-info"><i class="fas fa-cog me-2"></i>Payroll Config</a>
                        <a href="modules/reports.php" class="btn btn-warning"><i class="fas fa-chart-bar me-2"></i>Reports</a>
                        <a href="modules/performance.php" class="btn btn-success"><i class="fas fa-star me-2"></i>Performance</a>
                        <a href="modules/notice-board.php" class="btn btn-primary"><i class="fas fa-bullhorn me-2"></i>Notice Board</a>
                        <a href="modules/hr-submissions.php" class="btn btn-info"><i class="fas fa-file-alt me-2"></i>Submissions</a>
                        <a href="modules/ai-screening-form.php" class="btn btn-info"><i class="fas fa-robot me-2"></i>AI Screening</a>
                        <a href="modules/employee-id-card-list.php" class="btn-id-card"><i class="fas fa-id-card me-2"></i>ID Cards</a>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="notification-header">
                        <h5><i class="fas fa-bell me-2 text-primary"></i>Recent Notifications</h5>
                        <span class="notification-count" id="notificationCount">0 notifications</span>
                    </div>
                    <div id="notifications"><p class="text-muted text-center py-3">Loading notifications...</p></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let realtimeInterval;
        
        async function loadDashboardStats() {
            try {
                const response = await fetch('api/dashboard-stats.php');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('totalEmployees').innerHTML = data.stats.total_employees;
                    document.getElementById('presentToday').innerHTML = data.stats.present_today;
                    document.getElementById('onLeave').innerHTML = data.stats.on_leave;
                    document.getElementById('pendingLeaves').innerHTML = data.stats.pending_leaves;
                }
            } catch(e) { console.error('Error loading stats:', e); }
        }

        async function loadNotificationCount() {
            try {
                const response = await fetch('api/notifications.php?count=1');
                const data = await response.json();
                if(data.success && data.unread > 0) {
                    const badge = document.getElementById('notificationBadge');
                    badge.style.display = 'inline';
                    badge.textContent = data.unread;
                } else {
                    document.getElementById('notificationBadge').style.display = 'none';
                }
            } catch(e) {
                console.error('Error loading notification count:', e);
            }
        }

        async function loadRecentNotifications() {
            try {
                const response = await fetch('api/notifications.php');
                const data = await response.json();
                const container = document.getElementById('notifications');
                const countElement = document.getElementById('notificationCount');
                
                if(data.success && data.data && data.data.length > 0) {
                    countElement.textContent = data.data.length + ' notifications';
                    container.innerHTML = data.data.map(n => {
                        let icon = 'fa-info-circle';
                        let styleClass = 'info';
                        let isRead = n.is_read ? 'read' : 'unread';
                        if (n.type === 'info') { icon = 'fa-info-circle'; styleClass = 'info'; }
                        else if (n.type === 'success') { icon = 'fa-check-circle'; styleClass = 'success'; }
                        else if (n.type === 'danger') { icon = 'fa-exclamation-circle'; styleClass = 'danger'; }
                        else if (n.type === 'warning') { icon = 'fa-exclamation-triangle'; styleClass = 'warning'; }
                        if (n.title.includes('Leave')) {
                            if (n.message.includes('approved')) styleClass = 'leave-approved';
                            else if (n.message.includes('rejected')) styleClass = 'leave-rejected';
                            else styleClass = 'leave-pending';
                        }
                        return `<div class="notification-item ${isRead} ${styleClass}" onclick="markAsRead(${n.id})">
                            <div class="notification-text">
                                <span class="notification-icon"><i class="fas ${icon}"></i></span>
                                <span><strong>${n.title}</strong> - ${n.message}</span>
                            </div>
                            <span class="notification-time">${new Date(n.created_at).toLocaleTimeString()}</span>
                        </div>`;
                    }).join('');
                } else {
                    countElement.textContent = '0 notifications';
                    container.innerHTML = `<div class="notification-empty"><i class="fas fa-bell-slash"></i><p>No new notifications</p></div>`;
                }
            } catch(e) {
                console.error('Error loading notifications:', e);
                document.getElementById('notifications').innerHTML = '<p class="text-muted text-center py-3">Error loading notifications</p>';
            }
        }

        async function markAsRead(id) {
            try {
                await fetch('api/notifications.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                loadRecentNotifications();
                loadNotificationCount();
            } catch(e) { console.error('Error marking as read:', e); }
        }

        async function loadNotifications() {
            try {
                const response = await fetch('api/notifications.php');
                const data = await response.json();
                if(data.success && data.data.length > 0) {
                    let msg = '📋 Notifications:\n\n';
                    data.data.forEach(n => {
                        const status = n.is_read ? '✓ Read' : '● Unread';
                        msg += status + ' | ' + n.title + ': ' + n.message + '\n';
                        msg += '   📅 ' + new Date(n.created_at).toLocaleString() + '\n\n';
                    });
                    alert(msg);
                    await fetch('api/notifications.php', {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: 0 })
                    });
                    loadNotificationCount();
                    loadRecentNotifications();
                } else {
                    alert('No notifications found');
                }
            } catch(e) {
                console.error('Error loading notifications:', e);
                alert('Error loading notifications');
            }
        }

        async function checkRealtimeUpdates() {
            try {
                const response = await fetch('api/realtime-stats.php');
                const data = await response.json();
                if (data.success && data.has_updates) {
                    loadDashboardStats();
                    loadNotificationCount();
                    loadRecentNotifications();
                }
            } catch(e) {}
        }

        function startRealtimeUpdates() { 
            loadDashboardStats(); 
            loadNotificationCount();
            loadRecentNotifications();
            realtimeInterval = setInterval(() => { 
                loadDashboardStats(); 
                loadNotificationCount();
                loadRecentNotifications();
                checkRealtimeUpdates(); 
            }, 5000); 
        }

        startRealtimeUpdates();
    </script>
</body>
</html>

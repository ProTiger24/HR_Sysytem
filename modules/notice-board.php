<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$is_hr = ($_SESSION['user_type'] === 'hr');
$back_link = $is_hr ? '../hr-dashboard.php' : '../employee-dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1000px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .card-header h5 { margin: 0; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; }
        .btn-primary:hover { transform: scale(1.02); }
        .notice-card { 
            border-left: 4px solid #2c5aa0; 
            margin-bottom: 15px; 
            padding: 15px 20px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            transition: all 0.3s ease;
        }
        .notice-card:hover { 
            transform: translateX(5px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }
        .notice-card .notice-title { 
            font-weight: 600; 
            color: #2c5aa0;
            font-size: 16px;
        }
        .notice-card .notice-message { 
            color: #333;
            margin: 8px 0;
        }
        .notice-card .notice-meta { 
            font-size: 13px; 
            color: #6c757d; 
        }
        .notice-card .notice-meta i { 
            margin-right: 5px; 
        }
        .notice-card.unread {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        .notice-card.read {
            background: #f8f9fa;
            border-left-color: #28a745;
        }
        .notice-card .badge-read {
            background: #28a745;
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        .notice-card .badge-unread {
            background: #2196f3;
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .delete-btn:hover {
            background: #dc3545;
            color: white;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            color: #dee2e6;
            margin-bottom: 15px;
            display: block;
        }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-bullhorn me-2"></i>📢 Notice Board</h4>
    <a href="<?php echo $back_link; ?>"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="container">
    <?php if ($is_hr): ?>
    <!-- Post Notice Form - Only for HR -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus-circle me-2"></i>Post New Notice</h5>
        </div>
        <div class="card-body">
            <form id="noticeForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title *</label>
                    <input type="text" class="form-control" name="title" placeholder="Enter notice title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Message *</label>
                    <textarea class="form-control" name="message" rows="3" placeholder="Enter notice details" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i>Post Notice
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Notices -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-list me-2"></i>All Notices</h5>
        </div>
        <div class="card-body">
            <div id="noticesList">
                <p class="text-muted text-center py-3">Loading notices...</p>
            </div>
        </div>
    </div>
</div>

<script>
async function loadNotices() {
    try {
        const response = await fetch('../api/notices.php');
        const data = await response.json();
        const container = document.getElementById('noticesList');
        
        if(data.success && data.data && data.data.length > 0) {
            container.innerHTML = data.data.map(notice => {
                const isRead = notice.is_read > 0;
                const readClass = isRead ? 'read' : 'unread';
                const readBadge = isRead ? 
                    '<span class="badge-read"><i class="fas fa-check me-1"></i>Read</span>' : 
                    '<span class="badge-unread"><i class="fas fa-circle me-1"></i>New</span>';
                
                const deleteBtn = <?php echo $is_hr ? 'true' : 'false'; ?> ? 
                    `<button class="delete-btn" onclick="deleteNotice(${notice.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>` : '';
                
                return `
                    <div class="notice-card ${readClass}" onclick="markAsRead(${notice.id})">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;">
                                <div class="notice-title">${notice.title}</div>
                                <div class="notice-message">${notice.message || notice.content}</div>
                                <div class="notice-meta">
                                    <i class="fas fa-user"></i>${notice.first_name || 'HR'} ${notice.last_name || ''} | 
                                    <i class="fas fa-clock"></i>${new Date(notice.created_at).toLocaleString()}
                                    ${readBadge}
                                </div>
                            </div>
                            ${deleteBtn}
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bullhorn"></i>
                    <p>No notices found</p>
                </div>
            `;
        }
    } catch(e) { 
        console.error(e);
        document.getElementById('noticesList').innerHTML = '<p class="text-muted text-center py-3">Error loading notices</p>';
    }
}

async function markAsRead(id) {
    try {
        await fetch('../api/notices.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notice_id: id })
        });
        loadNotices();
    } catch(e) { 
        console.error('Error marking as read:', e);
    }
}

async function deleteNotice(id) {
    if(!confirm('Are you sure you want to delete this notice?')) return;
    try {
        const response = await fetch('../api/notices.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notice_id: id })
        });
        const result = await response.json();
        alert(result.message);
        if(result.success) {
            loadNotices();
        }
    } catch(e) { 
        alert('Error deleting notice');
    }
}

// Submit form
document.getElementById('noticeForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('../api/notices.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        alert(result.message);
        if(result.success) {
            this.reset();
            loadNotices();
        }
    } catch(e) {
        alert('Error posting notice');
    }
});

loadNotices();
setInterval(loadNotices, 10000);
</script>
</body>
</html>

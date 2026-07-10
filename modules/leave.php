<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 8px 20px; border-radius: 8px; color: white; cursor: pointer; }
        .btn-primary:hover { transform: translateY(-2px); }
        .btn-success { background: #28a745; border: none; padding: 5px 15px; border-radius: 5px; color: white; cursor: pointer; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; border: none; padding: 5px 15px; border-radius: 5px; color: white; cursor: pointer; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; border: none; padding: 5px 15px; border-radius: 5px; color: white; cursor: pointer; }
        .btn-secondary:hover { background: #5a6268; }
        .leave-card { border-left: 4px solid #2c5aa0; margin-bottom: 15px; transition: 0.3s; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        .leave-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .leave-card.pending { border-left-color: #ffc107; }
        .leave-card.approved { border-left-color: #28a745; }
        .leave-card.rejected { border-left-color: #dc3545; }
        .status-pending { background: #ffc107; color: #333; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .status-approved { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .status-rejected { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
        .balance-box { font-size: 48px; font-weight: bold; color: #2c5aa0; }
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
        .rejection-reason { font-size: 13px; color: #dc3545; background: #f8d7da; padding: 5px 10px; border-radius: 5px; margin-top: 5px; display: inline-block; }
        .modal-content { border-radius: 15px; }
        .modal-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-calendar-alt me-2"></i>Leave Management</h4>
    <a href="<?php echo $user_type == 'hr' ? '../hr-dashboard.php' : '../employee-dashboard.php'; ?>">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<div class="container">
    <?php if($user_type == 'employee'): ?>
    <!-- Employee: Leave Balance + Apply Form -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-umbrella-beach me-2"></i>My Leave Balance</h5>
                </div>
                <div class="card-body text-center">
                    <div class="balance-box" id="leaveBalance">0</div>
                    <p>Days Remaining</p>
                    <div class="progress">
                        <div id="balanceProgress" class="progress-bar bg-success" style="width: 100%"></div>
                    </div>
                    <p class="text-muted mt-2">Total: 20 days per year</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Apply for Leave</h5>
                </div>
                <div class="card-body">
                    <form id="leaveRequestForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Leave Type</label>
                                <select class="form-select" name="leave_type" required>
                                    <option value="casual">Casual Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="earned">Earned Leave</option>
                                    <option value="maternity">Maternity Leave</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Days *</label>
                                <input type="number" class="form-control" name="total_days" id="totalDays" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" id="startDate" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control" name="end_date" id="endDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea class="form-control" name="reason" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leave Requests List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>
                <?php echo $user_type == 'hr' ? 'All Leave Requests' : 'My Leave Requests'; ?>
            </h5>
        </div>
        <div class="card-body">
            <div id="leaveRequestsList">
                <p class="text-muted text-center">Loading leave requests...</p>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Leave Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Please provide a reason for rejecting this leave request:</p>
                <textarea class="form-control" id="rejectionReason" rows="3" placeholder="Enter rejection reason..."></textarea>
                <input type="hidden" id="rejectLeaveId">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" onclick="confirmReject()"><i class="fas fa-times me-2"></i>Reject</button>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate days between dates
document.getElementById('startDate')?.addEventListener('change', calculateDays);
document.getElementById('endDate')?.addEventListener('change', calculateDays);

function calculateDays() {
    const start = document.getElementById('startDate').value;
    const end = document.getElementById('endDate').value;
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        if (days > 0) {
            document.getElementById('totalDays').value = days;
        }
    }
}

// Load leave balance for employee
async function loadLeaveBalance() {
    try {
        const response = await fetch('../api/leave-balance.php');
        const data = await response.json();
        if (data.success) {
            document.getElementById('leaveBalance').innerText = data.balance;
            const percentage = (data.balance / 20) * 100;
            document.getElementById('balanceProgress').style.width = percentage + '%';
            if (percentage < 30) {
                document.getElementById('balanceProgress').className = 'progress-bar bg-danger';
            } else if (percentage < 60) {
                document.getElementById('balanceProgress').className = 'progress-bar bg-warning';
            }
        }
    } catch(e) { console.error(e); }
}

// Load leave requests
async function loadLeaveRequests() {
    try {
        const response = await fetch('../api/leave-requests.php');
        const data = await response.json();
        const container = document.getElementById('leaveRequestsList');
        
        if (data.length > 0) {
            container.innerHTML = data.map(leave => {
                let actions = '';
                if ('<?php echo $user_type; ?>' == 'hr' && leave.status == 'pending') {
                    actions = `
                        <div class="action-buttons">
                            <button class="btn btn-success btn-sm" onclick="approveLeave(${leave.id})">
                                <i class="fas fa-check me-1"></i>Approve
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="openRejectModal(${leave.id})">
                                <i class="fas fa-times me-1"></i>Reject
                            </button>
                        </div>
                    `;
                }
                
                let rejectionHtml = '';
                if (leave.status == 'rejected' && leave.rejection_reason) {
                    rejectionHtml = `<div class="rejection-reason"><i class="fas fa-comment me-1"></i>Reason: ${leave.rejection_reason}</div>`;
                }
                
                const cardClass = leave.status == 'pending' ? 'pending' : leave.status == 'approved' ? 'approved' : 'rejected';
                const employeeName = leave.employee_name || 'You';
                
                return `
                    <div class="leave-card ${cardClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="flex:1;">
                                <h6 class="mb-1">
                                    <span class="badge bg-info">${leave.leave_type.toUpperCase()}</span>
                                    <span class="status-${leave.status} ms-2">${leave.status.toUpperCase()}</span>
                                </h6>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user me-1"></i>${employeeName} | 
                                    <i class="fas fa-calendar-week me-1"></i>${new Date(leave.start_date).toLocaleDateString()} - ${new Date(leave.end_date).toLocaleDateString()}
                                </p>
                                <p><strong>Duration:</strong> ${leave.total_days} days</p>
                                <p><strong>Reason:</strong> ${leave.reason || 'No reason provided'}</p>
                                ${rejectionHtml}
                                ${leave.status == 'approved' ? `<small class="text-success"><i class="fas fa-check-circle me-1"></i>Approved by HR</small>` : ''}
                                ${leave.status == 'rejected' ? `<small class="text-danger"><i class="fas fa-times-circle me-1"></i>Rejected by HR</small>` : ''}
                                ${actions}
                            </div>
                            <div>
                                <small class="text-muted">Applied: ${new Date(leave.applied_on).toLocaleDateString()}</small>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<p class="text-muted text-center">No leave requests found.</p>';
        }
    } catch(e) { console.error(e); }
}

// Submit leave request
document.getElementById('leaveRequestForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const leaveData = Object.fromEntries(formData);
    
    try {
        const response = await fetch('../api/leave-requests.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(leaveData)
        });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            this.reset();
            loadLeaveBalance();
            loadLeaveRequests();
        }
    } catch(error) { alert('Error submitting request'); }
});

// Approve leave (HR only)
async function approveLeave(id) {
    if(confirm('Approve this leave request?')) {
        try {
            const response = await fetch('../api/leave-requests.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, status: 'approved' })
            });
            const result = await response.json();
            alert(result.message);
            loadLeaveRequests();
        } catch(e) { alert('Error'); }
    }
}

// Open reject modal
function openRejectModal(id) {
    document.getElementById('rejectLeaveId').value = id;
    document.getElementById('rejectionReason').value = '';
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

// Confirm reject
async function confirmReject() {
    const id = document.getElementById('rejectLeaveId').value;
    const reason = document.getElementById('rejectionReason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for rejection.');
        return;
    }
    
    try {
        const response = await fetch('../api/leave-requests.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id: id, 
                status: 'rejected',
                rejection_reason: reason 
            })
        });
        const result = await response.json();
        alert(result.message);
        document.getElementById('rejectModal').querySelector('.btn-close').click();
        loadLeaveRequests();
    } catch(e) { alert('Error'); }
}

// Initialize
<?php if($user_type == 'employee'): ?>loadLeaveBalance();<?php endif; ?>
loadLeaveRequests();
setInterval(loadLeaveRequests, 5000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

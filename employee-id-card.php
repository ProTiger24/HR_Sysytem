<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';
$db = (new Database())->getConnection();

$employee_id = $_GET['id'] ?? $_SESSION['user_id'];

// Get employee details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header('Location: employee-dashboard.php');
    exit;
}

// QR Code data
$qr_data = json_encode([
    'id' => $employee['employee_id'],
    'name' => $employee['first_name'] . ' ' . $employee['last_name'],
    'department' => $employee['department'],
    'position' => $employee['position'],
    'email' => $employee['email'],
    'phone' => $employee['phone']
]);

// Check if signature exists
$signature_path = 'uploads/signature.png';
$signature_exists = file_exists($signature_path);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .id-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 400px; margin: 2rem auto; padding: 0 20px; }
        .id-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        .id-card .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
        }
        .id-card .header h5 { margin: 0; font-weight: 700; letter-spacing: 1px; }
        .id-card .header small { opacity: 0.8; }
        .id-card .body { padding: 20px; }
        .id-card .profile-section { text-align: center; margin-top: -40px; }
        .id-card .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #2c5aa0;
            object-fit: cover;
            background: #f8f9fa;
            display: inline-block;
        }
        .id-card .profile-img-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #2c5aa0;
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        .id-card .info-grid {
            margin-top: 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
        }
        .id-card .info-item { font-size: 13px; }
        .id-card .info-item .label { color: #6c757d; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .id-card .info-item .value { font-weight: 600; color: #333; }
        .id-card .footer-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .id-card .footer-section .qr-code { width: 80px; height: 80px; }
        .id-card .footer-section .signature-area { text-align: center; }
        .id-card .footer-section .signature-area .signature-img {
            max-height: 40px;
            max-width: 120px;
            display: block;
            margin: 0 auto;
        }
        .id-card .footer-section .signature-area .signature-line {
            width: 120px;
            border-bottom: 2px solid #333;
            margin: 5px auto;
        }
        .id-card .footer-section .signature-area .signature-text { font-size: 10px; color: #6c757d; }
        .btn-print { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; color: white; padding: 10px 30px; border-radius: 8px; }
        .btn-print:hover { transform: scale(1.05); }
        .btn-download { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; color: white; padding: 10px 30px; border-radius: 8px; }
        .btn-download:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="navbar no-print">
    <h4><i class="fas fa-id-card me-2"></i>Employee ID Card</h4>
    <div>
        <a href="modules/employee-id-card-list.php" class="me-2"><i class="fas fa-list me-1"></i>All Cards</a>
        <?php if ($_SESSION['user_type'] === 'hr'): ?>
        <a href="profile.php"><i class="fas fa-signature me-1"></i>Add Signature</a>
        <?php endif; ?>
        <a href="<?php echo $_SESSION['user_type'] === 'hr' ? 'hr-dashboard.php' : 'employee-dashboard.php'; ?>"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

<div class="container">
    <div class="id-card" id="idCard">
        <!-- Header -->
        <div class="header">
            <h5><i class="fas fa-building me-2"></i>KormoShathi</h5>
            <small>Employee ID Card</small>
        </div>
        
        <!-- Body -->
        <div class="body">
            <!-- Profile Section -->
            <div class="profile-section">
                <?php if(!empty($employee['profile_picture']) && file_exists($employee['profile_picture'])): ?>
                    <img src="/kormoshathi/<?php echo $employee['profile_picture']; ?>" class="profile-img" alt="Profile">
                <?php else: ?>
                    <div class="profile-img-placeholder">
                        <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <h5 class="mt-2 mb-0"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h5>
                <small class="text-muted"><?php echo $employee['position'] ?? 'Employee'; ?></small>
            </div>
            
            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Employee ID</div>
                    <div class="value"><?php echo $employee['employee_id']; ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Department</div>
                    <div class="value"><?php echo $employee['department'] ?? 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Blood Group</div>
                    <div class="value"><?php echo $employee['blood_group'] ?? 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Phone</div>
                    <div class="value"><?php echo $employee['phone'] ?? 'N/A'; ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Email</div>
                    <div class="value" style="font-size:11px;"><?php echo $employee['email']; ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Joined</div>
                    <div class="value"><?php echo date('d M Y', strtotime($employee['join_date'] ?? 'now')); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Signature -->
        <div class="footer-section">
            <div class="qr-code" id="qrcode"></div>
            <div class="signature-area">
                <?php if ($signature_exists): ?>
                    <img src="/kormoshathi/uploads/signature.png" class="signature-img" alt="Signature">
                <?php else: ?>
                    <div class="signature-line"></div>
                <?php endif; ?>
                <div class="signature-text">HR Authority</div>
                <div style="font-size: 12px; color: #2c5aa0; font-weight: 600;">KormoShathi</div>
            </div>
        </div>
    </div>
    
    <!-- Buttons -->
    <div class="text-center mt-3 no-print">
        <button class="btn-print me-2" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
        <button class="btn-download" onclick="downloadCard()">
            <i class="fas fa-download me-2"></i>Download
        </button>
    </div>
</div>

<script>
// Generate QR Code
document.addEventListener('DOMContentLoaded', function() {
    const qrData = <?php echo json_encode($qr_data); ?>;
    new QRCode(document.getElementById("qrcode"), {
        text: JSON.stringify(qrData),
        width: 80,
        height: 80,
        colorDark: "#2c5aa0",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
});

// Download ID Card
function downloadCard() {
    if (typeof html2canvas === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
        script.onload = function() {
            captureAndDownload();
        };
        document.head.appendChild(script);
    } else {
        captureAndDownload();
    }
}

function captureAndDownload() {
    const card = document.getElementById('idCard');
    html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'employee_id_card_<?php echo $employee['employee_id']; ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}
</script>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$db = (new Database())->getConnection();

// Get all employees
$stmt = $db->prepare("SELECT id, employee_id, first_name, last_name, department, position, profile_picture FROM users WHERE user_type = 'employee' AND status = 'active' ORDER BY first_name");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Cards - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; transition: 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.3); }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; padding: 15px 20px; }
        .employee-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: 0.3s;
            cursor: pointer;
        }
        .employee-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.15); }
        .employee-card .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 10px;
            display: block;
            border: 3px solid #2c5aa0;
        }
        .employee-card .avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
            margin: 0 auto 10px;
            border: 3px solid #2c5aa0;
        }
        .employee-card .name { font-weight: 600; font-size: 14px; }
        .employee-card .dept { font-size: 12px; color: #6c757d; }
        .employee-card .id { font-size: 11px; color: #2c5aa0; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .btn-id-card {
            background: linear-gradient(135deg, #6f42c1 0%, #2c5aa0 100%);
            border: none;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-id-card:hover { transform: scale(1.05); color: white; }
    </style>
</head>
<body>
<div class="navbar">
    <h4><i class="fas fa-id-card me-2"></i>Employee ID Cards</h4>
    <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>All Employees (<?php echo count($employees); ?>)</h5>
        </div>
        <div class="card-body">
            <div class="grid">
                <?php foreach ($employees as $emp): ?>
                <div class="employee-card" onclick="window.location.href='employee-id-card.php?id=<?php echo $emp['id']; ?>'">
                    <?php if(!empty($emp['profile_picture']) && file_exists('../' . $emp['profile_picture'])): ?>
                        <img src="../<?php echo $emp['profile_picture']; ?>" class="avatar" alt="Profile">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="name"><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></div>
                    <div class="dept"><?php echo $emp['department'] ?? 'N/A'; ?></div>
                    <div class="id"><?php echo $emp['employee_id']; ?></div>
                    <div class="mt-2">
                        <a href="employee-id-card.php?id=<?php echo $emp['id']; ?>" class="btn-id-card">
                            <i class="fas fa-id-card me-1"></i>View Card
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>

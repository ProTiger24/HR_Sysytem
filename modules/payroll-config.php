<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'hr') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get current config
$stmt = $db->query("SELECT payroll_config FROM company_settings WHERE id = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$config = json_decode($result['payroll_config'] ?? '{}', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Configuration - KormoShathi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 8px; }
        .container { max-width: 600px; margin: 2rem auto; padding: 0 20px; }
        .card { background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); color: white; border-radius: 15px 15px 0 0; padding: 15px 20px; }
        .btn-primary { background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%); border: none; padding: 10px 20px; border-radius: 8px; color: white; cursor: pointer; width: 100%; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .form-label { font-weight: 600; margin-bottom: 5px; display: block; }
        .mb-3 { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="navbar">
        <h4><i class="fas fa-cog me-2"></i>Payroll Configuration</h4>
        <a href="../hr-dashboard.php"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payroll Settings</h5>
            </div>
            <div class="card-body">
                <form id="configForm">
                    <div class="mb-3">
                        <label class="form-label">House Rent (%)</label>
                        <input type="number" class="form-control" name="house_rent_percent" value="<?php echo $config['house_rent_percent'] ?? 50; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Medical Allowance (%)</label>
                        <input type="number" class="form-control" name="medical_percent" value="<?php echo $config['medical_percent'] ?? 10; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Travel Allowance (%)</label>
                        <input type="number" class="form-control" name="travel_percent" value="<?php echo $config['travel_percent'] ?? 5; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provident Fund (%)</label>
                        <input type="number" class="form-control" name="pf_percent" value="<?php echo $config['pf_percent'] ?? 10; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" class="form-control" name="tax_percent" value="<?php echo $config['tax_percent'] ?? 5; ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Save Configuration</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('configForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const configData = Object.fromEntries(formData);
            
            try {
                const response = await fetch('../api/update-payroll-config.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(configData)
                });
                const result = await response.json();
                alert(result.message);
                if(result.success) {
                    location.reload();
                }
            } catch(error) {
                alert('Error saving configuration');
            }
        });
    </script>
</body>
</html>

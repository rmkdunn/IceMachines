<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../config.php";

// Get machine ID from URL
$machine_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($machine_id <= 0) {
    header("location: ice_dashboard.php");
    exit;
}

// Fetch machine details
$machine = null;
$machine_sql = "SELECT * FROM ice_machines WHERE id = ?";
if($stmt = mysqli_prepare($link, $machine_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $machine_id);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $machine = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if(!$machine) {
    header("location: ice_dashboard.php");
    exit;
}

// Fetch maintenance history
$maintenance_records = [];
$history_sql = "SELECT * FROM maintenance_records WHERE machine_id = ? ORDER BY maintenance_date DESC, created_at DESC";
if($stmt = mysqli_prepare($link, $history_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $machine_id);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            $maintenance_records[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Calculate statistics
$total_cost = 0;
$maintenance_types = [];
foreach($maintenance_records as $record) {
    if($record['cost']) {
        $total_cost += $record['cost'];
    }
    if(!isset($maintenance_types[$record['maintenance_type']])) {
        $maintenance_types[$record['maintenance_type']] = 0;
    }
    $maintenance_types[$record['maintenance_type']]++;
}

function getMaintenanceTypeIcon($type) {
    switch($type) {
        case 'routine': return '🔧';
        case 'cleaning': return '🧽';
        case 'inspection': return '🔍';
        case 'repair': return '⚡';
        case 'emergency': return '🚨';
        default: return '📋';
    }
}

function getMaintenanceTypeClass($type) {
    switch($type) {
        case 'routine': return 'maintenance-routine';
        case 'cleaning': return 'maintenance-cleaning';
        case 'inspection': return 'maintenance-inspection';
        case 'repair': return 'maintenance-repair';
        case 'emergency': return 'maintenance-emergency';
        default: return 'maintenance-other';
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History - <?php echo htmlspecialchars($machine['machine_name']); ?></title>
    <link rel="stylesheet" href="ice_machine_style.css">
    <style>
        .maintenance-routine { border-left-color: #10b981; }
        .maintenance-cleaning { border-left-color: #3b82f6; }
        .maintenance-inspection { border-left-color: #f59e0b; }
        .maintenance-repair { border-left-color: #ef4444; }
        .maintenance-emergency { border-left-color: #dc2626; }
        .maintenance-other { border-left-color: #6b7280; }
        
        .maintenance-record {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #ddd;
        }
        
        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .record-type {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .record-date {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .record-details {
            margin-bottom: 10px;
        }
        
        .record-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #6b7280;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .cost-highlight {
            color: #059669;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="ice_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <!-- Machine Info Header -->
        <div class="machine-card" style="margin-bottom: 20px;">
            <div class="machine-header">
                <h2><?php echo htmlspecialchars($machine['machine_name']); ?></h2>
                <span class="status-badge <?php echo $machine['status'] == 'operational' ? 'status-good' : ($machine['status'] == 'needs_maintenance' ? 'status-warning' : 'status-danger'); ?>">
                    <?php echo ucwords(str_replace('_', ' ', $machine['status'])); ?>
                </span>
            </div>
            
            <div class="machine-info">
                <div class="info-row">
                    <span class="label">📍 Location:</span>
                    <span><?php echo htmlspecialchars($machine['location']); ?></span>
                </div>
                
                <?php if($machine['model']): ?>
                <div class="info-row">
                    <span class="label">🏷️ Model:</span>
                    <span><?php echo htmlspecialchars($machine['model']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($machine['serial_number']): ?>
                <div class="info-row">
                    <span class="label">🔢 Serial:</span>
                    <span><?php echo htmlspecialchars($machine['serial_number']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($machine['installation_date']): ?>
                <div class="info-row">
                    <span class="label">📅 Installed:</span>
                    <span><?php echo date('M j, Y', strtotime($machine['installation_date'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Maintenance Statistics -->
        <div class="stats-grid" style="margin-bottom: 20px;">
            <div class="stat-card stat-info">
                <div class="stat-number"><?php echo count($maintenance_records); ?></div>
                <div class="stat-label">Total Services</div>
            </div>
            <div class="stat-card stat-good">
                <div class="stat-number">$<?php echo number_format($total_cost, 2); ?></div>
                <div class="stat-label">Total Cost</div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-number"><?php echo isset($maintenance_types['routine']) ? $maintenance_types['routine'] : 0; ?></div>
                <div class="stat-label">Routine Services</div>
            </div>
            <div class="stat-card stat-danger">
                <div class="stat-number"><?php echo isset($maintenance_types['repair']) ? $maintenance_types['repair'] : 0; ?></div>
                <div class="stat-label">Repairs</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons" style="margin-bottom: 20px;">
            <a href="log_maintenance.php?machine_id=<?php echo $machine['id']; ?>" class="btn btn-primary">📝 Log New Service</a>
            <a href="edit_machine.php?id=<?php echo $machine['id']; ?>" class="btn btn-secondary">✏️ Edit Machine</a>
        </div>

        <!-- Maintenance History -->
        <h3>Maintenance History</h3>
        
        <?php if(empty($maintenance_records)): ?>
            <div class="no-machines">
                <h3>No maintenance records found</h3>
                <p>Start by <a href="log_maintenance.php?machine_id=<?php echo $machine['id']; ?>">logging the first maintenance</a> for this machine.</p>
            </div>
        <?php else: ?>
            <?php foreach($maintenance_records as $record): ?>
                <div class="maintenance-record <?php echo getMaintenanceTypeClass($record['maintenance_type']); ?>">
                    <div class="record-header">
                        <div class="record-type">
                            <?php echo getMaintenanceTypeIcon($record['maintenance_type']); ?>
                            <?php echo ucwords(str_replace('_', ' ', $record['maintenance_type'])); ?>
                        </div>
                        <div class="record-date">
                            <?php echo date('M j, Y', strtotime($record['maintenance_date'])); ?>
                        </div>
                    </div>
                    
                    <div class="record-details">
                        <p><strong>Work Performed:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars($record['description'])); ?></p>
                        
                        <?php if($record['parts_used']): ?>
                            <p><strong>Parts Used:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($record['parts_used'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if($record['next_service_date']): ?>
                            <p><strong>Next Service Due:</strong> <?php echo date('M j, Y', strtotime($record['next_service_date'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="record-meta">
                        <span>Performed by: <?php echo htmlspecialchars($record['performed_by']); ?></span>
                        <?php if($record['cost']): ?>
                            <span class="cost-highlight">Cost: $<?php echo number_format($record['cost'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
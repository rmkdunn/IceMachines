<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Function to get status badge class
function getStatusBadge($status) {
    switch($status) {
        case 'operational':
            return 'status-good';
        case 'needs_maintenance':
            return 'status-warning';
        case 'out_of_order':
            return 'status-danger';
        case 'scheduled_maintenance':
            return 'status-info';
        default:
            return 'status-unknown';
    }
}

// Function to calculate days until next maintenance
function getDaysUntilMaintenance($next_date) {
    if (!$next_date) return null;
    $today = new DateTime();
    $next = new DateTime($next_date);
    $interval = $today->diff($next);
    return $interval->invert ? -$interval->days : $interval->days;
}

// Fetch all ice machines with their latest maintenance info
$sql = "SELECT im.*, 
               (SELECT MAX(maintenance_date) FROM maintenance_records mr WHERE mr.machine_id = im.id) as last_actual_maintenance,
               (SELECT COUNT(*) FROM maintenance_records mr WHERE mr.machine_id = im.id) as maintenance_count
        FROM ice_machines im 
        ORDER BY im.status = 'out_of_order' DESC, 
                 im.status = 'needs_maintenance' DESC, 
                 im.next_maintenance_due ASC";

$machines = [];
if($result = mysqli_query($link, $sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $machines[] = $row;
    }
}

// Get summary statistics
$stats_sql = "SELECT 
    SUM(CASE WHEN status = 'operational' THEN 1 ELSE 0 END) as operational,
    SUM(CASE WHEN status = 'needs_maintenance' THEN 1 ELSE 0 END) as needs_maintenance,
    SUM(CASE WHEN status = 'out_of_order' THEN 1 ELSE 0 END) as out_of_order,
    SUM(CASE WHEN status = 'scheduled_maintenance' THEN 1 ELSE 0 END) as scheduled_maintenance,
    COUNT(*) as total
FROM ice_machines";

$stats = ['operational' => 0, 'needs_maintenance' => 0, 'out_of_order' => 0, 'scheduled_maintenance' => 0, 'total' => 0];
if($result = mysqli_query($link, $stats_sql)) {
    $stats = mysqli_fetch_assoc($result);
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ice Machine Maintenance Dashboard</title>
    <link rel="stylesheet" href="ice_machine_style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🧊 Ice Machine Maintenance</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-good">
                <div class="stat-number"><?php echo $stats['operational']; ?></div>
                <div class="stat-label">Operational</div>
            </div>
            <div class="stat-card stat-warning">
                <div class="stat-number"><?php echo $stats['needs_maintenance']; ?></div>
                <div class="stat-label">Need Maintenance</div>
            </div>
            <div class="stat-card stat-danger">
                <div class="stat-number"><?php echo $stats['out_of_order']; ?></div>
                <div class="stat-label">Out of Order</div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-number"><?php echo $stats['scheduled_maintenance']; ?></div>
                <div class="stat-label">Scheduled</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="add_machine.php" class="btn btn-primary">+ Add New Machine</a>
            <a href="log_maintenance.php" class="btn btn-secondary">📝 Log Maintenance</a>
        </div>

        <!-- Machines List -->
        <div class="machines-grid">
            <?php if (empty($machines)): ?>
                <div class="no-machines">
                    <h3>No machines found</h3>
                    <p>Get started by <a href="add_machine.php">adding your first ice machine</a>.</p>
                </div>
            <?php else: ?>
                <?php foreach ($machines as $machine): ?>
                    <div class="machine-card <?php echo getStatusBadge($machine['status']); ?>">
                        <div class="machine-header">
                            <h3><?php echo htmlspecialchars($machine['machine_name']); ?></h3>
                            <span class="status-badge <?php echo getStatusBadge($machine['status']); ?>">
                                <?php echo ucwords(str_replace('_', ' ', $machine['status'])); ?>
                            </span>
                        </div>
                        
                        <div class="machine-info">
                            <div class="info-row">
                                <span class="label">📍 Location:</span>
                                <span><?php echo htmlspecialchars($machine['location']); ?></span>
                            </div>
                            
                            <?php if ($machine['model']): ?>
                            <div class="info-row">
                                <span class="label">🏷️ Model:</span>
                                <span><?php echo htmlspecialchars($machine['model']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($machine['last_actual_maintenance']): ?>
                            <div class="info-row">
                                <span class="label">🔧 Last Service:</span>
                                <span><?php echo date('M j, Y', strtotime($machine['last_actual_maintenance'])); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($machine['next_maintenance_due']): ?>
                            <div class="info-row">
                                <span class="label">📅 Next Due:</span>
                                <span>
                                    <?php 
                                    echo date('M j, Y', strtotime($machine['next_maintenance_due']));
                                    $days = getDaysUntilMaintenance($machine['next_maintenance_due']);
                                    if ($days !== null) {
                                        if ($days < 0) {
                                            echo " <span class='overdue'>(" . abs($days) . " days overdue)</span>";
                                        } elseif ($days <= 7) {
                                            echo " <span class='due-soon'>(" . $days . " days)</span>";
                                        } else {
                                            echo " <span class='due-later'>(" . $days . " days)</span>";
                                        }
                                    }
                                    ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-row">
                                <span class="label">📊 Total Services:</span>
                                <span><?php echo $machine['maintenance_count']; ?></span>
                            </div>
                        </div>
                        
                        <div class="machine-actions">
                            <a href="machine_history.php?id=<?php echo $machine['id']; ?>" class="btn btn-small">History</a>
                            <a href="log_maintenance.php?machine_id=<?php echo $machine['id']; ?>" class="btn btn-small btn-primary">Log Service</a>
                            <a href="edit_machine.php?id=<?php echo $machine['id']; ?>" class="btn btn-small btn-secondary">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
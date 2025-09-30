<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Get machine ID from URL if provided
$selected_machine_id = isset($_GET['machine_id']) ? (int)$_GET['machine_id'] : 0;

// Fetch all machines for dropdown
$machines = [];
$machine_sql = "SELECT id, machine_name, location FROM ice_machines ORDER BY machine_name";
if($result = mysqli_query($link, $machine_sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $machines[] = $row;
    }
}

// Form variables
$machine_id = $maintenance_date = $maintenance_type = $performed_by = $description = $parts_used = $cost = $next_service_date = "";
$machine_id_err = $maintenance_date_err = $maintenance_type_err = $performed_by_err = $description_err = "";
$success_message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate machine selection
    if(empty($_POST["machine_id"])){
        $machine_id_err = "Please select a machine.";
    } else{
        $machine_id = (int)$_POST["machine_id"];
    }
    
    // Validate maintenance date
    if(empty(trim($_POST["maintenance_date"]))){
        $maintenance_date_err = "Please enter the maintenance date.";
    } else{
        $maintenance_date = trim($_POST["maintenance_date"]);
    }
    
    // Validate maintenance type
    if(empty($_POST["maintenance_type"])){
        $maintenance_type_err = "Please select a maintenance type.";
    } else{
        $maintenance_type = $_POST["maintenance_type"];
    }
    
    // Validate performed by
    if(empty(trim($_POST["performed_by"]))){
        $performed_by_err = "Please enter who performed the maintenance.";
    } else{
        $performed_by = trim($_POST["performed_by"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))){
        $description_err = "Please enter a description of the work performed.";
    } else{
        $description = trim($_POST["description"]);
    }
    
    // Get optional fields
    $parts_used = trim($_POST["parts_used"]);
    $cost = trim($_POST["cost"]);
    $next_service_date = trim($_POST["next_service_date"]);
    
    // Check input errors before inserting in database
    if(empty($machine_id_err) && empty($maintenance_date_err) && empty($maintenance_type_err) && empty($performed_by_err) && empty($description_err)){
        
        // Start transaction
        mysqli_autocommit($link, FALSE);
        
        try {
            // Insert maintenance record
            $sql = "INSERT INTO maintenance_records (machine_id, maintenance_date, maintenance_type, performed_by, description, parts_used, cost, next_service_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "isssssds", $param_machine_id, $param_maintenance_date, $param_maintenance_type, $param_performed_by, $param_description, $param_parts_used, $param_cost, $param_next_service_date);
                
                $param_machine_id = $machine_id;
                $param_maintenance_date = $maintenance_date;
                $param_maintenance_type = $maintenance_type;
                $param_performed_by = $performed_by;
                $param_description = $description;
                $param_parts_used = !empty($parts_used) ? $parts_used : null;
                $param_cost = !empty($cost) ? (float)$cost : null;
                $param_next_service_date = !empty($next_service_date) ? $next_service_date : null;
                
                if(!mysqli_stmt_execute($stmt)){
                    throw new Exception("Error inserting maintenance record");
                }
                mysqli_stmt_close($stmt);
            }
            
            // Update machine's last maintenance and next maintenance due dates
            $update_sql = "UPDATE ice_machines SET last_maintenance = ?, next_maintenance_due = ? WHERE id = ?";
            if($stmt = mysqli_prepare($link, $update_sql)){
                mysqli_stmt_bind_param($stmt, "ssi", $maintenance_date, $next_service_date, $machine_id);
                
                if(!mysqli_stmt_execute($stmt)){
                    throw new Exception("Error updating machine dates");
                }
                mysqli_stmt_close($stmt);
            }
            
            // Update machine status based on maintenance type
            $new_status = 'operational';
            if($maintenance_type == 'repair' || $maintenance_type == 'emergency') {
                $new_status = 'operational'; // Assume repairs fix the machine
            }
            
            $status_sql = "UPDATE ice_machines SET status = ? WHERE id = ?";
            if($stmt = mysqli_prepare($link, $status_sql)){
                mysqli_stmt_bind_param($stmt, "si", $new_status, $machine_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            
            // Commit transaction
            mysqli_commit($link);
            
            $success_message = "Maintenance record added successfully!";
            // Clear form fields
            $machine_id = $maintenance_date = $maintenance_type = $performed_by = $description = $parts_used = $cost = $next_service_date = "";
            $selected_machine_id = 0;
            
        } catch (Exception $e) {
            mysqli_rollback($link);
            echo "Error: " . $e->getMessage();
        }
        
        mysqli_autocommit($link, TRUE);
    }
}

// Set default values
if($selected_machine_id > 0) {
    $machine_id = $selected_machine_id;
}
if(empty($maintenance_date)) {
    $maintenance_date = date('Y-m-d');
}
if(empty($performed_by)) {
    $performed_by = $_SESSION["username"];
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Maintenance</title>
    <link rel="stylesheet" href="ice_machine_style.css">
</head>
<body>
    <div class="container">
        <a href="ice_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="form-wrapper">
            <h2>Log Maintenance Activity</h2>
            <p>Record maintenance work performed on ice machines.</p>
            
            <?php if(!empty($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Machine *</label>
                    <select name="machine_id">
                        <option value="">Select a machine...</option>
                        <?php foreach($machines as $machine): ?>
                            <option value="<?php echo $machine['id']; ?>" <?php echo ($machine['id'] == $machine_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($machine['machine_name']) . ' - ' . htmlspecialchars($machine['location']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error"><?php echo $machine_id_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Maintenance Date *</label>
                    <input type="date" name="maintenance_date" value="<?php echo $maintenance_date; ?>">
                    <span class="error"><?php echo $maintenance_date_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Maintenance Type *</label>
                    <select name="maintenance_type">
                        <option value="">Select type...</option>
                        <option value="routine" <?php echo ($maintenance_type == 'routine') ? 'selected' : ''; ?>>Routine Maintenance</option>
                        <option value="cleaning" <?php echo ($maintenance_type == 'cleaning') ? 'selected' : ''; ?>>Cleaning</option>
                        <option value="inspection" <?php echo ($maintenance_type == 'inspection') ? 'selected' : ''; ?>>Inspection</option>
                        <option value="repair" <?php echo ($maintenance_type == 'repair') ? 'selected' : ''; ?>>Repair</option>
                        <option value="emergency" <?php echo ($maintenance_type == 'emergency') ? 'selected' : ''; ?>>Emergency Repair</option>
                    </select>
                    <span class="error"><?php echo $maintenance_type_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Performed By *</label>
                    <input type="text" name="performed_by" value="<?php echo $performed_by; ?>" placeholder="Technician name">
                    <span class="error"><?php echo $performed_by_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Description of Work *</label>
                    <textarea name="description" placeholder="Describe the maintenance work performed, any issues found, and actions taken..."><?php echo $description; ?></textarea>
                    <span class="error"><?php echo $description_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Parts Used</label>
                    <textarea name="parts_used" placeholder="List any parts or materials used..."><?php echo $parts_used; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Cost ($)</label>
                    <input type="number" name="cost" value="<?php echo $cost; ?>" step="0.01" min="0" placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label>Next Service Date</label>
                    <input type="date" name="next_service_date" value="<?php echo $next_service_date; ?>">
                    <small style="color: #666; display: block; margin-top: 5px;">When should the next maintenance be performed?</small>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Log Maintenance">
                    <a href="ice_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
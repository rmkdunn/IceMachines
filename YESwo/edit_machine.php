<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

// Get machine ID from URL
$machine_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($machine_id <= 0) {
    header("location: ice_dashboard.php");
    exit;
}

$machine_name = $location = $model = $serial_number = $installation_date = $status = $notes = "";
$machine_name_err = $location_err = "";
$success_message = "";

// Fetch current machine data
$machine_sql = "SELECT * FROM ice_machines WHERE id = ?";
if($stmt = mysqli_prepare($link, $machine_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $machine_id);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $machine = mysqli_fetch_assoc($result);
        if($machine) {
            $machine_name = $machine['machine_name'];
            $location = $machine['location'];
            $model = $machine['model'];
            $serial_number = $machine['serial_number'];
            $installation_date = $machine['installation_date'];
            $status = $machine['status'];
            $notes = $machine['notes'];
        } else {
            header("location: ice_dashboard.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate machine name
    if(empty(trim($_POST["machine_name"]))){
        $machine_name_err = "Please enter a machine name.";
    } else{
        $machine_name = trim($_POST["machine_name"]);
    }
    
    // Validate location
    if(empty(trim($_POST["location"]))){
        $location_err = "Please enter a location.";
    } else{
        $location = trim($_POST["location"]);
    }
    
    // Get other fields
    $model = trim($_POST["model"]);
    $serial_number = trim($_POST["serial_number"]);
    $installation_date = trim($_POST["installation_date"]);
    $status = $_POST["status"];
    $notes = trim($_POST["notes"]);
    
    // Check input errors before updating database
    if(empty($machine_name_err) && empty($location_err)){
        
        $sql = "UPDATE ice_machines SET machine_name = ?, location = ?, model = ?, serial_number = ?, installation_date = ?, status = ?, notes = ? WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssssi", $param_machine_name, $param_location, $param_model, $param_serial_number, $param_installation_date, $param_status, $param_notes, $machine_id);
            
            $param_machine_name = $machine_name;
            $param_location = $location;
            $param_model = !empty($model) ? $model : null;
            $param_serial_number = !empty($serial_number) ? $serial_number : null;
            $param_installation_date = !empty($installation_date) ? $installation_date : null;
            $param_status = $status;
            $param_notes = !empty($notes) ? $notes : null;
            
            if(mysqli_stmt_execute($stmt)){
                $success_message = "Machine updated successfully!";
            } else{
                echo "Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ice Machine</title>
    <link rel="stylesheet" href="ice_machine_style.css">
</head>
<body>
    <div class="container">
        <a href="ice_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="form-wrapper">
            <h2>Edit Ice Machine</h2>
            <p>Update the details for this ice machine.</p>
            
            <?php if(!empty($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $machine_id; ?>" method="post">
                <div class="form-group">
                    <label>Machine Name *</label>
                    <input type="text" name="machine_name" value="<?php echo htmlspecialchars($machine_name); ?>" placeholder="e.g., Ice Machine 01">
                    <span class="error"><?php echo $machine_name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="e.g., Kitchen - Main Floor">
                    <span class="error"><?php echo $location_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" value="<?php echo htmlspecialchars($model); ?>" placeholder="e.g., Manitowoc IY-0454A">
                </div>
                
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_number" value="<?php echo htmlspecialchars($serial_number); ?>" placeholder="e.g., IC001234">
                </div>
                
                <div class="form-group">
                    <label>Installation Date</label>
                    <input type="date" name="installation_date" value="<?php echo $installation_date; ?>">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="operational" <?php echo ($status == 'operational') ? 'selected' : ''; ?>>Operational</option>
                        <option value="needs_maintenance" <?php echo ($status == 'needs_maintenance') ? 'selected' : ''; ?>>Needs Maintenance</option>
                        <option value="out_of_order" <?php echo ($status == 'out_of_order') ? 'selected' : ''; ?>>Out of Order</option>
                        <option value="scheduled_maintenance" <?php echo ($status == 'scheduled_maintenance') ? 'selected' : ''; ?>>Scheduled Maintenance</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Any additional information about this machine..."><?php echo htmlspecialchars($notes); ?></textarea>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Update Machine">
                    <a href="ice_dashboard.php" class="btn btn-secondary">Cancel</a>
                    <a href="machine_history.php?id=<?php echo $machine_id; ?>" class="btn btn-secondary">View History</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
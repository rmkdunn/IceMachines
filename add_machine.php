<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config.php";

$machine_name = $location = $model = $serial_number = $installation_date = $notes = "";
$machine_name_err = $location_err = "";
$success_message = "";

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
    
    // Get optional fields
    $model = trim($_POST["model"]);
    $serial_number = trim($_POST["serial_number"]);
    $installation_date = trim($_POST["installation_date"]);
    $notes = trim($_POST["notes"]);
    
    // Check input errors before inserting in database
    if(empty($machine_name_err) && empty($location_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO ice_machines (machine_name, location, model, serial_number, installation_date, notes) VALUES (?, ?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssssss", $param_machine_name, $param_location, $param_model, $param_serial_number, $param_installation_date, $param_notes);
            
            // Set parameters
            $param_machine_name = $machine_name;
            $param_location = $location;
            $param_model = !empty($model) ? $model : null;
            $param_serial_number = !empty($serial_number) ? $serial_number : null;
            $param_installation_date = !empty($installation_date) ? $installation_date : null;
            $param_notes = !empty($notes) ? $notes : null;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $success_message = "Ice machine added successfully!";
                // Clear form fields
                $machine_name = $location = $model = $serial_number = $installation_date = $notes = "";
            } else{
                echo "Something went wrong. Please try again later.";
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ice Machine</title>
    <link rel="stylesheet" href="ice_machine_style.css">
</head>
<body>
    <div class="container">
        <a href="ice_dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="form-wrapper">
            <h2>Add New Ice Machine</h2>
            <p>Fill in the details below to add a new ice machine to the maintenance system.</p>
            
            <?php if(!empty($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Machine Name *</label>
                    <input type="text" name="machine_name" value="<?php echo $machine_name; ?>" placeholder="e.g., Ice Machine 01">
                    <span class="error"><?php echo $machine_name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" value="<?php echo $location; ?>" placeholder="e.g., Kitchen - Main Floor">
                    <span class="error"><?php echo $location_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" value="<?php echo $model; ?>" placeholder="e.g., Manitowoc IY-0454A">
                </div>
                
                <div class="form-group">
                    <label>Serial Number</label>
                    <input type="text" name="serial_number" value="<?php echo $serial_number; ?>" placeholder="e.g., IC001234">
                </div>
                
                <div class="form-group">
                    <label>Installation Date</label>
                    <input type="date" name="installation_date" value="<?php echo $installation_date; ?>">
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Any additional information about this machine..."><?php echo $notes; ?></textarea>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Add Machine">
                    <a href="ice_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
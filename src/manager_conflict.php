<?php
include('../src/config.php'); 
include('../src/error_handler.php');

// Validate and fetch the required parameters
$department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
$current_manager_id = isset($_GET['current_manager_id']) ? (int)$_GET['current_manager_id'] : null;

// Check if required parameters are provided
if (!$department_id || !$current_manager_id) {
    $error = urlencode("Invalid request. Missing required parameters.");
    header("Location: hr_crud.php?error=$error");
    exit();
}

// Fetch current manager details
$sql = "SELECT e.first_name, e.last_name, d.department_name 
        FROM Employee e
        JOIN Department d ON d.manager_id = e.employee_id
        WHERE e.employee_id = ? AND d.department_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $current_manager_id, $department_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $manager = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    logError("Failed to retrieve manager details from Employee and Department tables", mysqli_error($conn));
    $error = urlencode("Failed to fetch current manager details.");
    header("Location: hr_create.php?error=$error");
    exit();
}

// Check if manager details are found
if (!$manager) {
    $error = urlencode("No manager found for the selected department.");
    header("Location: hr_crud.php?error=$error");
    exit();
}

// Extract manager details
$manager_name = htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']);
$department_name = htmlspecialchars($manager['department_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Conflict</title>
    <link rel="stylesheet" href="../styles/manager_conflict.css">
</head>
<body>
    <div class="modal">
        <h2>Manager Conflict</h2>
        <p>The department <strong><?php echo $department_name; ?></strong> already has a manager: <strong><?php echo $manager_name; ?></strong>.</p>
        <p>Would you like to update the current manager's role or return to the previous page?</p>
        <a href="../src/hr_update.php?id=<?php echo $current_manager_id; ?>" class="btn-update">Yes, update the manager's role</a>
        <a href="../src/hr_create.php" class="close">No, go back</a>
    </div>
</body>
</html>

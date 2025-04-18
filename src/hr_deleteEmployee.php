<?php
session_start();

include('../src/config.php');
include('../src/error_handler.php');
include('../src/audit_helper.php'); 

// Check if session variables exist and validate role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: unauthorized.php');
    exit();
}

// Validate employee_id parameter
if (!isset($_GET['id'])) {
    $error = urlencode("No employee ID provided.");
    header("Location: hr_crud.php?error=$error");
    exit();
}

$employee_id = $_GET['id'];

// First, get the user_id associated with the employee
$sqlGetUserId = "SELECT user_id FROM Employee WHERE employee_id = ?";
$stmtGetUserId = mysqli_prepare($conn, $sqlGetUserId);
mysqli_stmt_bind_param($stmtGetUserId, "i", $employee_id);
mysqli_stmt_execute($stmtGetUserId);
$resultGetUserId = mysqli_stmt_get_result($stmtGetUserId);
$user = mysqli_fetch_assoc($resultGetUserId);
mysqli_stmt_close($stmtGetUserId);

if ($user) {
    $user_id = $user['user_id'];

    // Store the employee ID for later use in the audit log
    $deletedEmployeeId = $employee_id; 

    // Check if the employee has a record in the Manager table
    $sqlCheckManagerRecord = "SELECT manager_id, end_date FROM Manager WHERE manager_id = ?";
    $stmtCheckManagerRecord = mysqli_prepare($conn, $sqlCheckManagerRecord);
    mysqli_stmt_bind_param($stmtCheckManagerRecord, "i", $employee_id);
    mysqli_stmt_execute($stmtCheckManagerRecord);
    $resultCheckManagerRecord = mysqli_stmt_get_result($stmtCheckManagerRecord);
    $managerRecord = mysqli_fetch_assoc($resultCheckManagerRecord);
    mysqli_stmt_close($stmtCheckManagerRecord);

    // Fetch the manager record if it exists
    if ($managerRecord) {
        // If the employee is an active manager (end_date is NULL), update the Department table
        if ($managerRecord['end_date'] === null) {
            $sqlUpdateDepartment = "UPDATE Department SET manager_id = NULL WHERE manager_id = ?";
            $stmtUpdateDepartment = mysqli_prepare($conn, $sqlUpdateDepartment);
            mysqli_stmt_bind_param($stmtUpdateDepartment, "i", $employee_id);
            mysqli_stmt_execute($stmtUpdateDepartment);
            mysqli_stmt_close($stmtUpdateDepartment);
        }

        // Delete the manager record from the Manager table
        $sqlDeleteManager = "DELETE FROM Manager WHERE manager_id = ?";
        $stmtDeleteManager = mysqli_prepare($conn, $sqlDeleteManager);
        mysqli_stmt_bind_param($stmtDeleteManager, "i", $employee_id);
        mysqli_stmt_execute($stmtDeleteManager);
        mysqli_stmt_close($stmtDeleteManager);
    }

    // Delete the employee from Employee table
    $sqlDeleteEmployee = "DELETE FROM Employee WHERE employee_id = ?";
    $stmtDeleteEmployee = mysqli_prepare($conn, $sqlDeleteEmployee);
    mysqli_stmt_bind_param($stmtDeleteEmployee, "i", $employee_id);
    mysqli_stmt_execute($stmtDeleteEmployee);
    mysqli_stmt_close($stmtDeleteEmployee);

    // Now delete the user from the User table
    $sqlDeleteUser = "DELETE FROM `User` WHERE user_id = ?";
    $stmtDeleteUser = mysqli_prepare($conn, $sqlDeleteUser);
    mysqli_stmt_bind_param($stmtDeleteUser, "s", $user_id);
    mysqli_stmt_execute($stmtDeleteUser);
    mysqli_stmt_close($stmtDeleteUser);

    // Log successful delete in AuditLog
    $actionId = 5; 
    $statusMessage = "Success";
    $details = "Deleted employee with ID: $deletedEmployeeId.";
    $ipAddress = $_SERVER['REMOTE_ADDR']; 
    
    insertAuditLog($conn, $_SESSION['user_id'], $actionId, $statusMessage, $details, $ipAddress);

    // Success
    $message = "Employee deleted successfully.";
    header("Location: hr_crud.php?message=" . urlencode($message));
    exit();
} else {
    // If user_id is not found for the employee
    $error = urlencode("Failed to delete employee (id: $employee_id).");
    header("Location: hr_crud.php?error=$error");
    exit();
}

// Close the MySQL connection
mysqli_close($conn);
?>

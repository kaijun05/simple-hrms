<?php
include('../src/config.php'); 
include('../src/error_handler.php');

// Validate the session (for authenticated users only)
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<option value=''>Unauthorized</option>";
    exit;
}

// Retrieve and validate role_id and department_id from POST data
$role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : null;
$department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;

if (!$role_id || !$department_id || $role_id <= 0 || $department_id <= 0) {
    echo "<option value=''>Invalid Selection</option>";
    exit;
}


// Query to fetch job titles based on role and department
$sql = "SELECT job_title_id, job_title_name 
        FROM JobTitle 
        WHERE role_id = ? AND department_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    logError("SQL Prepare Error: " . mysqli_error($conn));
    echo "<option value=''>Database Error</option>";
    exit;
}

// Bind parameters (i = integer, i = integer)
mysqli_stmt_bind_param($stmt, "ii", $role_id, $department_id);
// Execute the statement
mysqli_stmt_execute($stmt);
// Get the result
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    logError("Query Execution Error: " . mysqli_error($conn));
    echo "<option value=''>Failed to fetch job titles</option>";
    exit;
}

// Generate dropdown options
// ENT_QUOTES - Encodes double and single quotes
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='" . htmlspecialchars($row['job_title_id'], ENT_QUOTES, 'UTF-8') . "'>" 
             . htmlspecialchars($row['job_title_name'], ENT_QUOTES, 'UTF-8') 
             . "</option>";
    }
} else {
    echo "<option value=''>No Job Titles Available</option>";
}

// Close the statement and connection
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>


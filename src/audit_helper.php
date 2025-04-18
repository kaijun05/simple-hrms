<?php
function insertAuditLog($conn, $userId, $actionId, $statusMessage, $details, $ipAddress)
{
    // Clear any previous results
    while(mysqli_next_result($conn)) {;}
    
    // SQL query to insert a record into AuditLog
    $sql = "INSERT INTO AuditLog (user_id, action_id, status_message, details, ip_address)
            VALUES (?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        error_log("Failed to prepare SQL statement: " . mysqli_error($conn));
        return false;
    }

    // Bind parameters (s = string, i = integer)
    mysqli_stmt_bind_param($stmt, "sisss", $userId, $actionId, $statusMessage, $details, $ipAddress);

    // Execute the statement and return the result
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return true;
    } else {
        error_log("Failed to execute SQL statement: " . mysqli_error($conn));
        mysqli_stmt_close($stmt);
        return false;
    }
}
?>

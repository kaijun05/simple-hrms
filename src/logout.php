<?php
session_start();

session_unset();
session_destroy();

header("Location: login.php");
exit();

// include('../src/config.php');
// include('../src/audit_helper.php'); 


// if (isset($_SESSION['user_id'])) {
//     $userId = $_SESSION['user_id'];

//     // Log the logout event
//     $actionId = 2;
//     $statusMessage = "Success";
//     $details = "Logout success.";
//     $ipAddress = $_SERVER['REMOTE_ADDR']; 

//     $logoutSuccess = insertAuditLog($conn, $userId, $actionId, $statusMessage, $details, $ipAddress);
    
//     if ($logoutSuccess) {
//         session_unset();
//         session_destroy();
//         header("Location: login.php");
//         exit();
//     } 

// } else {
//     $actionId = 2; 
//     $statusMessage = "Failed";
//     $details = "Logout attempt failed.";
//     $ipAddress = $_SERVER['REMOTE_ADDR']; 

//     $logoutSuccess = insertAuditLog($conn, null, $actionId, $statusMessage, $details, $ipAddress);
    
//     logError("Logout attempt failed: No active session found.");
//     header("Location: login.php");
//     exit(); 
// }

?>
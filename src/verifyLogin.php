<?php
session_start();
include('../src/config.php');
include('../src/error_handler.php');
include('../src/audit_helper.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);  // Sanitize email
    $password = trim($_POST['password']);

    // Query only necessary columns using a stored procedure 
    $sql = "CALL GetUserForLogin(?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        logError("Failed to prepare GetUserForLogin stored procedure.", mysqli_error($conn));
        header("Location: error.php?msg=Internal error occurred");
        exit();
    }

    // Execute and get results
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Clear any remaining result sets
    while ($stmt->more_results()) {
        $stmt->next_result();
    }

    if ($user && password_verify($password, $user['password'])) {
        // Store user details in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];

        // Fetch role-specific details
        if (in_array($user['role_id'], [2, 3])) {
            try {
                $roleSpecificId = fetchRoleSpecificId($user['role_id'], $user['user_id'], $conn);
                
                if ($roleSpecificId) {
                    if ($user['role_id'] == 2) {
                        $_SESSION['manager_id'] = $roleSpecificId;
                    } elseif ($user['role_id'] == 3) {
                        $_SESSION['employee_id'] = $roleSpecificId;
                    }
                } else {
                    logError("No role-specific ID found for user: {$user['user_id']}", "");
                    header("Location: error.php?msg=Account setup incomplete");
                    exit();
                }
            } catch (Exception $e) {
                logError("Error fetching role-specific ID", $e->getMessage());
                header("Location: error.php?msg=Internal error occurred");
                exit();
            }
        }

        // Redirect based on role
        redirectToRolePage($user['role_id']);
    }

    // Log failed login attempt
    $actionId = 1; 
    $statusMessage = "Failed";
    $details = "Login attempt failed.";
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    insertAuditLog($conn, null, $actionId, $statusMessage, $details, $ipAddress);

    
    // Invalid credentials
    $error = urlencode('Invalid login credentials. Please try again.');
    header("Location: login.php?error=$error");
    exit();
}

// Function to fetch role-specific IDs
function fetchRoleSpecificId($roleId, $userId, $conn) {
    $sql = ($roleId == 2)
        ? "SELECT manager_id AS role_specific_id FROM Manager WHERE manager_id = (SELECT employee_id FROM Employee WHERE user_id = ?)"
        : "SELECT employee_id AS role_specific_id FROM Employee WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare role-specific ID query: " . mysqli_error($conn));
    }

    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['role_specific_id'] ?? null;
}

// Function to redirect based on role
function redirectToRolePage($roleId) {
    switch ($roleId) {
        case 1: // HR Admin
            header("Location: hr.php");
            break;
        case 2: // Manager
            header("Location: manager.php");
            break;
        case 3: // Employee
            header("Location: employee.php");
            break;
        default:
            header("Location: unauthorized.php");
            break;
    }
    exit();
}
?>

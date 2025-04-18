<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('../src/config.php');
    include('../src/error_handler.php');
    include('../src/audit_helper.php'); 

    // Get data and sanitize input to prevent SQL injection
    $fname = mysqli_real_escape_string($conn, trim($_POST['fname']));
    $lname = mysqli_real_escape_string($conn, trim($_POST['lname']));
    $dob = $_POST['dob'];
    $country_of_birth = mysqli_real_escape_string($conn, $_POST['country_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);
    $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role_id = (int)$_POST['role_id'];
    $department_id = (int)$_POST['department_id'];
    $job_title_id = (int)$_POST['job_title_id'];
    $employment_status_id = (int)$_POST['employment_status_id'];
    $joined_date = $_POST['joined_date'];

    

    // Generate username, email, password
    // Generate the username
    $fname_lower = strtolower($fname);
    $lname_lower = strtolower($lname);
    $username = $fname_lower . $lname_lower;

    // Determine role and set email accordingly
    switch ($role_id) {
        case '1': 
            $role = 'hradmin';
            break;
        case '2': 
            $role = 'manager';
            break;
        case '3':
            $role = 'employee'; 
            break;
    }

    // Generate email 
    $email = "{$username}@{$role}.hrms.com";

    // Set default password
    $user_password = 'Str0ngPassw0rd'; 
    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);



    // If the role is manager, insert into Manager table and update the department table
    // Check if the department already has a manager
    if ($role_id == 2) { 
        $sqlCheckManager = "SELECT manager_id FROM Department WHERE department_id = ?";
        $stmtCheckManager = mysqli_prepare($conn, $sqlCheckManager);
        mysqli_stmt_bind_param($stmtCheckManager, "i", $department_id);
        mysqli_stmt_execute($stmtCheckManager);
        $resultCheckManager = mysqli_stmt_get_result($stmtCheckManager);
        $managerRow = mysqli_fetch_assoc($resultCheckManager);

        if ($managerRow && $managerRow['manager_id'] !== null) {
            $currentManagerId = $managerRow['manager_id'];
            header("Location: manager_conflict.php?department_id=$department_id&current_manager_id=$currentManagerId");
            exit();
        }
        mysqli_stmt_close($stmtCheckManager);
    }



    // Insert into User table
    $sqlUser = "INSERT INTO `User` (role_id, username, email, `password`) 
                VALUES (?, ?, ?, ?)";
    $stmtUser = mysqli_prepare($conn, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, "isss", $role_id, $username, $email, $hashed_password);

    if (!mysqli_stmt_execute($stmtUser)) {
        logError("Failed to insert into User table", mysqli_error($conn));
        $error = urlencode("Failed to create a new user.");
        header("Location: hr_create.php?error=$error");
        exit();
    }

    // Get the generated UUID
    $sqlGetUserId = "SELECT user_id FROM User WHERE username = ?";
    $stmtGetUserId = mysqli_prepare($conn, $sqlGetUserId);
    mysqli_stmt_bind_param($stmtGetUserId, "s", $username);
    mysqli_stmt_execute($stmtGetUserId);
    $result = mysqli_stmt_get_result($stmtGetUserId);
    $row = mysqli_fetch_assoc($result);
    $userId = $row['user_id'];
    mysqli_stmt_close($stmtGetUserId);

    if (!$userId) {
        die("Failed to retrieve User ID.");
    }



    // Insert into Employee table
    $sqlEmployee = "INSERT INTO Employee (user_id, department_id, employment_status_id, job_title_id, first_name, last_name, date_of_birth, country_of_birth, gender, contact_info, emergency_contact, address, joined_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtEmployee = mysqli_prepare($conn, $sqlEmployee);
    mysqli_stmt_bind_param($stmtEmployee, "siiisssssssss", 
        $userId, $department_id, $employment_status_id, $job_title_id, 
        $fname, $lname, $dob, $country_of_birth, 
        $gender, $contact_info, $emergency_contact, $address, $joined_date
    );

    if (!mysqli_stmt_execute($stmtEmployee)) {
        logError("Failed to insert into Employee table", mysqli_error($conn));
        $error = urlencode("Failed to add employee.");
        header("Location: hr_create.php?error=$error");
        exit();
    }

    // Retrieve the inserted Employee ID
    $employeeId = mysqli_insert_id($conn); 
    mysqli_stmt_close($stmtEmployee);

    if (!$employeeId) {
        die("Failed to retrieve Employee ID.");
    }



    // If the role is manager, proceed to set the manager in the Manager table and update the Department table
    if ($role_id == 2) {
        $sqlManager = "INSERT INTO Manager (manager_id, start_date) VALUES (?, NOW())";
        $stmtManager = mysqli_prepare($conn, $sqlManager);
        mysqli_stmt_bind_param($stmtManager, "i", $employeeId);

        if (!mysqli_stmt_execute($stmtManager)) {
            logError("Failed to insert into Manager table", mysqli_error($conn));
            $error = urlencode("Failed to add manager.");
            header("Location: hr_create.php?error=$error");
            exit();
        }
        mysqli_stmt_close($stmtManager);

        $sqlUpdateDepartment = "UPDATE Department SET manager_id = ?, updated_at = NOW() WHERE department_id = ?";
        $stmtUpdateDepartment = mysqli_prepare($conn, $sqlUpdateDepartment);
        mysqli_stmt_bind_param($stmtUpdateDepartment, "ii", $employeeId, $department_id);

        if (!mysqli_stmt_execute($stmtUpdateDepartment)) {
            logError("Failed to update the Department table", mysqli_error($conn));
            $error = urlencode("Failed to update department manager.");
            header("Location: hr_create.php?error=$error");
            exit();
        }
    }

    // Log successful create in AuditLog
    $actionId = 4; 
    $statusMessage = "Success";
    $details = "Created new employee with ID: $employeeId.";
    $ipAddress = $_SERVER['REMOTE_ADDR']; 
    
    insertAuditLog($conn, $_SESSION['user_id'], $actionId, $statusMessage, $details, $ipAddress);

    // Success message
    $message = "Employee added successfully";
    header("Location: hr_crud.php?message=" . urlencode($message));
    exit();
}

// Close connection
mysqli_close($conn);
?>

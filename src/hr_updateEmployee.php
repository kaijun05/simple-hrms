<?php
session_start();
include ('../src/config.php');
include('../src/audit_helper.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize data to prevent SQL injection
    $role_id = (int)$_POST['role_id'];
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $dob = $_POST['dob'];
    $country_of_birth = mysqli_real_escape_string($conn, $_POST['country_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);
    $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $department_id = (int)$_POST['department_id'];
    $job_title_id = (int)$_POST['job_title_id'];
    $employment_status_id = (int)$_POST['employment_status_id'];
    $employee_id = (int)$_POST['employee_id'];

    // Update Email Address 
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
    // Construct email 
    $email = $username . '@' . $role . '.hrms.com';



    // Fetch current data for the Employee
    $currentEmployeeQuery = "
        SELECT first_name, last_name, date_of_birth, country_of_birth, gender, 
            contact_info, emergency_contact, address, department_id, 
            job_title_id, employment_status_id 
        FROM Employee 
        WHERE employee_id = ?";
    $stmt = mysqli_prepare($conn, $currentEmployeeQuery);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $currentEmployee = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$currentEmployee) {
        die("Employee not found.");
    }



    // Fetch current data for the User
    $currentUserQuery = "SELECT role_id, email, username FROM User WHERE user_id = (SELECT user_id FROM Employee WHERE employee_id = ?)";
    $stmt = mysqli_prepare($conn, $currentUserQuery);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $currentUser = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$currentUser) {
        die("User not found.");
    }

    

    // Update Manager and Department Table
    
    // If the role is manager, insert into Manager table and update the department table
    // Check if the department already has a manager
    if ($role_id == 2) { 
        $sqlCheckManager = "SELECT manager_id FROM Department WHERE department_id = ?";
        $stmt = mysqli_prepare($conn, $sqlCheckManager);
        mysqli_stmt_bind_param($stmt, "i", $department_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $managerRow = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($managerRow && $managerRow['manager_id'] !== null) {
            header("Location: manager_conflict.php?department_id=$department_id&current_manager_id={$managerRow['manager_id']}");
            exit();
        }
    }

    // Helper function to update Manager table
    function updateManager($conn, $employee_id, $activate = true) {
        if ($activate) {
            // Reactivate or assign new manager
            $query = "UPDATE Manager SET end_date = NULL, start_date = NOW() WHERE manager_id = ?";
        } else {
            // Mark as no longer a manager
            $query = "UPDATE Manager SET end_date = NOW() WHERE manager_id = ?";
        }
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $employee_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Helper function to update Department table
    function updateDepartmentManager($conn, $department_id, $manager_id = null) {
        $query = "UPDATE Department SET manager_id = ?, updated_at = NOW() WHERE department_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ii", $manager_id, $department_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Check if the employee is or was a manager
    $checkManagerQuery = "SELECT manager_id, end_date FROM Manager WHERE manager_id = ?";
    $stmt = mysqli_prepare($conn, $checkManagerQuery);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $managerData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($role_id == 2) {
        // If the employee should be a manager
        if ($managerData) {
            // Reactivate manager if previously inactive
            if ($managerData['end_date'] !== null) {
                updateManager($conn, $employee_id, true);
            }
        } else {
            // Insert new manager record
            $insertManagerQuery = "INSERT INTO Manager (manager_id, start_date) VALUES (?, NOW())";
            $stmt = mysqli_prepare($conn, $insertManagerQuery);
            mysqli_stmt_bind_param($stmt, "i", $employee_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Update the Department table to set the new manager
        updateDepartmentManager($conn, $department_id, $employee_id);
    } else {
        // If the employee is no longer a manager
        if ($managerData && $managerData['end_date'] === null) {
            // Mark as no longer a manager
            updateManager($conn, $employee_id, false);

            // Remove manager from the Department table
            updateDepartmentManager($conn, $department_id, null);
        }
    }



    // Prepare the details of what was updated
    $updatedFields = [];
    // User table update
    $userUpdates = [];
    $userParams = [];

    if ($role_id && $role_id != $currentUser['role_id']) {
        $userUpdates[] = "role_id = ?";
        $userParams[] = $role_id;
        $updatedFields[] = "Role";
    }

    if ($username && $username != $currentUser['username']) {
        $userUpdates[] = "username = ?";
        $userParams[] = $username;
    }

    if ($email && $email != $currentUser['email']) {
        $userUpdates[] = "email = ?";
        $userParams[] = $email;
    }

    if (!empty($userUpdates)) {
        $updateUserQuery = "UPDATE User SET " . implode(", ", $userUpdates) . " WHERE user_id = (SELECT user_id FROM Employee WHERE employee_id = ?)";
        $userParams[] = $employee_id;
        $stmt = mysqli_prepare($conn, $updateUserQuery);
        mysqli_stmt_bind_param($stmt, str_repeat("s", count($userParams)), ...$userParams);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Employee table update
    $employeeUpdates = [];
    $employeeParams = [];

    if ($fname && $fname != $currentEmployee['first_name']) {
        $employeeUpdates[] = "first_name = ?";
        $employeeParams[] = $fname;
        $updatedFields[] = "First Name";
    }
    if ($lname && $lname != $currentEmployee['last_name']) {
        $employeeUpdates[] = "last_name = ?";
        $employeeParams[] = $lname;
        $updatedFields[] = "Last Name";
    }
    if ($dob && $dob != $currentEmployee['date_of_birth']) {
        $employeeUpdates[] = "date_of_birth = ?";
        $employeeParams[] = $dob;
        $updatedFields[] = "Date of Birth";
    }
    if ($country_of_birth && $country_of_birth != $currentEmployee['country_of_birth']) {
        $employeeUpdates[] = "country_of_birth = ?";
        $employeeParams[] = $country_of_birth;
        $updatedFields[] = "Country of Birth";
    }
    if ($gender && $gender != $currentEmployee['gender']) {
        $employeeUpdates[] = "gender = ?";
        $employeeParams[] = $gender;
        $updatedFields[] = "Gender";
    }
    if ($contact_info && $contact_info != $currentEmployee['contact_info']) {
        $employeeUpdates[] = "contact_info = ?";
        $employeeParams[] = $contact_info;
        $updatedFields[] = "Contact Info";
    }
    if ($emergency_contact && $emergency_contact != $currentEmployee['emergency_contact']) {
        $employeeUpdates[] = "emergency_contact = ?";
        $employeeParams[] = $emergency_contact;
        $updatedFields[] = "Emergency Contact";
    }
    if ($address && $address != $currentEmployee['address']) {
        $employeeUpdates[] = "address = ?";
        $employeeParams[] = $address;
        $updatedFields[] = "Address";
    }
    if ($department_id && $department_id != $currentEmployee['department_id']) {
        $employeeUpdates[] = "department_id = ?";
        $employeeParams[] = $department_id;
        $updatedFields[] = "Department";
    }
    if ($job_title_id && $job_title_id != $currentEmployee['job_title_id']) {
        $employeeUpdates[] = "job_title_id = ?";
        $employeeParams[] = $job_title_id;
        $updatedFields[] = "Job Title";
    }
    if ($employment_status_id && $employment_status_id != $currentEmployee['employment_status_id']) {
        $employeeUpdates[] = "employment_status_id = ?";
        $employeeParams[] = $employment_status_id;
        $updatedFields[] = "Employment Status";
    }
    

    if (!empty($employeeUpdates)) {
        $updateEmployeeQuery = "UPDATE Employee SET " . implode(", ", $employeeUpdates) . ", updated_at = NOW() WHERE employee_id = ?";
        $employeeParams[] = $employee_id;
        
        $typeString = str_repeat("s", count($employeeParams) - 1) . "i"; // Ensures the last one is integer
        $stmt = mysqli_prepare($conn, $updateEmployeeQuery);
        
        if ($stmt === false) {
            die("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, $typeString, ...$employeeParams);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
        

    // Log successful update in AuditLog
    $actionId = 3; 
    $statusMessage = "Success";
    $details = "(ID: $employee_id) Updated fields: " . implode(", ", $updatedFields);
    $ipAddress = $_SERVER['REMOTE_ADDR']; 
          
    insertAuditLog($conn, $_SESSION['user_id'], $actionId, $statusMessage, $details, $ipAddress);

    header("Location: hr_crud.php?id=$employee_id&message=Employee updated successfully (id: $employee_id)");
    exit();
}

// Close database connection
mysqli_close($conn);
?>

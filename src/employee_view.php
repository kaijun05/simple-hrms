<?php
session_start();
include('../src/config.php');

// Check if employee session is active
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php?error=You need to log in first.");
    exit();
}

// Get employee ID from session
$employee_id = filter_var($_SESSION['employee_id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch employee details
$sql = "
    SELECT 
        e.employee_id, 
        e.first_name, 
        e.last_name, 
        e.date_of_birth,
        e.country_of_birth,
        e.gender,
        e.contact_info, 
        e.emergency_contact,
        e.address,
        e.department_id, 
        r.role_name,
        d.department_name, 
        e.employment_status_id, 
        es.employment_status_name,
        e.job_title_id, 
        jt.job_title_name,

        m.first_name AS manager_first_name,   
        m.last_name AS manager_last_name 

    FROM 
        Employee e
    JOIN 
        User u ON e.user_id = u.user_id
    JOIN 
        Role r ON u.role_id = r.role_id     
    JOIN 
        Department d ON e.department_id = d.department_id
    JOIN 
        EmploymentStatus es ON e.employment_status_id = es.employment_status_id

    JOIN 
        JobTitle jt ON e.job_title_id = jt.job_title_id
                
    LEFT JOIN 
        Employee m ON d.manager_id = m.employee_id

    WHERE 
        e.employee_id = ?;
    ";
    
// Prepare and execute the query
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query preparation failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query execution failed: " . mysqli_error($conn));
}
    
// Fetch employee data
$employee = mysqli_fetch_assoc($result);
    
if (!$employee) {
    die("Employee not found.");
}
    
// Format date of birth
$date_of_birth = !empty($employee['date_of_birth']) ? htmlspecialchars($employee['date_of_birth']) : '';

// Handle manager name
$manager_name = 'No Manager Assigned';
if (!empty($employee['manager_first_name']) && !empty($employee['manager_last_name'])) {
    $manager_name = htmlspecialchars($employee['manager_first_name'] . " " . $employee['manager_last_name']);
}

// Free result and close the statement
mysqli_free_result($result);
mysqli_stmt_close($stmt);

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Information</title>
    <link rel="stylesheet" href="../styles/employee_view.css">
</head>
<body>

    <div class="container">
        <h2>View My Information</h2>
        <form>
            <!-- Personal Information Section -->
            <fieldset>
                <legend>Personal Information</legend>
                <div class="form-row">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($employee['first_name']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($employee['last_name'])  ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($date_of_birth)  ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="country_of_birth">Country of Birth</label>
                    <input type="text" id="country_of_birth" name="country_of_birth" value="<?= htmlspecialchars($employee['country_of_birth'])  ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="gender">Gender</label>
                    <input type="text" id="gender" name="gender" value="<?= htmlspecialchars($employee['gender']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="contact">Contact Number</label>
                    <input type="tel" id="contact" name="contact" value="<?= htmlspecialchars($employee['contact_info']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="emergency_contact">Emergency Contact</label>
                    <input type="tel" id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($employee['emergency_contact']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" readonly style="width: 65%; height: 100px; resize: none;"><?= htmlspecialchars($employee['address']) ?></textarea>
                </div>
            </fieldset>

            <!-- Job Information Section -->
            <fieldset>
                <legend>Job Information</legend>
                <div class="form-row">
                    <label for="role_id">Role</label>
                    <input type="text" id="role_id" name="role_id" value="<?= htmlspecialchars($employee['role_name']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="department_id">Department</label>
                    <input type="text" id="department_id" name="department_id" value="<?= htmlspecialchars($employee['department_name']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="manager_id">Manager</label>
                    <input type="text" id="manager_id" name="manager_id" value="<?= htmlspecialchars($employee['manager_first_name'] . " " . $employee['manager_last_name']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="job_title">Job Title</label>
                    <input type="text" id="job_title" name="job_title" value="<?= htmlspecialchars($employee['job_title_name']) ?>" readonly>
                </div>
                <div class="form-row">
                    <label for="manager_id">Employment Status</label>
                    <input type="text" id="employment_status" name="employment_status" value="<?= htmlspecialchars($employee['employment_status_name']) ?>" readonly>
                </div>
            </fieldset>

            <div class="button-container">
                <a href="../src/employee.php" class="back-button">← Back to Dashboard</a>
                <a href="../src/employee_update.php" class="update-button">Update Personal Information</a>
            </div>
        </form>
    </div>
</body>
</html>
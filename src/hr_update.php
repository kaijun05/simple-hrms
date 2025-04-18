<?php
session_start();
include ('../src/config.php');

$hr_user_id = $_SESSION['user_id']; 
$hr_user_role = $_SESSION['role_id'];

if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    $query = "SELECT * FROM Employee WHERE employee_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $employee_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $employee = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$employee) {
        echo "Employee not found.";
        exit;
    }
} else {
    die("No ID provided.");
}

// Format Date of Birth
$date_of_birth = date("Y-m-d", strtotime($employee['date_of_birth']));



// 1. Role
// Fetch all available roles
$role_query = "SELECT role_id, role_name FROM Role";
$role_result = mysqli_query($conn, $role_query);

// Fetch current role of the user
$user_id = $employee['user_id']; 

$user_role_query = "SELECT role_id FROM User WHERE user_id = ?"; 
$stmt = mysqli_prepare($conn, $user_role_query);
mysqli_stmt_bind_param($stmt, "s", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user_role = mysqli_fetch_assoc($result)) {
    $current_role_id = $user_role['role_id']; 
} else {
    $current_role_id = null; // Prevent it from defaulting incorrectly
}

mysqli_stmt_close($stmt);

// Store available roles in an array
$roles = mysqli_fetch_all($role_result, MYSQLI_ASSOC);



// 2. Department
// Fetch all departments from the department table
$department_query = "SELECT department_id, department_name FROM Department";
$department_result = mysqli_query($conn, $department_query);

// Fetch the current employee's department_id
$employee_department_query = "
    SELECT department_id
    FROM Employee
    WHERE employee_id = ?";
$stmt = mysqli_prepare($conn, $employee_department_query);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employee_department = mysqli_fetch_assoc($result);
$current_department_id = $employee_department['department_id'];
mysqli_stmt_close($stmt);

// Store Departments in an array
$departments = mysqli_fetch_all($department_result, MYSQLI_ASSOC);



// 3. Job Title
// Fetch all job titles from the job_title table
$job_title_query = "SELECT job_title_id, job_title_name FROM JobTitle";
$job_title_result = mysqli_query($conn, $job_title_query);

// Fetch the current employee's job_title_id
$employee_job_title_query = "
    SELECT job_title_id
    FROM Employee
    WHERE employee_id = ?";
$stmt = mysqli_prepare($conn, $employee_job_title_query);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employee_job_title = mysqli_fetch_assoc($result);
$current_job_title_id = $employee_job_title['job_title_id'];
mysqli_stmt_close($stmt);

// Store Job Titles in an array
$job_titles = mysqli_fetch_all($job_title_result, MYSQLI_ASSOC);



// 4. Employment Status 
$employment_status_query = "SELECT employment_status_id, employment_status_name FROM EmploymentStatus";
$employment_status_result = mysqli_query($conn, $employment_status_query);

$employment_status_query_for_current = "
    SELECT employment_status_id
    FROM Employee
    WHERE employee_id = ?";
$stmt = mysqli_prepare($conn, $employment_status_query_for_current);
mysqli_stmt_bind_param($stmt, "i", $employee_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$employment_status = mysqli_fetch_assoc($result);
$current_employment_status_id = $employment_status['employment_status_id'];
mysqli_stmt_close($stmt);

// Store Employment Statuses in an array
$employment_statuses = mysqli_fetch_all($employment_status_result, MYSQLI_ASSOC);

// Define the list of countries
$countries = require_once 'countries_config.php';


// Check if HR Admin is updating their own record
$is_hr_admin = ($hr_user_role == 1 && $employee['user_id'] == $hr_user_id);


// Check if a success or error message is set
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Employee</title>
    <link rel="stylesheet" href="../styles/hr_create.css">
    <script src="../js/fetch_job_title.js" defer></script>
    <script src="../js/input_validation_hr_update.js" defer></script>
</head>
<body>
    <div class="container">
        <h2>Update Employee Information</h2>
        <form action="hr_updateEmployee.php" method="POST" onsubmit="return validateForm(event)">
            <!-- Hidden field to pass the employee_id -->
            <input type="hidden" name="employee_id" value="<?= $employee['employee_id'] ?>">
            
            <!-- Personal Information Section -->
            <fieldset>
                <legend>Personal Information</legend>
                <div class="form-row">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" name="fname" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
                </div>
                <div id="error-fname" class="error"></div>

                <div class="form-row">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" name="lname" value="<?= htmlspecialchars($employee['last_name']) ?>" required>
                </div>
                <div id="error-lname" class="error"></div>

                <div class="form-row">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($date_of_birth) ?>" required>
                </div>
                <div id="error-dob" class="error"></div>

                <div class="form-row">
                    <label for="country_of_birth">Country of Birth</label>
                    <select id="country_of_birth" name="country_of_birth" required>
                        <option value="">Select Country of Birth</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= htmlspecialchars($country) ?>" 
                                <?= htmlspecialchars($employee['country_of_birth']) == $country ? 'selected' : '' ?>>
                                <?= htmlspecialchars($country) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?= htmlspecialchars($employee['gender']) == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= htmlspecialchars($employee['gender']) == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Others" <?= htmlspecialchars($employee['gender']) == 'Others' ? 'selected' : '' ?>>Others</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="contact_info">Contact Number</label>
                    <input type="tel" id="contact_info" name="contact_info" value="<?= htmlspecialchars($employee['contact_info']) ?>" required>
                </div>
                <div id="error-contact_info" class="error"></div>

                <div class="form-row">
                    <label for="emergency_contact">Emergency Contact</label>
                    <input type="tel" id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($employee['emergency_contact']) ?>" required>
                </div>
                <div id="error-emergency_contact" class="error"></div>

                <div class="form-row">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" style="width: 65%; height: 100px; resize: none;" required><?= htmlspecialchars($employee['address']) ?></textarea>
                </div>
            </fieldset>

            <!-- Job Information Section -->
            <fieldset>
                <legend>Job Information</legend>
                
                
                <div class="form-row">
                    <label for="role_id">Role</label>
                    <?php if ($is_hr_admin): ?>
                        <!-- If HR Admin, show the role as text, but submit the role_id in a hidden field -->
                        <?php
                            // Find the role name from the roles array using the current role ID
                            $role_name = '';
                            foreach ($roles as $role) {
                                if ($role['role_id'] == $current_role_id) {
                                    $role_name = $role['role_name'];
                                    break;
                                }
                            }
                        ?>
                        <input type="text" value="<?= htmlspecialchars($role_name) ?>" readonly>
                        <input type="hidden" id="role_id" name="role_id" value="<?= htmlspecialchars($current_role_id) ?>">
                    <?php else: ?>
                        <select id="role_id" name="role_id" onchange="updateJobTitles()" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['role_id']) ?>" 
                                <?= $role['role_id'] == $current_role_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" onchange="updateJobTitles()" required>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= htmlspecialchars($department['department_id']) ?>" 
                            <?= $department['department_id'] == $current_department_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($department['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="job_title_id">Job Title</label>
                    <select id="job_title_id" name="job_title_id" required>
                        <?php foreach ($job_titles as $job_title): ?>
                            <option value="<?= htmlspecialchars($job_title['job_title_id']) ?>" 
                                <?= $job_title['job_title_id'] == $current_job_title_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($job_title['job_title_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="employment_status_id">Employee Status</label>
                    <select id="employment_status_id" name="employment_status_id" required>
                        <?php foreach ($employment_statuses as $status): ?>
                            <option value="<?= htmlspecialchars($status['employment_status_id']) ?>" 
                                <?= $status['employment_status_id'] == $current_employment_status_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status['employment_status_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <div class="form-row">
                <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">
                <button type="submit">Update Employee</button>
            </div>
        </form>
        <a href="../src/hr_crud.php" class="back-button">← Back to Employee List</a>

        <!-- Notification -->
        <?php if ($message || $error): ?>
            <div class="notification-box <?php echo $message ? 'success' : 'error'; ?>">
                <p><?php echo htmlspecialchars($message ?: $error); ?></p>
                <a href="../src/hr_create.php">Close</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>

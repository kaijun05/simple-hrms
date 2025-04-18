<?php
session_start();
include('../src/config.php');
include('../src/error_handler.php');
include('../src/audit_helper.php'); 

// Check if employee session is active
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php?error=You need to log in first.");
    exit();
}

// Get employee ID from session
$employee_id = filter_var($_SESSION['employee_id'], FILTER_SANITIZE_NUMBER_INT);

// Fetch employee details
$queryCurrentEmployee = "SELECT * FROM Employee WHERE employee_id = ?";
$stmtCurrentEmployee = mysqli_prepare($conn, $queryCurrentEmployee);
mysqli_stmt_bind_param($stmtCurrentEmployee, "i", $employee_id);
mysqli_stmt_execute($stmtCurrentEmployee);
$resultCurrentEmployee = mysqli_stmt_get_result($stmtCurrentEmployee);
$employee = mysqli_fetch_assoc($resultCurrentEmployee);
mysqli_stmt_close($stmtCurrentEmployee);

if (!$employee) {
    logError("No employee found or query failed", $conn);
    die("No employee found.");
}

// Format DOB
$date_of_birth = date("Y-m-d", strtotime($employee['date_of_birth']));

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Retrieve and sanitize POST data
   $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
   $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
   $gender = mysqli_real_escape_string($conn, $_POST['gender']);
   $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
   $country_of_birth = mysqli_real_escape_string($conn, $_POST['country_of_birth']);
   $contact_info = mysqli_real_escape_string($conn, $_POST['contact_info']);
   $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
   $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Track updated fields
    $updatedFields = [];
    if ($first_name !== $employee['first_name']) $updatedFields[] = "First Name";
    if ($last_name !== $employee['last_name']) $updatedFields[] = "Last Name";
    if ($gender !== $employee['gender']) $updatedFields[] = "Gender";
    if ($date_of_birth !== $employee['date_of_birth']) $updatedFields[] = "Date of Birth";
    if ($country_of_birth !== $employee['country_of_birth']) $updatedFields[] = "Country of Birth";
    if ($contact_info !== $employee['contact_info']) $updatedFields[] = "Contact Info";
    if ($emergency_contact !== $employee['emergency_contact']) $updatedFields[] = "Emergency Contact";
    if ($address !== $employee['address']) $updatedFields[] = "Address";

    // SQL query to update employee information
    $queryUpdateEmployee = "
        UPDATE Employee SET 
            first_name = ?, 
            last_name = ?, 
            gender = ?, 
            date_of_birth = ?, 
            country_of_birth = ?, 
            contact_info = ?, 
            emergency_contact = ?, 
            address = ? 
        WHERE employee_id = ?
    ";

    $stmtUpdateEmployee = mysqli_prepare($conn, $queryUpdateEmployee);
    mysqli_stmt_bind_param($stmtUpdateEmployee, "ssssssssi", 
    $first_name, $last_name, $gender, $date_of_birth, $country_of_birth, 
    $contact_info, $emergency_contact, $address, $employee_id);

    if (!mysqli_stmt_execute($stmtUpdateEmployee)) {
        $error = urlencode("Failed to update employee details.");
        header("Location: employee_update.php?error=$error");
        exit();
    }
    mysqli_stmt_close($stmtUpdateEmployee);

    // Update username and email in [User] table
    if (!updateUserDetails($conn, $employee_id, $first_name, $last_name)) {
        header("Location: employee_update.php?error=" . urlencode("Failed to update username and email."));
        exit();
    }

    // Audit log details
    $actionId = 3; 
    $statusMessage = "Success";
    $details = "Updated fields: " . implode(", ", $updatedFields);
    $ipAddress = $_SERVER['REMOTE_ADDR']; 
          
    insertAuditLog($conn, $_SESSION['user_id'], $actionId, $statusMessage, $details, $ipAddress);

    
    // Redirect with success message
    header("Location: employee_update.php?message=Employee updated successfully");
    exit();
}

// Function to update username and email in [User] table
function updateUserDetails($conn, $employee_id, $first_name, $last_name) {
    // Fetch user_id for the employee
    $queryUserId = "SELECT user_id FROM Employee WHERE employee_id = ?";
    $stmtUserId = mysqli_prepare($conn, $queryUserId);
    mysqli_stmt_bind_param($stmtUserId, "i", $employee_id);
    mysqli_stmt_execute($stmtUserId);
    $resultUserId = mysqli_stmt_get_result($stmtUserId);
    $user = mysqli_fetch_assoc($resultUserId);
    mysqli_stmt_close($stmtUserId);

    if (!$user) {
        return false;
    }

    // Generate new username and email
    $user_id = $user['user_id'];
    $username = strtolower($first_name . $last_name);
    $email = "{$username}@employee.hrms.com"; // Adjust domain based on role

    // Update [User] table
    $queryUpdateUser = "
        UPDATE User
        SET username = ?, email = ?
        WHERE user_id = ?
    ";
    $stmtUpdateUser = mysqli_prepare($conn, $queryUpdateUser);
    mysqli_stmt_bind_param($stmtUpdateUser, "sss", $username, $email, $user_id);
    $success = mysqli_stmt_execute($stmtUpdateUser);
    mysqli_stmt_close($stmtUpdateUser);

    return $success;
}

// Define the list of countries
$countries = require_once 'countries_config.php';

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
    <link rel="stylesheet" href="../styles/employee_update.css">
    <script src="../js/input_validation_employee.js" defer></script>
</head>
<body>
    <div class="container">
        <h2>Update My Information</h2>
        <form action="employee_update.php" method="POST" onsubmit="return validateForm(event)">
            <!-- Hidden field to pass the employee_id -->
            <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>">
            
            <!-- Personal Information Section -->
            <fieldset>
                <legend>Personal Information</legend>
                <div class="form-row">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" 
                    value="<?= $employee['first_name'] ?>"
                    required>
                </div>
                <div id="error-fname" class="error"></div>

                <div class="form-row">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name"
                    value="<?= $employee['last_name'] ?>" 
                    required>
                </div>
                <div id="error-lname" class="error"></div>

                <div class="form-row">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($employee['date_of_birth']) ?>" required>
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

            <div class="form-row">
                <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">
                <button type="submit">Update My Information</button>
            </div>
        </form>
        <a href="../src/employee_view.php" class="back-button">← Back to View My Information</a>

        <!-- Notification Box in HTML -->
        <?php if ($message || $error): ?>
            <div class="notification-box <?php echo $message ? 'success' : 'error'; ?>">
                <p><?php echo htmlspecialchars($message ?: $error); ?></p>
                <a href="../src/employee_update.php">Close</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
<?php
session_start();
include('../src/config.php');

// Check if session variables exist and validate role
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: unauthorized.php');
    exit();
}

// Securely validate logged-in user using user_id
$loggedInUserId = $_SESSION['user_id'];
$validateQuery = "SELECT employee_id FROM Employee WHERE user_id = ?";

// Prepare, bind, and execute to get the results
$validateStmt = $conn->prepare($validateQuery);
$validateStmt->bind_param("s", $loggedInUserId);
$validateStmt->execute();
$validateResult = $validateStmt->get_result();

if (!$validateResult) {
    logError("Failed to validate logged-in user.", mysqli_error($conn));
    header('Location: logout.php');
    exit();
}

// Fetch the employee_id for the logged-in user
$loggedInEmployeeId = null;

if ($validateResult->num_rows > 0) {
    $result = $validateResult->fetch_assoc();
    $loggedInEmployeeId = $result['employee_id'];
} else {
    logError("Invalid session: Employee ID not found.");
    header('Location: logout.php');
    exit();
}

// Check if a success or error message is set
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="../styles/hr_crud.css">
</head>
<body>
<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
</form>
    
<div class="container">
    <a href="../src/hr.php" class="back-button">← Back to Dashboard</a>
    
    <h2>Employee List</h2>

    <!-- Add New Employee -->
    <a href="../src/hr_create.php" class="add-button">Add New Employee</a>

    <!-- Notifications -->
    <?php if ($message || $error): ?>
        <div class="notification-box <?php echo $message ? 'success' : 'error'; ?>">
            <p><?php echo $message ?: $error; ?></p>
            <a href="../src/hr_crud.php">Close</a>
        </div>
    <?php endif; ?>

    <!-- Employee Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Contact</th>
                <th>Department</th>
                <th>Status</th>
                <th>Job Title</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="employeeTable">
            <?php
            $sql = "
                SELECT 
                    e.employee_id, 
                    e.first_name, 
                    e.last_name, 
                    e.contact_info, 
                    e.department_id, 
                    d.department_name, 
                    e.employment_status_id, 
                    es.employment_status_name,
                    e.job_title_id, 
                    jt.job_title_name
                FROM 
                    Employee e
                JOIN 
                    Department d
                ON 
                    e.department_id = d.department_id
                JOIN 
                    EmploymentStatus es
                ON 
                    e.employment_status_id = es.employment_status_id
                JOIN 
                    JobTitle jt
                ON 
                    e.job_title_id = jt.job_title_id
                ORDER BY 
                    e.employee_id ASC;
                ";

            $result = $conn->query($sql);
        
            if (!$result) {
                logError("Failed to retrieve employees list", mysqli_error($conn));
                die("<p>Failed to load employee data. Please try again later.</p>");  
            }

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                    <td>" . htmlspecialchars($row['employee_id']) . "</td>
                    <td>" . htmlspecialchars($row['first_name']) . "</td>
                    <td>" . htmlspecialchars($row['last_name']) . "</td>
                    <td>" . htmlspecialchars($row['contact_info']) . "</td>
                    <td>" . htmlspecialchars($row['department_name']) . "</td>
                    <td>" . htmlspecialchars($row['employment_status_name']) . "</td>
                    <td>" . htmlspecialchars($row['job_title_name']) . "</td>
                    <td class='action-buttons'>
                        <a href='../src/hr_view.php?id={$row['employee_id']}' class='view-btn'>View</a>
                        <a href='../src/hr_update.php?id={$row['employee_id']}' class='update-btn'>Update</a>";
                    
                    if ($row['employee_id'] != $loggedInEmployeeId) {
                        echo "<a href='../src/hr_deleteEmployee.php?id={$row['employee_id']}' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this employee?\")'>Delete</a>";
                    }

                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='12' style='text-align: center;'> No employees found.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</div>
</body>
</html>

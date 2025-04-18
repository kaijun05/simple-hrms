<?php
session_start();
include('../src/config.php');

$manager_id = $_SESSION['manager_id'];

$query = "
    SELECT 
        e.employee_id,
        e.first_name,
        e.last_name,
        jt.job_title_name,
        e.joined_date,
        e.department_id,
        d.department_name
    FROM 
        Employee e
    JOIN 
        Department d ON e.department_id = d.department_id
    JOIN 
        Manager m ON d.manager_id = m.manager_id
    JOIN
        JobTitle jt ON e.job_title_id = jt.job_title_id
    WHERE 
        m.manager_id = ? AND e.employee_id != m.manager_id
    ORDER BY 
        e.employee_id;
    ";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Query preparation failed: " . mysqli_error($conn));
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, "i", $manager_id);
    // Execute the query
    mysqli_stmt_execute($stmt);
    // Get the result set
    $result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="../styles/manager_view.css">
</head>
<body>
<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-button">Log Out</button>
    </form>
    
    <div class="container">
        <a href="../src/manager.php" class="back-button">← Back to Dashboard</a>
        <h2>Employee List</h2>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Job Title Name</th>
                    <th>Joined Date</th>
                    <th>Department ID</th>
                    <th>Department Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $joined_date = $row['joined_date'] ? htmlspecialchars($row['joined_date']) : ''; 
                        echo "<tr>
                            <td>" . htmlspecialchars($row['employee_id']) . "</td>
                            <td>" . htmlspecialchars($row['first_name']) . "</td>
                            <td>" . htmlspecialchars($row['last_name']) . "</td>
                            <td>" . htmlspecialchars($row['job_title_name']) . "</td>
                            <td>" . htmlspecialchars($joined_date) . "</td>
                            <td>" . htmlspecialchars($row['department_id']) . "</td>
                            <td>" . htmlspecialchars($row['department_name']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>No employees found.</td></tr>";
                }

                // Free result and close statement
                mysqli_free_result($result);
                mysqli_stmt_close($stmt);

                // Close the database connection
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
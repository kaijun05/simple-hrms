<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="../styles/audit_logs.css">
</head>
<body>
<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
</form>
    
    <div class="container">
    <a href="../src/hr.php" class="back-button">← Back to Dashboard</a>
    
        <h2>Audit Logs</h2>

        <table>
            <thead>
                <tr>
                <th>Log ID</th>
                <th>Employee ID</th>
                <th>Action</th>
                <th>Status</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Timestamp</th>
                </tr>
            </thead>
            <tbody id="auditLogsTable">
                <?php
                    include('../src/config.php');         

                    $sql = "SELECT
                                    a.log_id, 
                                    e.employee_id,
                                    aa.action_name,
                                    a.status_message,
                                    a.details,
                                    a.ip_address,
                                    a.timestamp

                                FROM 
                                    AuditLog a

                                LEFT JOIN 
                                    User u ON a.user_id = u.user_id

                                LEFT JOIN 
                                    Employee e ON u.user_id = e.user_id

                                JOIN 
                                    AuditAction aa ON a.action_id= aa.action_id;
                                ";

                    $result = mysqli_query($conn, $sql);

                    if (!$result) {
                        die("Query failed: " . mysqli_error($conn));  
                    }

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {

                            // // Convert timestamp to a string format
                            // $timestamp = $row['timestamp'];
                            // if ($timestamp instanceof DateTime) {
                            //     $timestamp = $timestamp->format('Y-m-d H:i:s'); 
                            // }

                            echo "<tr>
                                <td>" . htmlspecialchars($row["log_id"]) . "</td>
                                <td>" . htmlspecialchars($row["employee_id"]) . "</td>
                                <td>" . htmlspecialchars($row["action_name"]) . "</td>
                                <td>" . htmlspecialchars($row["status_message"]) . "</td>
                                <td>" . htmlspecialchars($row["details"]) . "</td>
                                <td>" . htmlspecialchars($row["ip_address"]) . "</td>
                                <td>" . htmlspecialchars($row["timestamp"]) . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'> No logs found.</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

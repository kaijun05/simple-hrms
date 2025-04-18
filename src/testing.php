<?php
$serverName = "192.168.100.62";  
$connectionInfo = array(
    "Database" => "HRMS",
    // "UID" => "AppLogin",
    // "PWD" => 'mYc$<io27u@/z?9Z',
    "UID" => "DBAdmin",
    "PWD" => '9wjw2MOJd1T/FdG7',
    "Encrypt" => "yes",
    "TrustServerCertificate" => "yes"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    echo "Connection established.<br />";

    // Query to Retrieve Employee-Manager Relationship
    $sql = "
        SELECT
            e.employee_id AS employee_id,
            e.first_name AS employee_first_name,
            e.last_name AS employee_last_name,
            m.employee_id AS manager_id,
            m.first_name AS manager_first_name,
            m.last_name AS manager_last_name,
            d.department_name AS department_name
        FROM
            Employee e
        LEFT JOIN
            Department d ON e.department_id = d.department_id
        LEFT JOIN
            Manager mgr ON d.manager_id = mgr.manager_id
        LEFT JOIN
            Employee m ON mgr.manager_id = m.employee_id
        ORDER BY
            e.employee_id;
    ";

    // Execute the query
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Generate an HTML table to display the results
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Employee ID</th>";
    echo "<th>Employee First Name</th>";
    echo "<th>Employee Last Name</th>";
    echo "<th>Manager ID</th>";
    echo "<th>Manager First Name</th>";
    echo "<th>Manager Last Name</th>";
    echo "<th>Department Name</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    // Loop through query results and populate the table
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['manager_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['manager_first_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['manager_last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['department_name']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";

    // Free the statement and close the connection
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
} else {
    echo "Connection could not be established.<br />";
    die(print_r(sqlsrv_errors(), true));
}
?>

<!-- // Data Fetching Testing
    $query = "SELECT * FROM CreditCards";  
    $getResults = sqlsrv_query($conn, $query); 

    if ($getResults === false) {
        echo "Error (sqlsrv_query): " . print_r(sqlsrv_errors(), true);
        exit;
    }

    // Fetch and display the results
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>CardID</th>";
    echo "<th>CardType</th>";
    echo "<th>CardNumber</th>";
    echo "<th>ExpMonth</th>";
    echo "<th>ExpYear</th>";
    echo "<th>CustomerID</th>";
    echo "</tr>";

    while ($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['CardID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CardType']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CardNumber']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ExpMonth']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ExpYear']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CustomerID']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Free the result resource
    sqlsrv_free_stmt($getResults);
-->

<!-- // Data Insertion Testing
    // $query = "INSERT INTO Inventory (ItemID, ItemName, Quantity) VALUES (?, ?, ?)";
    // $params = array(3, "Choco", 200);  // The values for ItemName and Quantity
    // $stmt = sqlsrv_query($conn, $query, $params);

    // if ($stmt) {
    //     echo "Data inserted successfully.<br />";
    // } else {
    //     echo "Data insertion failed.<br />";
    //     die(print_r(sqlsrv_errors(), true));
    // }

    // Data Fetching Testing
    $query = "SELECT * FROM Inventory";  // Correctly referencing the query
    $getResults = sqlsrv_query($conn, $query);  // Pass $query, not $sql

    if ($getResults === false) {
        echo "Error (sqlsrv_query): " . print_r(sqlsrv_errors(), true);
        exit;
    }

    // Fetch and display the results
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ItemID</th>";
    echo "<th>ItemName</th>";
    echo "<th>Quantity</th>";
    echo "</tr>";

    while ($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ItemID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ItemName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Free the result resource
    sqlsrv_free_stmt($getResults); 
-->



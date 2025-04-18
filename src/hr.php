<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/hr.css">
</head>
<body>

<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
</form>

<div class="dashboard-container">
    <h2>Dashboard</h2>

    <div class="button-container">
        <!-- Employee List button -->
        <a href="../src/hr_crud.php">Employee List</a>

        <!-- Audit Logs button -->
        <a href="../src/audit_logs.php">Audit Logs</a>
    </div>
</div>

</body>
</html>

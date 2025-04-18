<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="../styles/employee.css">
</head>
<body>

<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
</form>

<div class="dashboard-container">
    <h2>Dashboard</h2>

    <div class="button-container">
        <!-- View Information button -->
        <a href="../src/employee_view.php">View My Information</a>
    </div>
</div>

</body>
</html>
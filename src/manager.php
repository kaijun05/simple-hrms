<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="../styles/manager.css">
</head>
<body>

<form action="logout.php" method="POST">
    <button type="submit" class="logout-button">Log Out</button>
</form>

<div class="dashboard-container">
    <h2>Dashboard</h2>

    <div class="button-container">
        <!-- Employee List button -->
        <a href="../src/manager_view.php">Employee List</a>
    </div>
</div>

</body>
</html>
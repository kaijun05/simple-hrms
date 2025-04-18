<!-- <?php
// Include database connection
include('../src/config.php');

// Fetch all users who have non-hashed passwords (assumes un-hashed passwords are less than 60 characters)
$query = "SELECT user_id, password FROM User WHERE LENGTH(password) < 60";  
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error fetching users: " . mysqli_error($conn));
}

$count = 0; // Track number of updated users
while ($row = mysqli_fetch_assoc($result)) {
    $user_id = $row['user_id'];
    $old_password = $row['password'];

    // Hash the old password using bcrypt
    $hashed_password = password_hash($old_password, PASSWORD_DEFAULT);

    // Update the password in the database
    $updateQuery = "UPDATE User SET password = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $count++;
    } else {
        echo "Failed to update password for user_id: $user_id\n";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
echo "Password migration completed! $count passwords updated.";
?> -->

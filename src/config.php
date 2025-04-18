<?php
// Load Composer autoload
require_once __DIR__ . '../../vendor/autoload.php';

// Load .env variables
// Reference: https://www.doppler.com/blog/configuring-php-applications-using-environment-variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
// Prevent failure if .env is missing
$dotenv->safeload();

// Ensure environment variables are set
$requiredEnv = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($requiredEnv as $envVar) {
    if (!isset($_ENV[$envVar]) || empty($_ENV[$envVar])) {
        die("Missing required environment variable: $envVar");
    }
}

// Use environment variables for database connection
$serverName = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$database = $_ENV['DB_DATABASE'];

// Establish connection using MySQLi
$conn = mysqli_connect($serverName, $username, $password, $database);

// Check for connection errors
if (!$conn) {
    $errorDetails = mysqli_connect_error();

    // Use the logError function for logging
    logError("Database Connection Failed", $errorDetails);

    die("Error Connecting to the Database, Please Try Again Later");
}

?>
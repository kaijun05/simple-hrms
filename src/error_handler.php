<?php
function logError($message, $conn = null, $logFile = __DIR__ . '/../logs/app_errors.log') {
    // Ensure the log directory exists
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }

    // Build the log message
    $logMessage = "[" . date("Y-m-d H:i:s") . "] [ERROR] " . $message;

    // Append MySQL errors if a database connection is provided
    if ($conn) {
        $sqlError = mysqli_error($conn); // Fetch MySQL error message
        if (!empty($sqlError)) {
            $logMessage .= " | MySQL Error: " . $sqlError;
        }
    }

    // Log the message to the specified file
    error_log($logMessage . PHP_EOL, 3, $logFile);
}
?>
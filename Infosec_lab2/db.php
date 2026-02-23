<?php
/**
 * DB.PHP - SECURED Database Connection
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Uses config.php for credentials
 * ✓ Proper error handling without exposing sensitive info
 * ✓ Sets character encoding to UTF-8
 * ✓ Implements logging for connection failures
 */

require_once('config.php');

// Establish secure connection
$conn = mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME
);

// Error handling - do NOT expose connection details to users
if (!$conn) {
    // Log error to file (never display to user)
    logError("Database connection failed: " . mysqli_connect_error());
    die("Database connection error. Please contact administrator.");
}

// Set character set to UTF-8 to prevent encoding attacks
mysqli_set_charset($conn, "utf8mb4");

/**
 * Function to securely log errors
 * 
 * @param string $message Error message to log
 * @return void
 */
function logError($message) {
    $logFile = ERROR_LOG_FILE;
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Log with timestamp
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    error_log($logEntry, 3, $logFile);
}

/**
 * Function to safely execute prepared statements
 * Prevents SQL Injection attacks
 * 
 * @param mysqli $connection Database connection
 * @param string $query SQL query with placeholders (?)
 * @param array $params Parameter values to bind
 * @param string $types Type string (s=string, i=integer, d=double, b=blob)
 * @return mysqli_result|bool Query result or false on failure
 */
function executeQuery($connection, $query, $params = [], $types = "") {
    if (empty($params)) {
        // No parameters, execute directly
        $result = mysqli_query($connection, $query);
        if (!$result) {
            logError("Query failed: " . mysqli_error($connection));
        }
        return $result;
    }
    
    // Prepare statement
    $stmt = mysqli_prepare($connection, $query);
    if (!$stmt) {
        logError("Prepare failed: " . mysqli_error($connection));
        return false;
    }
    
    // Bind parameters
    if (!empty($types)) {
        array_unshift($params, $types);
        if (!call_user_func_array('mysqli_stmt_bind_param', array_merge(array(&$stmt), $params))) {
            logError("Bind param failed: " . mysqli_error($connection));
            return false;
        }
    }
    
    // Execute statement
    if (!mysqli_stmt_execute($stmt)) {
        logError("Execution failed: " . mysqli_error($connection));
        return false;
    }
    
    return mysqli_stmt_get_result($stmt);
}

?>


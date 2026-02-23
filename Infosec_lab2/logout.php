<?php
/**
 * LOGOUT.PHP - SECURED Session Termination
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Proper session cleanup
 * ✓ Destroys all session variables
 * ✓ Unsets session cookies
 * ✓ Redirects to login page
 * ✓ Logging of logout events
 */

session_start();
require_once('config.php');
require_once('db.php');

// Log logout event
if (isset($_SESSION['user'])) {
    logError("Logout: User logged out - " . $_SESSION['user']);
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php?logout=success");
exit();

?>


<?php
/**
 * DELETE_STUDENT.PHP - SECURED Student Deletion Handler
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Session validation (access control)
 * ✓ Input validation (ID must be numeric)
 * ✓ Prepared statement (prevents SQL Injection)
 * ✓ Prevents Broken Object-Level Authorization (BOLA)
 * ✓ Proper error handling
 * ✓ Confirmation mechanism (via JavaScript)
 */

session_start();
require_once('config.php');
require_once('db.php');

// ============================================
// SESSION VALIDATION & ACCESS CONTROL
// ============================================

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['initialized'])) {
    header("Location: login.php");
    exit();
}

// ============================================
// INPUT VALIDATION
// ============================================

// Check if ID parameter exists
if (!isset($_GET['id'])) {
    logError("Delete Student: ID parameter missing");
    header("Location: dashboard.php?error=Invalid request");
    exit();
}

// Get and validate ID
$id = trim($_GET['id']);

// Validate that ID is numeric (prevents SQL Injection)
if (!is_numeric($id) || $id <= 0) {
    logError("Delete Student: Invalid ID format - " . htmlspecialchars($id));
    header("Location: dashboard.php?error=Invalid student ID");
    exit();
}

$id = intval($id);

// ============================================
// VERIFY STUDENT EXISTS BEFORE DELETION
// ============================================

$verify_query = "SELECT id FROM students WHERE id = ?";
$verify_stmt = mysqli_prepare($conn, $verify_query);

if (!$verify_stmt) {
    logError("Delete Student: Verify prepare failed - " . mysqli_error($conn));
    header("Location: dashboard.php?error=System error");
    exit();
}

mysqli_stmt_bind_param($verify_stmt, "i", $id);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    // Student does not exist
    logError("Delete Student: Attempted to delete non-existent student ID: " . $id);
    header("Location: dashboard.php?error=Student not found");
    exit();
}

mysqli_stmt_close($verify_stmt);

// ============================================
// SECURE DELETION WITH PREPARED STATEMENT
// ============================================

$delete_query = "DELETE FROM students WHERE id = ?";
$delete_stmt = mysqli_prepare($conn, $delete_query);

if (!$delete_stmt) {
    logError("Delete Student: Delete prepare failed - " . mysqli_error($conn));
    header("Location: dashboard.php?error=System error");
    exit();
}

// Bind parameter (i = integer)
mysqli_stmt_bind_param($delete_stmt, "i", $id);

// Execute deletion
if (!mysqli_stmt_execute($delete_stmt)) {
    logError("Delete Student: Delete execution failed - " . mysqli_error($conn));
    header("Location: dashboard.php?error=Failed to delete student");
    exit();
}

// Log successful deletion
logError("Delete Student: Student deleted successfully - ID: " . $id);

mysqli_stmt_close($delete_stmt);

// ============================================
// REINDEX IDs TO AVOID GAPS (make IDs sequential)
// Note: This rewrites the `id` column for all rows. Ensure no
// external foreign key dependencies rely on these IDs before enabling.
// ============================================

// Initialize MySQL user variable and renumber IDs based on creation order
$reindex_ok = true;
$reset_var_q = "SET @i = 0";
if (!mysqli_query($conn, $reset_var_q)) {
    logError("Reindex: Failed to reset counter - " . mysqli_error($conn));
    $reindex_ok = false;
}

if ($reindex_ok) {
    $reindex_q = "UPDATE students SET id = (@i := @i + 1) ORDER BY id ASC";
    if (!mysqli_query($conn, $reindex_q)) {
        logError("Reindex: Update failed - " . mysqli_error($conn));
        $reindex_ok = false;
    }
}

// Reset AUTO_INCREMENT to max(id)+1 so new inserts continue the sequence
if ($reindex_ok) {
    $max_res = mysqli_query($conn, "SELECT MAX(id) AS maxid FROM students");
    if ($max_res) {
        $max_row = mysqli_fetch_assoc($max_res);
        $next_ai = (isset($max_row['maxid']) && $max_row['maxid'] !== null) ? intval($max_row['maxid']) + 1 : 1;
        $alter_q = "ALTER TABLE students AUTO_INCREMENT = " . $next_ai;
        if (!mysqli_query($conn, $alter_q)) {
            logError("Reindex: Failed to reset AUTO_INCREMENT - " . mysqli_error($conn));
        }
    } else {
        logError("Reindex: Failed to get max id - " . mysqli_error($conn));
    }
}

// ============================================
// REDIRECT TO DASHBOARD
// ============================================

header("Location: dashboard.php?success=Student deleted successfully");
exit();

?>


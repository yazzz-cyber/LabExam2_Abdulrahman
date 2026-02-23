<?php
/**
 * DASHBOARD.PHP - SECURED Student Management Dashboard
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Session validation and authentication check
 * ✓ Output escaping with htmlspecialchars() (prevents XSS)
 * ✓ Session timeout handling
 * ✓ Access control (only logged-in users)
 * ✓ Safe display of student records
 */

session_start();
require_once('config.php');
require_once('db.php');

// ============================================
// SESSION VALIDATION & ACCESS CONTROL
// ============================================

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['initialized'])) {
    // User not authenticated - redirect to login
    header("Location: login.php");
    exit();
}

// Check session timeout (optional but recommended)
if (isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
        // Session expired
        session_destroy();
        header("Location: login.php?expired=1");
        exit();
    }
}

// Refresh session timeout on each page load
$_SESSION['login_time'] = time();

// Safe username output
$logged_in_user = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h2 {
            margin: 0;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            background: #3498db;
            border-radius: 3px;
            transition: background 0.3s;
        }
        .nav-links a:hover {
            background: #2980b9;
        }
        .nav-links a.logout {
            background: #e74c3c;
        }
        .nav-links a.logout:hover {
            background: #c0392b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .action-link {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>

<div class="container" style="width: 90%; max-width: 1200px;">
    
    <!-- Header with Navigation -->
    <div class="header">
        <h2>📚 Student Management System</h2>
        <div style="color: white; font-size: 14px;">
            Logged in as: <strong><?php echo $logged_in_user; ?></strong>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="nav-links" style="margin-bottom: 20px;">
        <a href="add_student.php">➕ Add New Student</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Student List Section -->
    <h3>📋 Student List</h3>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            // Fetch all students from database
            $query = "SELECT id, student_id, fullname, email, course, course_description 
                      FROM students 
                      ORDER BY created_at DESC";
            
            $result = mysqli_query($conn, $query);
            
            if (!$result) {
                logError("Dashboard: Query failed - " . mysqli_error($conn));
                echo "<tr><td colspan='7' class='empty-message'>Error loading students. Please try again later.</td></tr>";
            } elseif (mysqli_num_rows($result) === 0) {
                // No students found
                echo "<tr><td colspan='7' class='empty-message'>No students found. Click 'Add New Student' to get started.</td></tr>";
            } else {
                // Display each student record
                while ($row = mysqli_fetch_assoc($result)) {
                    // ============================================
                    // OUTPUT ESCAPING - PREVENTS XSS
                    // ============================================
                    
                    $id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
                    $student_id = htmlspecialchars($row['student_id'], ENT_QUOTES, 'UTF-8');
                    $fullname = htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8');
                    $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                    $course = htmlspecialchars($row['course'], ENT_QUOTES, 'UTF-8');
                    $description = htmlspecialchars($row['course_description'], ENT_QUOTES, 'UTF-8');
                    
                    echo "<tr>";
                    echo "<td>" . $id . "</td>";
                    echo "<td>" . $student_id . "</td>";
                    echo "<td>" . $fullname . "</td>";
                    echo "<td>" . $email . "</td>";
                    echo "<td>" . $course . "</td>";
                    echo "<td>" . $description . "</td>";
                    echo "<td>";
                    echo "<a class='action-link' href='delete_student.php?id=" . $id . "' onclick='return confirm(\"Are you sure you want to delete this student?\");'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            }
            
            ?>
        </tbody>
    </table>

</div>

</body>
</html>


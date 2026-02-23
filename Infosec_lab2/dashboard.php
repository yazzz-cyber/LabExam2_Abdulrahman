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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
            background: var(--light-bg);
        }

        .sidebar {
            width: 280px;
            background: var(--dark-bg);
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: var(--transition);
            left: 0;
            top: 0;
        }

        .sidebar-header {
            padding: 0 24px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: 700;
            color: white;
            margin: 0;
        }

        .sidebar-subtitle {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin: 5px 0 0;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0 12px;
        }

        .sidebar-nav li {
            margin-bottom: 8px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(99, 102, 241, 0.2);
            color: white;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--card-bg);
            padding: 20px 30px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .topbar-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .content-area {
            padding: 30px;
            flex: 1;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .page-header-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-header {
            padding: 20px 24px;
            background: var(--light-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
        }

        thead {
            background: var(--light-bg);
            border-bottom: 2px solid var(--border-color);
        }

        th {
            background: var(--light-bg);
            color: var(--text-primary);
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background: var(--light-bg);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .empty-state-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .empty-state-text {
            color: var(--text-secondary);
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                position: fixed;
                left: 0;
                z-index: 2000;
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1500;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 16px 20px;
            }

            .topbar-title {
                font-size: 20px;
            }

            .menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: var(--light-bg);
                border: none;
                border-radius: 8px;
                cursor: pointer;
                margin-right: auto;
                transition: var(--transition);
            }

            .menu-toggle:hover {
                background: var(--border-color);
            }

            .content-area {
                padding: 20px;
            }

            .page-header-title {
                font-size: 22px;
            }

            .user-menu {
                gap: 12px;
            }

            .user-info {
                display: none;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
                min-width: 0;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 12px !important;
            }
        }

        @media (max-width: 480px) {
            .page-header {
                margin-bottom: 20px;
            }

            .page-header-title {
                font-size: 18px;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 12px;
            }

            .topbar-title {
                font-size: 18px;
            }

            th, td {
                padding: 10px !important;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-layout">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">📚 EduMS</h2>
            <p class="sidebar-subtitle">Student Management</p>
        </div>
        
        <ul class="sidebar-nav">
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="add_student.php">➕ Add Student</a></li>
            <li><a href="backup.php">💾 Database Backup</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar">
            <button class="menu-toggle" id="menuToggle" style="display: none;">
                ☰
            </button>
            <h1 class="topbar-title">📋 Students</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">Hello, <?php echo htmlspecialchars(substr($logged_in_user, 0, 15)); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h2 class="page-header-title">Student List</h2>
                <div class="header-actions">
                    <a href="add_student.php" class="btn">
                        ➕ Add New Student
                    </a>
                    <a href="logout.php" class="btn btn-outline">
                        🚪 Logout
                    </a>
                </div>
            </div>

            <!-- Students Table -->
            <div class="table-container">
                <div class="table-header">
                    <span class="table-title">All Students</span>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Description</th>
                                <th style="text-align: center;">Actions</th>
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
                                echo "<tr><td colspan='7'><div class='empty-state'><div class='empty-state-icon'>⚠️</div><div class='empty-state-title'>Error</div><div class='empty-state-text'>Failed to load students. Please try again later.</div></div></td></tr>";
                            } elseif (mysqli_num_rows($result) === 0) {
                                // No students found
                                echo "<tr><td colspan='7'><div class='empty-state'><div class='empty-state-icon'>📚</div><div class='empty-state-title'>No Students Yet</div><div class='empty-state-text'>Start adding students to the system.</div><div class='action-buttons'><a href='add_student.php' class='btn btn-sm'>Add First Student</a></div></div></td></tr>";
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
                                    echo "<td><strong>#" . $id . "</strong></td>";
                                    echo "<td><strong>" . $student_id . "</strong></td>";
                                    echo "<td>" . $fullname . "</td>";
                                    echo "<td><a href='mailto:" . $email . "' style='color: var(--primary-color); text-decoration: none;'>" . $email . "</a></td>";
                                    echo "<td>" . $course . "</td>";
                                    echo "<td>" . (strlen($description) > 30 ? substr($description, 0, 30) . '...' : $description) . "</td>";
                                    echo "<td style='text-align: center;'>";
                                    echo "<a href='delete_student.php?id=" . $id . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this student?\");'>🗑️ Delete</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            }
                            
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Mobile menu toggle functionality
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function updateMenuVisibility() {
        if (window.innerWidth <= 768) {
            menuToggle.style.display = 'flex';
        } else {
            menuToggle.style.display = 'none';
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    }

    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
    });

    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });

    // Close sidebar when a link is clicked
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    });

    // Update menu visibility on page load and resize
    updateMenuVisibility();
    window.addEventListener('resize', updateMenuVisibility);
</script>

</body>
</html>


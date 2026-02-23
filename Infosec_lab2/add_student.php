<?php
/**
 * ADD_STUDENT.PHP - SECURED Student Addition Handler
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Session validation (access control)
 * ✓ Input validation (all fields validated)
 * ✓ Input sanitization (trim, length checks)
 * ✓ Email format validation
 * ✓ Prepared statements (prevents SQL Injection)
 * ✓ Output escaping (prevents XSS)
 * ✓ UNIQUE constraint on student_id handled
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

$error_message = "";
$success_message = "";

// Initialize form fields to prevent undefined variable warnings
$student_id = "";
$fullname = "";
$email = "";
$course = "";
$course_description = "";

// ============================================
// FORM SUBMISSION HANDLER
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    
    // Get and trim inputs
    $student_id = trim($_POST['student_id'] ?? "");
    $fullname = trim($_POST['fullname'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $course = trim($_POST['course'] ?? "");
    $course_description = trim($_POST['course_description'] ?? "");
    
    // ============================================
    // INPUT VALIDATION
    // ============================================
    
    $validation_errors = [];
    
    // Validate student_id
    if (empty($student_id)) {
        $validation_errors[] = "Student ID is required.";
    } elseif (strlen($student_id) > MAX_STUDENT_ID_LENGTH) {
        $validation_errors[] = "Student ID exceeds maximum length.";
    } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $student_id)) {
        $validation_errors[] = "Student ID can only contain letters, numbers, hyphens, and underscores.";
    }
    
    // Validate fullname
    if (empty($fullname)) {
        $validation_errors[] = "Full Name is required.";
    } elseif (strlen($fullname) > MAX_FULLNAME_LENGTH) {
        $validation_errors[] = "Full Name exceeds maximum length.";
    } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/', $fullname)) {
        $validation_errors[] = "Full Name can only contain letters, spaces, hyphens, and apostrophes.";
    }
    
    // Validate email
    if (empty($email)) {
        $validation_errors[] = "Email is required.";
    } elseif (strlen($email) > MAX_EMAIL_LENGTH) {
        $validation_errors[] = "Email exceeds maximum length.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = "Invalid email format.";
    }
    
    // Validate course
    if (empty($course)) {
        $validation_errors[] = "Course is required.";
    } elseif (strlen($course) > MAX_COURSE_LENGTH) {
        $validation_errors[] = "Course name exceeds maximum length.";
    }
    
    // Validate course_description
    if (strlen($course_description) > MAX_DESCRIPTION_LENGTH) {
        $validation_errors[] = "Course description exceeds maximum length.";
    }
    
    // ============================================
    // DATABASE INSERTION (IF VALIDATION PASSED)
    // ============================================
    
    if (empty($validation_errors)) {
        
        // Prepare SQL query with placeholders
        $query = "INSERT INTO students 
                  (student_id, fullname, email, course, course_description) 
                  VALUES (?, ?, ?, ?, ?)";
        
        // Create prepared statement
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            logError("Add Student: Prepare failed - " . mysqli_error($conn));
            $error_message = "System error. Please try again later.";
        } else {
            // Bind parameters (s=string for all fields)
            if (!mysqli_stmt_bind_param($stmt, "sssss", 
                $student_id, $fullname, $email, $course, $course_description)) {
                logError("Add Student: Bind failed - " . mysqli_error($conn));
                $error_message = "System error. Please try again later.";
            } else {
                // Execute query
                if (!mysqli_stmt_execute($stmt)) {
                    // Check for specific error
                    $db_error = mysqli_error($conn);
                    if (strpos($db_error, 'Duplicate entry') !== false) {
                        // Student ID already exists
                        $error_message = "This Student ID already exists in the system.";
                    } else {
                        logError("Add Student: Execution failed - " . $db_error);
                        $error_message = "Failed to add student. Please try again.";
                    }
                } else {
                    // Success!
                    logError("Add Student: New student added - Student ID: " . $student_id);
                    
                    // Clear form and show success message
                    $success_message = "Student added successfully!";
                    $student_id = "";
                    $fullname = "";
                    $email = "";
                    $course = "";
                    $course_description = "";
                    
                    // Optionally redirect after 2 seconds
                    echo "<meta http-equiv='refresh' content='2;url=dashboard.php'>";
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        // Validation failed
        $error_message = implode("<br>", $validation_errors);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: var(--light-bg);
        }

        .add-student-wrapper {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow-xl);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .form-header h1 {
            font-size: 1.75rem;
            margin-bottom: 10px;
            color: white;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            margin: 0;
        }

        .form-body {
            padding: 40px 30px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .required {
            color: var(--danger-color);
        }

        .form-group input,
        .form-group textarea {
            margin: 0;
        }

        .form-footer {
            text-align: center;
            color: var(--text-secondary);
            font-size: 12px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .button-group .btn {
            flex: 1;
        }

        .alert {
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .form-card {
                box-shadow: var(--shadow-md);
            }

            .form-header {
                padding: 30px 20px;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }

            .form-body {
                padding: 30px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="add-student-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h1>➕ Add New Student</h1>
            <p>Register a new student in the system</p>
        </div>

        <div class="form-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">✓ <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                
                <div class="form-section">
                    <h4 style="color: var(--text-primary); margin-bottom: 18px;">Personal Information</h4>
                    
                    <div class="form-group">
                        <label for="student_id">Student ID <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="student_id" 
                            name="student_id" 
                            placeholder="e.g., STU001 or STU-2024-001"
                            value="<?php echo htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="<?php echo MAX_STUDENT_ID_LENGTH; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="fullname">Full Name <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            placeholder="e.g., John Doe"
                            value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="<?php echo MAX_FULLNAME_LENGTH; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="e.g., john@university.edu"
                            value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="<?php echo MAX_EMAIL_LENGTH; ?>"
                            required>
                    </div>
                </div>

                <div class="form-section">
                    <h4 style="color: var(--text-primary); margin-bottom: 18px;">Course Information</h4>
                    
                    <div class="form-group">
                        <label for="course">Course <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="course" 
                            name="course" 
                            placeholder="e.g., Computer Science"
                            value="<?php echo htmlspecialchars($course, ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="<?php echo MAX_COURSE_LENGTH; ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="course_description">Course Description</label>
                        <textarea 
                            id="course_description" 
                            name="course_description" 
                            placeholder="Add optional course details..."
                            maxlength="<?php echo MAX_DESCRIPTION_LENGTH; ?>"></textarea>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" name="add" class="btn btn-success">
                        ✓ Create Student
                    </button>
                    <a href="dashboard.php" class="btn btn-outline">
                        ← Cancel
                    </a>
                </div>
            </form>

            <div class="form-footer">
                <strong>Fields marked with <span class="required">*</span> are required.</strong>
            </div>
        </div>
    </div>
</div>

</body>
</html>


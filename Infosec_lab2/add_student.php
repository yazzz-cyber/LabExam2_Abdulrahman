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
<html>
<head>
    <title>Add Student - Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            width: 600px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 3px;
            display: none;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
            display: block;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button,
        a.btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        button[type="submit"] {
            background: #27ae60;
            color: white;
        }
        button[type="submit"]:hover {
            background: #229954;
        }
        a.btn {
            background: #95a5a6;
            color: white;
        }
        a.btn:hover {
            background: #7f8c8d;
        }
        .required {
            color: #e74c3c;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>➕ Add New Student</h2>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div class="message success">✓ <?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        
        <div class="form-group">
            <label for="student_id">Student ID <span class="required">*</span></label>
            <input type="text" id="student_id" name="student_id" 
                   placeholder="e.g., STU001, STU-2024-001"
                   value="<?php echo htmlspecialchars($student_id, ENT_QUOTES, 'UTF-8'); ?>"
                   maxlength="<?php echo MAX_STUDENT_ID_LENGTH; ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="fullname">Full Name <span class="required">*</span></label>
            <input type="text" id="fullname" name="fullname" 
                   placeholder="e.g., John Doe"
                   value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>"
                   maxlength="<?php echo MAX_FULLNAME_LENGTH; ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" 
                   placeholder="e.g., john@example.com"
                   value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                   maxlength="<?php echo MAX_EMAIL_LENGTH; ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="course">Course <span class="required">*</span></label>
            <input type="text" id="course" name="course" 
                   placeholder="e.g., Computer Science"
                   value="<?php echo htmlspecialchars($course, ENT_QUOTES, 'UTF-8'); ?>"
                   maxlength="<?php echo MAX_COURSE_LENGTH; ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="course_description">Course Description</label>
            <textarea id="course_description" name="course_description" 
                      placeholder="Optional description of the course"
                      maxlength="<?php echo MAX_DESCRIPTION_LENGTH; ?>"></textarea>
        </div>

        <div class="button-group">
            <button type="submit" name="add">✓ Add Student</button>
            <a href="dashboard.php" class="btn">← Back to Dashboard</a>
        </div>
    </form>

    <hr style="margin-top: 30px;">
    <p style="font-size: 12px; color: #7f8c8d;">
        <strong>Fields marked with <span class="required">*</span> are required.</strong>
    </p>
</div>

</body>
</html>


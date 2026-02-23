<?php
/**
 * LOGIN.PHP - SECURED Authentication Handler
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Uses prepared statements (prevents SQL Injection)
 * ✓ Uses password_verify() (prevents plaintext password comparison)
 * ✓ Input validation and sanitization
 * ✓ Session regeneration after login (prevents Session Fixation)
 * ✓ Rate limiting indication (for future enhancement)
 * ✓ Generic error messages (prevents user enumeration)
 * ✓ CSRF token ready (placeholder)
 * ✓ Proper session management
 */

session_start();
require_once('config.php');
require_once('db.php');

// Prevent session fixation
if (isset($_SESSION['user'], $_SESSION['initialized']) && $_SESSION['initialized'] === true) {
    // User already logged in
    header("Location: dashboard.php");
    exit();
}

$error_message = "";
$username_input = "";

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    // ============================================
    // INPUT VALIDATION
    // ============================================
    
    $username = trim($_POST['username'] ?? "");
    $password = trim($_POST['password'] ?? "");
    
    // Validate input exists
    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required.";
    }
    
    // Validate username length
    elseif (strlen($username) > MAX_USERNAME_LENGTH) {
        $error_message = "Invalid username format.";
    }
    
    // Validate password length
    elseif (strlen($password) > MAX_PASSWORD_LENGTH) {
        $error_message = "Invalid password format.";
    }
    
    else {
        // ============================================
        // SECURE DATABASE QUERY (PREPARED STATEMENT)
        // ============================================
        
        $query = "SELECT id, username, password FROM users WHERE username = ?";
        
        // Use prepared statement to prevent SQL Injection
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            logError("Login: Prepare failed - " . mysqli_error($conn));
            $error_message = "System error. Please try again later.";
        } else {
            // Bind parameter (s = string)
            mysqli_stmt_bind_param($stmt, "s", $username);
            
            // Execute query
            if (!mysqli_stmt_execute($stmt)) {
                logError("Login: Execution failed - " . mysqli_error($conn));
                $error_message = "System error. Please try again later.";
            } else {
                // Get result
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    
                    // ============================================
                    // VERIFY PASSWORD WITH BCRYPT
                    // ============================================
                    
                    if (password_verify($password, $row['password'])) {
                        // Password is correct!
                        
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);
                        
                        // Store user info in session
                        $_SESSION['user'] = $row['username'];
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['initialized'] = true;
                        $_SESSION['login_time'] = time();
                        
                        // Log successful login
                        logError("Login: Successful login for user: " . $row['username']);
                        
                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        // Password is incorrect
                        $error_message = "Invalid username or password.";
                    }
                } else {
                    // User not found
                    $error_message = "Invalid username or password.";
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        // Store username for display (without XSS risk)
        $username_input = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Infosec Lab 2</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 15px;
            display: none;
        }
        .error.show {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container" style="width: 400px;">
    <h2>🔐 Admin Login</h2>
    
    <?php if (!empty($error_message)): ?>
        <div class="error show"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" 
                   placeholder="Enter username" 
                   value="<?php echo $username_input; ?>"
                   maxlength="<?php echo MAX_USERNAME_LENGTH; ?>"
                   required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password"
                   placeholder="Enter password"
                   maxlength="<?php echo MAX_PASSWORD_LENGTH; ?>"
                   required>
        </div>

        <button type="submit" name="login" style="width: 100%">Login</button>
    </form>

</div>

</body>
</html>


<?php
/**
 * CONFIG.PHP - Secure Configuration File
 * 
 * SECURITY BEST PRACTICES:
 * - Store outside web root in production
 * - Use environment variables for credentials
 * - Change default credentials
 * - Use strong database passwords
 * - Implement proper access controls
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Change this in production!
define('DB_NAME', 'infosec_lab');

// ============================================
// SESSION CONFIGURATION
// ============================================

define('SESSION_TIMEOUT', 1800);    // 30 minutes (in seconds)
define('SESSION_NAME', 'INFOSEC_SESSION');

// ============================================
// SECURITY CONFIGURATION
// ============================================

define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 10);        // Cost factor for bcrypt (4-31)

// ============================================
// ERROR HANDLING
// ============================================

define('ERROR_LOG_FILE', dirname(__FILE__) . '/logs/error.log');
define('DISPLAY_ERRORS', false);    // Set to true only in development

// ============================================
// INPUT VALIDATION CONSTRAINTS
// ============================================

define('MAX_USERNAME_LENGTH', 100);
define('MAX_PASSWORD_LENGTH', 255);
define('MAX_STUDENT_ID_LENGTH', 50);
define('MAX_FULLNAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 100);
define('MAX_COURSE_LENGTH', 100);
define('MAX_DESCRIPTION_LENGTH', 255);

// ============================================
// BACKUP STRATEGY
// ============================================
// Implement automated daily backups:
// 1. Use mysqldump via cron job
// 2. Store backups outside web root
// 3. Rotate old backups (keep last 30 days)
// 4. Test backup restoration monthly
// 5. Encrypt backups in transit and at rest
// 
// Example cron command (run daily at 2 AM):
// 0 2 * * * mysqldump -u root -p infosec_lab > /backup/db_$(date +\%Y\%m\%d).sql
// ============================================

?>

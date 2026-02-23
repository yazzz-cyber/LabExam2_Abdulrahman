<?php
/**
 * BACKUP.PHP - Database Backup Handler
 * 
 * SECURITY IMPROVEMENTS:
 * ✓ Session validation and authentication check
 * ✓ Restricts backup to logged-in users only
 * ✓ Generates timestamped backup files
 * ✓ Uses mysqli functions for safe SQL export
 * ✓ Proper error handling and logging
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

// Check session timeout
if (isset($_SESSION['login_time'])) {
    if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
        session_destroy();
        header("Location: login.php?expired=1");
        exit();
    }
}

// Refresh session timeout
$_SESSION['login_time'] = time();

// ============================================
// BACKUP PROCESS
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup'])) {
    try {
        // Get database name from config
        $database = DB_NAME;
        $timestamp = date('Y-m-d_H-i-s');
        
        // Create backups directory if it doesn't exist
        $backup_dir = 'backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate backup filename
        $backup_file = $backup_dir . '/' . $database . '_backup_' . $timestamp . '.sql';
        
        // Construct mysqldump command
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_NAME),
            escapeshellarg($backup_file)
        );
        
        // Execute backup command
        $output = '';
        $return_var = 0;
        exec($command, $output, $return_var);
        
        // Check if backup was successful
        if ($return_var === 0 && file_exists($backup_file)) {
            logError("Backup: Successfully created backup file: " . $backup_file);
            $success_message = "✅ Database backup created successfully: " . basename($backup_file);
        } else {
            logError("Backup: Failed to create backup - Return code: " . $return_var);
            $error_message = "❌ Failed to create backup. Please check server configuration.";
        }
    } catch (Exception $e) {
        logError("Backup: Exception - " . $e->getMessage());
        $error_message = "❌ Backup error: " . htmlspecialchars($e->getMessage());
    }
    
    // Redirect back to dashboard with message
    if (isset($success_message)) {
        header("Location: dashboard.php?backup=success&msg=" . urlencode($success_message));
    } else {
        header("Location: dashboard.php?backup=error&msg=" . urlencode($error_message ?? "Unknown error"));
    }
    exit();
}

// ============================================
// DOWNLOAD BACKUP FILE
// ============================================

if (isset($_GET['download']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    $backup_dir = 'backups';
    $filepath = $backup_dir . '/' . basename($file);
    
    // Validate file exists and is in backups directory
    if (file_exists($filepath) && strpos(realpath($filepath), realpath($backup_dir)) === 0) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filepath));
        
        logError("Backup: Downloaded backup file: " . $file);
        readfile($filepath);
        exit();
    } else {
        logError("Backup: Attempted to download invalid file: " . $file);
        header("Location: dashboard.php?backup=error&msg=Invalid file");
        exit();
    }
}

// ============================================
// DELETE BACKUP FILE
// ============================================

if (isset($_GET['delete']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    $backup_dir = 'backups';
    $filepath = $backup_dir . '/' . basename($file);
    
    // Validate file exists and is in backups directory
    if (file_exists($filepath) && strpos(realpath($filepath), realpath($backup_dir)) === 0) {
        if (unlink($filepath)) {
            logError("Backup: Deleted backup file: " . $file);
            header("Location: dashboard.php?backup=deleted&msg=Backup deleted successfully");
        } else {
            header("Location: dashboard.php?backup=error&msg=Failed to delete backup");
        }
    } else {
        logError("Backup: Attempted to delete invalid file: " . $file);
        header("Location: dashboard.php?backup=error&msg=Invalid file");
    }
    exit();
}

// ============================================
// LIST BACKUP FILES
// ============================================

$backup_dir = 'backups';
$backup_files = array();

if (is_dir($backup_dir)) {
    $files = array_diff(scandir($backup_dir), array('.', '..'));
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filepath = $backup_dir . '/' . $file;
            $backup_files[] = array(
                'name' => $file,
                'size' => formatBytes(filesize($filepath)),
                'date' => date('Y-m-d H:i:s', filemtime($filepath))
            );
        }
    }
}

// Sort by date descending
usort($backup_files, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - Student Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .backup-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
        }

        .backup-header {
            margin-bottom: 30px;
        }

        .backup-header h1 {
            color: var(--text-primary);
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .backup-header p {
            color: var(--text-secondary);
            margin: 0;
        }

        .backup-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .backup-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .backup-actions form {
            margin: 0;
        }

        .btn-backup {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-backup:hover {
            background: #15a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-back {
            background: var(--info-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #0284c7;
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .backups-list {
            margin-top: 25px;
        }

        .backups-list h3 {
            color: var(--text-primary);
            margin: 0 0 15px 0;
            font-size: 18px;
        }

        .backups-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
        }

        .backups-table thead {
            background: var(--light-bg);
            border-bottom: 2px solid var(--border-color);
        }

        .backups-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
        }

        .backups-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .backups-table tbody tr:hover {
            background: var(--light-bg);
        }

        .backup-actions-cell {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .btn-download {
            background: var(--info-color);
            color: white;
        }

        .btn-download:hover {
            background: #0284c7;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            opacity: 0.9;
        }

        .empty-backups {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }

        .empty-backups-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .backup-container {
                padding: 20px;
            }

            .backup-actions {
                flex-direction: column;
            }

            .backup-actions form,
            .btn-back {
                width: 100%;
            }

            .btn-backup,
            .btn-back {
                justify-content: center;
            }

            .backups-table {
                font-size: 13px;
            }

            .backups-table th,
            .backups-table td {
                padding: 10px;
            }

            .backup-actions-cell {
                flex-direction: column;
                gap: 5px;
            }

            .btn-sm {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="backup-container">
        <div class="backup-header">
            <h1>💾 Database Backup</h1>
            <p>Create and manage database backups safely</p>
        </div>

        <?php if (isset($_GET['backup']) && $_GET['backup'] === 'success'): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_GET['msg'] ?? 'Backup completed'); ?>
            </div>
        <?php elseif (isset($_GET['backup']) && $_GET['backup'] === 'error'): ?>
            <div class="message error">
                <?php echo htmlspecialchars($_GET['msg'] ?? 'Backup failed'); ?>
            </div>
        <?php elseif (isset($_GET['backup']) && $_GET['backup'] === 'deleted'): ?>
            <div class="message success">
                <?php echo htmlspecialchars($_GET['msg'] ?? 'Backup deleted'); ?>
            </div>
        <?php endif; ?>

        <div class="backup-card">
            <div class="backup-actions">
                <form method="POST">
                    <button type="submit" name="backup" value="1" class="btn-backup">
                        ➕ Create New Backup
                    </button>
                </form>
                <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
            </div>
        </div>

        <div class="backup-card">
            <div class="backups-list">
                <h3>📂 Backup Files (<?php echo count($backup_files); ?>)</h3>
                
                <?php if (count($backup_files) > 0): ?>
                    <table class="backups-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Date & Time</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backup_files as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                    <td><?php echo $backup['size']; ?></td>
                                    <td><?php echo $backup['date']; ?></td>
                                    <td>
                                        <div class="backup-actions-cell">
                                            <a href="backup.php?download=1&file=<?php echo urlencode($backup['name']); ?>" class="btn-sm btn-download">
                                                ⬇️ Download
                                            </a>
                                            <a href="backup.php?delete=1&file=<?php echo urlencode($backup['name']); ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this backup?');">
                                                🗑️ Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-backups">
                        <div class="empty-backups-icon">📭</div>
                        <p>No backup files yet. Click "Create New Backup" to start.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

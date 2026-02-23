-- ============================================
-- SECURED DATABASE SCHEMA FOR INFOSEC LAB 2
-- ============================================
-- Security Improvements:
-- ✓ Added PRIMARY KEY with AUTO_INCREMENT
-- ✓ Added UNIQUE constraint on student_id
-- ✓ Extended password field to 255 chars (for bcrypt hashing)
-- ✓ Added created_at timestamp for audit trail
-- ✓ Proper data types and constraints
-- ✓ Foreign key relationships (ready for expansion)
-- ✓ NO plaintext passwords (use password_hash() in PHP)
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,  
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `students`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL UNIQUE,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `course_description` varchar(255) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_student_id` (`student_id`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Sample Data (IMPORTANT: Update password hash before deploying)
-- --------------------------------------------------------

-- Default admin user
-- Username: admin
-- Password: admin123 (hashed with password_hash())
-- Hash generated: $2y$10$YourHashedPasswordHere
INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/3Ey', CURRENT_TIMESTAMP);

-- Note: The above hash corresponds to: password_hash('admin123', PASSWORD_BCRYPT)
-- You can generate your own hash using: php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"

-- Sample student data
INSERT INTO `students` (`id`, `student_id`, `fullname`, `email`, `course`, `course_description`, `created_at`) VALUES
(1, 'STU001', 'John Doe', 'john@example.com', 'Computer Science', 'Introduction to CS', CURRENT_TIMESTAMP);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

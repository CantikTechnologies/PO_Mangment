-- User Roles and Profile System Setup
-- Run this SQL to add role-based access control to your PO Management system

-- 1. Add role column to existing users table
ALTER TABLE `users_login_signup` 
ADD COLUMN `role` ENUM('admin', 'employee') NOT NULL DEFAULT 'employee' AFTER `password`,
ADD COLUMN `first_name` VARCHAR(100) NULL AFTER `role`,
ADD COLUMN `last_name` VARCHAR(100) NULL AFTER `first_name`,
ADD COLUMN `phone` VARCHAR(20) NULL AFTER `last_name`,
ADD COLUMN `department` VARCHAR(100) NULL AFTER `phone`,
ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `department`,
ADD COLUMN `last_login` TIMESTAMP NULL AFTER `is_active`,
ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `last_login`;

-- 2. Create user_profiles table for extended profile information
CREATE TABLE `user_profiles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `profile_picture` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `country` VARCHAR(100) NULL,
  `postal_code` VARCHAR(20) NULL,
  `emergency_contact_name` VARCHAR(200) NULL,
  `emergency_contact_phone` VARCHAR(20) NULL,
  `hire_date` DATE NULL,
  `employee_id` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_employee_id` (`employee_id`),
  CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_login_signup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create role_permissions table for granular permissions
CREATE TABLE `role_permissions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `role` ENUM('admin', 'employee') NOT NULL,
  `permission` VARCHAR(100) NOT NULL,
  `allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role`, `permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Insert default permissions
INSERT INTO `role_permissions` (`role`, `permission`, `allowed`) VALUES
-- Admin permissions (full access)
('admin', 'view_dashboard', 1),
('admin', 'view_po_details', 1),
('admin', 'add_po_details', 1),
('admin', 'edit_po_details', 1),
('admin', 'delete_po_details', 1),
('admin', 'view_invoices', 1),
('admin', 'add_invoices', 1),
('admin', 'edit_invoices', 1),
('admin', 'delete_invoices', 1),
('admin', 'view_outsourcing', 1),
('admin', 'add_outsourcing', 1),
('admin', 'edit_outsourcing', 1),
('admin', 'delete_outsourcing', 1),
('admin', 'view_reports', 1),
('admin', 'manage_users', 1),
('admin', 'view_finance_tasks', 1),
('admin', 'add_finance_tasks', 1),
('admin', 'edit_finance_tasks', 1),
('admin', 'delete_finance_tasks', 1),

-- Employee permissions (limited access)
('employee', 'view_dashboard', 1),
('employee', 'view_po_details', 1),
('employee', 'add_po_details', 1),
('employee', 'edit_po_details', 0),
('employee', 'delete_po_details', 0),
('employee', 'view_invoices', 1),
('employee', 'add_invoices', 1),
('employee', 'edit_invoices', 0),
('employee', 'delete_invoices', 0),
('employee', 'view_outsourcing', 1),
('employee', 'add_outsourcing', 1),
('employee', 'edit_outsourcing', 0),
('employee', 'delete_outsourcing', 0),
('employee', 'view_reports', 1),
('employee', 'manage_users', 0),
('employee', 'view_finance_tasks', 1),
('employee', 'add_finance_tasks', 1),
('employee', 'edit_finance_tasks', 0),
('employee', 'delete_finance_tasks', 0);

-- 5. Update existing admin user
UPDATE `users_login_signup` 
SET `role` = 'admin', 
    `first_name` = 'Admin', 
    `last_name` = 'User',
    `department` = 'IT',
    `is_active` = 1
WHERE `username` = 'admin';

-- 6. Create audit_log table for tracking user actions
CREATE TABLE `audit_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100) NULL,
  `record_id` INT(11) NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_login_signup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Create user_sessions table for better session management
CREATE TABLE `user_sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `session_id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `last_activity` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_login_signup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

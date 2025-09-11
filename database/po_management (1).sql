-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 02:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `po_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 04:13:59'),
(2, 1, 'login', NULL, NULL, NULL, NULL, '192.168.2.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', '2025-09-11 05:34:30'),
(3, 1, 'create_po', 'po_details', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:14:55'),
(4, 1, 'create_invoice', 'billing_details', 16, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:22:20'),
(5, 1, 'create_invoice', 'billing_details', 17, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:23:14'),
(6, 1, 'create_outsourcing', 'outsourcing_detail', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 07:34:15'),
(7, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:55:43'),
(8, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:55:51'),
(9, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 08:56:48'),
(10, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:00:26'),
(11, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 09:00:33'),
(12, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 11:11:37'),
(13, 1, 'delete_finance_task', 'finance_tasks', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `billing_details`
--

CREATE TABLE `billing_details` (
  `id` int(11) NOT NULL,
  `project_details` varchar(500) NOT NULL,
  `cost_center` varchar(100) NOT NULL,
  `customer_po` varchar(50) NOT NULL,
  `remaining_balance_in_po` decimal(15,2) DEFAULT 0.00,
  `cantik_invoice_no` varchar(100) NOT NULL,
  `cantik_invoice_date` int(11) NOT NULL,
  `cantik_inv_value_taxable` decimal(15,2) NOT NULL,
  `against_vendor_inv_number` varchar(100) DEFAULT NULL,
  `payment_receipt_date` int(11) DEFAULT NULL,
  `payment_advise_no` varchar(100) DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tds` decimal(18,2) GENERATED ALWAYS AS (round(`cantik_inv_value_taxable` * 0.02,2)) STORED,
  `receivable` decimal(18,2) GENERATED ALWAYS AS (round(`cantik_inv_value_taxable` * 1.18 - `cantik_inv_value_taxable` * 0.02,2)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_details`
--

INSERT INTO `billing_details` (`id`, `project_details`, `cost_center`, `customer_po`, `remaining_balance_in_po`, `cantik_invoice_no`, `cantik_invoice_date`, `cantik_inv_value_taxable`, `against_vendor_inv_number`, `payment_receipt_date`, `payment_advise_no`, `vendor_name`, `created_at`, `updated_at`) VALUES
(11, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/24-25/1312', 2025, 35225.00, 'MAH/464/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(12, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/24-25/1521', 2025, 68250.00, 'MAH/558/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(13, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/25-26/128', 2025, 68250.00, 'MAH/650/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(14, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 0.00, 'CTPL/25-26/306', 2025, 68250.00, 'M/2526/Jun/024', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(15, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 0.00, 'CTPL/25-26/307', 2025, 68250.00, 'M/2526/Jun/025', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44');

-- --------------------------------------------------------

--
-- Stand-in structure for view `billing_summary`
-- (See below for the actual view)
--
CREATE TABLE `billing_summary` (
`customer_po` varchar(50)
,`cost_center` varchar(100)
,`cantik_inv_value_taxable` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `finance_tasks`
--

CREATE TABLE `finance_tasks` (
  `id` int(11) NOT NULL,
  `action_req_by` varchar(100) NOT NULL,
  `request_date` date NOT NULL,
  `cost_center` varchar(100) NOT NULL,
  `action_req` text NOT NULL,
  `action_owner` varchar(100) NOT NULL,
  `status` enum('Incomplete','Pending','Complete') NOT NULL,
  `completion_date` date DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `finance_tasks`
--

INSERT INTO `finance_tasks` (`id`, `action_req_by`, `request_date`, `cost_center`, `action_req`, `action_owner`, `status`, `completion_date`, `remark`, `created_at`, `updated_at`) VALUES
(1, 'Naveen', '2025-06-25', 'Raptokos - PT', 'Vratatech - Raptokos PT One month payment to be released immediately', 'Sanjay', 'Pending', NULL, NULL, '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
(2, 'Naveen', '2025-06-25', 'Raptokos - PT', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL, '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
(3, 'Naveen', '2025-06-25', 'BMW-OA', 'Renewal to be followed with Priya', 'Sneha', 'Pending', NULL, NULL, '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
(4, 'Maneesh', '2025-06-25', 'Finder Fees - PT', 'Xpheno GST payment to be released', 'Sanjay', 'Pending', NULL, NULL, '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
(5, 'Maneesh', '2025-06-25', 'Finder Fees - PT', 'PO # 4500092198 - Check billing status', 'Sanjay', 'Pending', '2025-06-26', '', '2025-08-22 10:55:02', '2025-09-11 05:50:22'),
(7, 'Maneesh', '2025-06-26', 'HCIL PT', '25-26/10 - WinoVision Invoice - Get the CN Against the invoice', 'Sneha', 'Pending', NULL, NULL, '2025-08-22 10:55:02', '2025-08-22 10:55:02');

-- --------------------------------------------------------

--
-- Table structure for table `outsourcing_detail`
--

CREATE TABLE `outsourcing_detail` (
  `id` int(11) NOT NULL,
  `project_details` varchar(500) NOT NULL,
  `cost_center` varchar(100) NOT NULL,
  `customer_po` varchar(50) NOT NULL,
  `vendor_name` varchar(200) NOT NULL,
  `cantik_po_no` varchar(100) NOT NULL,
  `cantik_po_date` int(11) NOT NULL,
  `cantik_po_value` decimal(15,2) NOT NULL,
  `remaining_bal_in_po` decimal(15,2) DEFAULT 0.00,
  `vendor_invoice_frequency` varchar(50) NOT NULL,
  `vendor_inv_number` varchar(100) NOT NULL,
  `vendor_inv_date` int(11) NOT NULL,
  `vendor_inv_value` decimal(15,2) NOT NULL,
  `tds_ded` decimal(18,2) GENERATED ALWAYS AS (round(`vendor_inv_value` * 0.02,2)) STORED,
  `net_payble` decimal(18,2) GENERATED ALWAYS AS (round(`vendor_inv_value` * 1.18 - `vendor_inv_value` * 0.02,2)) STORED,
  `payment_status_from_ntt` varchar(100) DEFAULT NULL,
  `payment_value` decimal(15,2) DEFAULT 0.00,
  `payment_date` int(11) DEFAULT NULL,
  `pending_payment` decimal(18,2) GENERATED ALWAYS AS (`net_payble` - ifnull(`payment_value`,0)) STORED,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outsourcing_detail`
--

INSERT INTO `outsourcing_detail` (`id`, `project_details`, `cost_center`, `customer_po`, `vendor_name`, `cantik_po_no`, `cantik_po_date`, `cantik_po_value`, `remaining_bal_in_po`, `vendor_invoice_frequency`, `vendor_inv_number`, `vendor_inv_date`, `vendor_inv_value`, `payment_status_from_ntt`, `payment_value`, `payment_date`, `remarks`, `created_at`, `updated_at`) VALUES
(3, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/24-25/396', 45684, 167143.00, 0.00, 'Monthly', 'MAH/650/24-25', 45747, 65000.00, 'Paid', 75400.00, 2025, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05'),
(4, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/25-26/104', 45753, 130000.00, 0.00, 'Monthly', 'M/2526/Jun/024', 45821, 65000.00, 'Paid', 75400.00, 2025, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05'),
(5, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/25-26/104', 45753, 130000.00, 0.00, 'Monthly', 'M/2526/Jun/025', 45821, 65000.00, 'Paid', 75400.00, 2025, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05');

-- --------------------------------------------------------

--
-- Stand-in structure for view `outsourcing_summary`
-- (See below for the actual view)
--
CREATE TABLE `outsourcing_summary` (
`customer_po` varchar(50)
,`cost_center` varchar(100)
,`cantik_po_no` varchar(100)
,`cantik_po_value` decimal(37,2)
,`vendor_inv_value` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `posummary`
-- (See below for the actual view)
--
CREATE TABLE `posummary` (
`po_id` int(11)
,`project_description` varchar(500)
,`cost_center` varchar(100)
,`po_number` varchar(50)
,`po_value` decimal(15,2)
,`vendor_name` varchar(200)
,`target_gm` decimal(5,4)
);

-- --------------------------------------------------------

--
-- Table structure for table `po_details`
--

CREATE TABLE `po_details` (
  `id` int(11) NOT NULL,
  `project_description` varchar(500) NOT NULL,
  `cost_center` varchar(100) NOT NULL,
  `sow_number` varchar(100) NOT NULL,
  `start_date` int(11) NOT NULL,
  `end_date` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `po_date` int(11) NOT NULL,
  `po_value` decimal(15,2) NOT NULL,
  `billing_frequency` varchar(50) NOT NULL,
  `target_gm` decimal(5,4) NOT NULL,
  `pending_amount` decimal(15,2) DEFAULT 0.00,
  `po_status` varchar(50) DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_details`
--

INSERT INTO `po_details` (`id`, `project_description`, `cost_center`, `sow_number`, `start_date`, `end_date`, `po_number`, `po_date`, `po_value`, `billing_frequency`, `target_gm`, `pending_amount`, `po_status`, `remarks`, `vendor_name`, `created_at`, `updated_at`) VALUES
(1, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', 'FC2024-497', 45673, 45747, '4500095281', 45684, 175500.00, 'Monthly', 0.0500, 0.00, 'Active', NULL, NULL, '2025-09-02 18:09:32', '2025-09-02 18:09:32'),
(2, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', 'FC2025-073', 45748, 45808, '4500098831', 45753, 136500.00, 'Monthly', 0.0500, 0.00, 'Active', NULL, NULL, '2025-09-02 18:09:32', '2025-09-02 18:09:32');

-- --------------------------------------------------------

--
-- Stand-in structure for view `po_summary`
-- (See below for the actual view)
--
CREATE TABLE `po_summary` (
`id` int(11)
,`project_description` varchar(500)
,`cost_center` varchar(100)
,`sow_number` varchar(100)
,`start_date` int(11)
,`end_date` int(11)
,`po_number` varchar(50)
,`po_date` int(11)
,`po_value` decimal(15,2)
,`billing_frequency` varchar(50)
,`target_gm` decimal(5,4)
,`pending_amount` decimal(15,2)
,`po_status` varchar(50)
,`remarks` text
,`vendor_name` varchar(200)
,`target_gm_value` decimal(17,2)
,`start_date_formatted` varchar(10)
,`end_date_formatted` varchar(10)
,`po_date_formatted` varchar(10)
);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `permission` varchar(100) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission`, `allowed`, `created_at`) VALUES
(1, 'admin', 'view_dashboard', 1, '2025-09-11 04:12:02'),
(2, 'admin', 'view_po_details', 1, '2025-09-11 04:12:02'),
(3, 'admin', 'add_po_details', 1, '2025-09-11 04:12:02'),
(4, 'admin', 'edit_po_details', 1, '2025-09-11 04:12:02'),
(5, 'admin', 'delete_po_details', 1, '2025-09-11 04:12:02'),
(6, 'admin', 'view_invoices', 1, '2025-09-11 04:12:02'),
(7, 'admin', 'add_invoices', 1, '2025-09-11 04:12:02'),
(8, 'admin', 'edit_invoices', 1, '2025-09-11 04:12:02'),
(9, 'admin', 'delete_invoices', 1, '2025-09-11 04:12:02'),
(10, 'admin', 'view_outsourcing', 1, '2025-09-11 04:12:02'),
(11, 'admin', 'add_outsourcing', 1, '2025-09-11 04:12:02'),
(12, 'admin', 'edit_outsourcing', 1, '2025-09-11 04:12:02'),
(13, 'admin', 'delete_outsourcing', 1, '2025-09-11 04:12:02'),
(14, 'admin', 'view_reports', 1, '2025-09-11 04:12:02'),
(15, 'admin', 'manage_users', 1, '2025-09-11 04:12:02'),
(16, 'admin', 'view_finance_tasks', 1, '2025-09-11 04:12:02'),
(17, 'admin', 'manage_finance_tasks', 1, '2025-09-11 04:12:02'),
(18, 'employee', 'view_dashboard', 1, '2025-09-11 04:12:02'),
(19, 'employee', 'view_po_details', 1, '2025-09-11 04:12:02'),
(20, 'employee', 'add_po_details', 1, '2025-09-11 04:12:02'),
(21, 'employee', 'edit_po_details', 0, '2025-09-11 04:12:02'),
(22, 'employee', 'delete_po_details', 0, '2025-09-11 04:12:02'),
(23, 'employee', 'view_invoices', 1, '2025-09-11 04:12:02'),
(24, 'employee', 'add_invoices', 1, '2025-09-11 04:12:02'),
(25, 'employee', 'edit_invoices', 0, '2025-09-11 04:12:02'),
(26, 'employee', 'delete_invoices', 0, '2025-09-11 04:12:02'),
(27, 'employee', 'view_outsourcing', 1, '2025-09-11 04:12:02'),
(28, 'employee', 'add_outsourcing', 1, '2025-09-11 04:12:02'),
(29, 'employee', 'edit_outsourcing', 0, '2025-09-11 04:12:02'),
(30, 'employee', 'delete_outsourcing', 0, '2025-09-11 04:12:02'),
(31, 'employee', 'view_reports', 1, '2025-09-11 04:12:02'),
(32, 'employee', 'manage_users', 0, '2025-09-11 04:12:02'),
(33, 'employee', 'view_finance_tasks', 1, '2025-09-11 04:12:02'),
(34, 'employee', 'manage_finance_tasks', 0, '2025-09-11 04:12:02'),
(35, 'admin', 'add_finance_tasks', 1, '2025-09-11 05:49:51'),
(36, 'admin', 'edit_finance_tasks', 1, '2025-09-11 05:49:51'),
(37, 'admin', 'delete_finance_tasks', 1, '2025-09-11 05:49:51'),
(38, 'employee', 'add_finance_tasks', 1, '2025-09-11 05:49:51'),
(39, 'employee', 'edit_finance_tasks', 0, '2025-09-11 05:49:51'),
(40, 'employee', 'delete_finance_tasks', 0, '2025-09-11 05:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `users_login_signup`
--

CREATE TABLE `users_login_signup` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL DEFAULT 'employee',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_login_signup`
--

INSERT INTO `users_login_signup` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `department`, `is_active`, `last_login`, `updated_at`, `created_at`) VALUES
(1, 'admin', 'admin@123.com', '$2y$10$HaEMAktwEzYpBjMrwF/tg.XNu3o6dHHSd9LuRN7TalUDen.6Gi2mK', 'admin', 'Admin', 'User', NULL, 'IT', 1, '2025-09-11 11:11:37', '2025-09-11 11:11:37', '2025-08-19 11:40:55');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(200) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `billing_summary`
--
DROP TABLE IF EXISTS `billing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `billing_summary`  AS SELECT `bd`.`customer_po` AS `customer_po`, `bd`.`cost_center` AS `cost_center`, sum(`bd`.`cantik_inv_value_taxable`) AS `cantik_inv_value_taxable` FROM `billing_details` AS `bd` GROUP BY `bd`.`customer_po`, `bd`.`cost_center` ;

-- --------------------------------------------------------

--
-- Structure for view `outsourcing_summary`
--
DROP TABLE IF EXISTS `outsourcing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `outsourcing_summary`  AS SELECT `od`.`customer_po` AS `customer_po`, `od`.`cost_center` AS `cost_center`, max(`od`.`cantik_po_no`) AS `cantik_po_no`, sum(`od`.`cantik_po_value`) AS `cantik_po_value`, sum(`od`.`vendor_inv_value`) AS `vendor_inv_value` FROM `outsourcing_detail` AS `od` GROUP BY `od`.`customer_po`, `od`.`cost_center` ;

-- --------------------------------------------------------

--
-- Structure for view `posummary`
--
DROP TABLE IF EXISTS `posummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `posummary`  AS SELECT `po`.`id` AS `po_id`, `po`.`project_description` AS `project_description`, `po`.`cost_center` AS `cost_center`, `po`.`po_number` AS `po_number`, `po`.`po_value` AS `po_value`, `po`.`vendor_name` AS `vendor_name`, `po`.`target_gm` AS `target_gm` FROM `po_details` AS `po` ;

-- --------------------------------------------------------

--
-- Structure for view `po_summary`
--
DROP TABLE IF EXISTS `po_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `po_summary`  AS SELECT `pd`.`id` AS `id`, `pd`.`project_description` AS `project_description`, `pd`.`cost_center` AS `cost_center`, `pd`.`sow_number` AS `sow_number`, `pd`.`start_date` AS `start_date`, `pd`.`end_date` AS `end_date`, `pd`.`po_number` AS `po_number`, `pd`.`po_date` AS `po_date`, `pd`.`po_value` AS `po_value`, `pd`.`billing_frequency` AS `billing_frequency`, `pd`.`target_gm` AS `target_gm`, `pd`.`pending_amount` AS `pending_amount`, `pd`.`po_status` AS `po_status`, `pd`.`remarks` AS `remarks`, `pd`.`vendor_name` AS `vendor_name`, round(`pd`.`po_value` * `pd`.`target_gm`,2) AS `target_gm_value`, date_format('1899-12-30' + interval `pd`.`start_date` day,'%d-%m-%Y') AS `start_date_formatted`, date_format('1899-12-30' + interval `pd`.`end_date` day,'%d-%m-%Y') AS `end_date_formatted`, date_format('1899-12-30' + interval `pd`.`po_date` day,'%d-%m-%Y') AS `po_date_formatted` FROM `po_details` AS `pd` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `billing_details`
--
ALTER TABLE `billing_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_po` (`customer_po`),
  ADD KEY `idx_project` (`project_details`),
  ADD KEY `idx_cost_center` (`cost_center`),
  ADD KEY `idx_cantik_invoice_no` (`cantik_invoice_no`),
  ADD KEY `idx_vendor_name` (`vendor_name`),
  ADD KEY `idx_billing_customer_po` (`customer_po`),
  ADD KEY `idx_billing_project_cost_po` (`project_details`,`cost_center`,`customer_po`);

--
-- Indexes for table `finance_tasks`
--
ALTER TABLE `finance_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_date` (`request_date`),
  ADD KEY `idx_cost_center` (`cost_center`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `outsourcing_detail`
--
ALTER TABLE `outsourcing_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_po` (`customer_po`),
  ADD KEY `idx_vendor_name` (`vendor_name`),
  ADD KEY `idx_cantik_po_no` (`cantik_po_no`),
  ADD KEY `idx_vendor_inv_number` (`vendor_inv_number`),
  ADD KEY `idx_outsourcing_customer_po` (`customer_po`),
  ADD KEY `idx_outsourcing_project_cost_po` (`project_details`,`cost_center`,`customer_po`);

--
-- Indexes for table `po_details`
--
ALTER TABLE `po_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_project` (`project_description`),
  ADD KEY `idx_cost_center` (`cost_center`),
  ADD KEY `idx_status` (`po_status`),
  ADD KEY `idx_po_po_number` (`po_number`),
  ADD KEY `idx_po_project_cost` (`project_description`,`cost_center`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission` (`role`,`permission`);

--
-- Indexes for table `users_login_signup`
--
ALTER TABLE `users_login_signup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `billing_details`
--
ALTER TABLE `billing_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `finance_tasks`
--
ALTER TABLE `finance_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `outsourcing_detail`
--
ALTER TABLE `outsourcing_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `po_details`
--
ALTER TABLE `po_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users_login_signup`
--
ALTER TABLE `users_login_signup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_login_signup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `billing_details`
--
ALTER TABLE `billing_details`
  ADD CONSTRAINT `billing_details_ibfk_1` FOREIGN KEY (`customer_po`) REFERENCES `po_details` (`po_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `outsourcing_detail`
--
ALTER TABLE `outsourcing_detail`
  ADD CONSTRAINT `outsourcing_detail_ibfk_1` FOREIGN KEY (`customer_po`) REFERENCES `po_details` (`po_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_login_signup` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

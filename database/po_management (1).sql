-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 11:39 AM
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
(13, 1, 'delete_finance_task', 'finance_tasks', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 12:04:12'),
(14, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:03:19'),
(15, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:03:23'),
(16, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:03:26'),
(17, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:24:58'),
(18, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:25:07'),
(19, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 13:25:20'),
(20, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:24:35'),
(21, 1, 'update_invoice', 'billing_details', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:26:50'),
(22, 1, 'update_invoice', 'billing_details', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:26:54'),
(23, 1, 'update_outsourcing', 'outsourcing_detail', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:31:56'),
(24, 1, 'create_po', 'po_details', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-11 15:33:08'),
(25, 1, 'create_po', 'po_details', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 04:01:28'),
(26, 1, 'create_po', 'po_details', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 04:49:05'),
(27, 1, 'update_po', 'po_details', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 04:49:19'),
(28, 1, 'update_invoice', 'billing_details', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 04:52:21'),
(29, 1, 'update_invoice', 'billing_details', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:01:13'),
(30, 1, 'update_invoice', 'billing_details', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:01:31'),
(31, 1, 'update_outsourcing', 'outsourcing_detail', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:03:18'),
(32, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:32:22'),
(33, 1, 'update_po', 'po_details', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:32:28'),
(34, 1, 'create_user', 'users_login_signup', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 05:51:43'),
(35, 1, 'delete_user', 'users_login_signup', 2, NULL, '{\"deleted_user\":\"pardeep@123.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:03:27'),
(36, 1, 'create_user', 'users_login_signup', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:08:04'),
(37, 1, 'delete_user', 'users_login_signup', 3, NULL, '{\"deleted_user\":\"Pardeep\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:15:55'),
(38, 1, 'create_user', 'users_login_signup', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:16:16'),
(39, 1, 'delete_user', 'users_login_signup', 4, NULL, '{\"deleted_user\":\"pardeepkumar23112000@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:20:20'),
(40, 1, 'create_user', 'users_login_signup', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:20:43'),
(41, 1, 'update_user', 'users_login_signup', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:20:50'),
(42, 1, 'update_user', 'users_login_signup', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:21:31'),
(43, 1, 'update_user', 'users_login_signup', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:22:33'),
(44, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:22:55'),
(45, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:23:11'),
(46, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:26:11'),
(47, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:29:36'),
(48, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:29:42'),
(49, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:35:36'),
(50, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:35:43'),
(51, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:41:15'),
(52, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:41:26'),
(53, 5, 'create_finance_task', 'finance_tasks', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:45:49'),
(54, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:46:47'),
(55, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:53:18'),
(56, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:53:24'),
(57, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:53:56'),
(58, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:54:15'),
(59, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:55:19'),
(60, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 06:55:24'),
(61, 5, 'create_outsourcing', 'outsourcing_detail', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 08:40:51'),
(62, 5, 'create_outsourcing', 'outsourcing_detail', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 08:43:01'),
(63, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 09:53:48'),
(64, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 09:53:56'),
(65, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 09:54:05'),
(66, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 09:54:14'),
(67, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:07:47'),
(68, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:07:54'),
(69, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:46:33'),
(70, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:46:44'),
(71, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:46:49'),
(72, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:46:54'),
(73, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:49:33'),
(74, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-12 10:49:41'),
(75, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:29:00'),
(76, 1, 'delete_finance_task', 'finance_tasks', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:19'),
(77, 1, 'delete_finance_task', 'finance_tasks', 7, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:25'),
(78, 1, 'delete_finance_task', 'finance_tasks', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:32'),
(79, 1, 'delete_finance_task', 'finance_tasks', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:39'),
(80, 1, 'delete_finance_task', 'finance_tasks', 2, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:45'),
(81, 1, 'delete_finance_task', 'finance_tasks', 3, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:52'),
(82, 1, 'delete_finance_task', 'finance_tasks', 4, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 04:36:57'),
(83, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(84, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(85, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(86, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(87, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(88, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(89, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(90, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(91, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(92, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(93, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(94, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(95, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(96, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(97, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(98, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(99, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(100, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(101, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(102, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(103, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(104, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(105, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(106, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(107, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(108, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(109, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(110, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(111, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 05:43:43'),
(112, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(113, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(114, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(115, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(116, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(117, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 07:27:14'),
(118, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 09:41:08'),
(119, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(120, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(121, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(122, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(123, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(124, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-15 10:37:21'),
(125, 1, 'create_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 11:18:33'),
(126, 1, 'create_invoice', 'billing_details', 18, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 11:19:38'),
(127, 1, 'update_invoice', 'billing_details', 18, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 11:19:58'),
(128, 1, 'create_outsourcing', 'outsourcing_detail', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 11:22:26'),
(129, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 11:46:56'),
(130, 1, 'create_invoice', 'billing_details', 19, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-15 12:06:11'),
(131, 1, 'create_po', 'po_details', 50, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:39:55'),
(132, 1, 'update_po', 'po_details', 50, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:46:54'),
(133, 1, 'update_po', 'po_details', 50, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:47:11'),
(134, 1, 'update_po', 'po_details', 50, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 04:52:11'),
(135, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(136, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(137, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(138, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(139, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(140, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-16 05:51:59'),
(141, 1, 'create_po', 'po_details', 57, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:54:08'),
(142, 1, 'create_invoice', 'billing_details', 20, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:54:41'),
(143, 1, 'update_invoice', 'billing_details', 20, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:55:41'),
(144, 1, 'create_outsourcing', 'outsourcing_detail', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:56:44'),
(145, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:59:25'),
(146, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:59:38'),
(147, 1, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 05:59:54'),
(148, 5, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 06:00:01'),
(149, 5, 'logout', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 06:00:25'),
(150, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 06:00:31'),
(151, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:02:20'),
(152, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:06:05'),
(153, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:06:39'),
(154, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:07:01'),
(155, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:10:06'),
(156, 1, 'create_po', 'po_details', 58, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:10:33'),
(157, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 07:15:50'),
(158, 1, 'update_po', 'po_details', 49, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 08:25:43'),
(159, 1, 'login', NULL, NULL, NULL, NULL, '192.168.2.134', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-16 08:48:02'),
(160, 1, 'create_po', 'po_details', 59, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:15:55'),
(161, 1, 'create_po', 'po_details', 60, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:19:51'),
(162, 1, 'create_invoice', 'billing_details', 21, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:21:05'),
(163, 1, 'create_po', 'po_details', 61, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:45:13'),
(164, 1, 'update_po', 'po_details', 61, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:45:31'),
(165, 1, 'create_po', 'po_details', 62, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:46:32'),
(166, 1, 'update_po', 'po_details', 62, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:47:16'),
(167, 1, 'update_po', 'po_details', 62, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:47:39'),
(168, 1, 'create_po', 'po_details', 63, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 09:49:14'),
(169, 1, 'create_outsourcing', 'outsourcing_detail', 11, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:26:14'),
(170, 1, 'create_outsourcing', 'outsourcing_detail', 13, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:35:28'),
(171, 1, 'create_outsourcing', 'outsourcing_detail', 14, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 10:39:29'),
(172, 1, 'create_po', 'po_details', 64, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:04:29'),
(173, 1, 'create_invoice', 'billing_details', 22, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:05:20'),
(174, 1, 'create_outsourcing', 'outsourcing_detail', 15, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 11:06:15'),
(175, 1, 'create_invoice', 'billing_details', 23, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-16 12:20:54'),
(176, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 09:55:16'),
(177, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 09:55:16'),
(178, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 09:55:16'),
(179, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 09:55:16'),
(180, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(181, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(182, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(183, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(184, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(185, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(186, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(187, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(188, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(189, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(190, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(191, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(192, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(193, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(194, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(195, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(196, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(197, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:06:38'),
(198, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(199, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(200, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(201, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(202, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(203, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(204, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(205, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(206, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(207, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(208, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(209, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(210, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(211, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(212, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(213, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(214, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(215, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(216, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(217, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(218, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(219, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(220, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(221, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:10:03'),
(222, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(223, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(224, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(225, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(226, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(227, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(228, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(229, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(230, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(231, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(232, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(233, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(234, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(235, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(236, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(237, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(238, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(239, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(240, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(241, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(242, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(243, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(244, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(245, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:18:56'),
(246, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(247, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(248, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(249, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(250, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(251, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(252, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(253, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(254, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(255, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(256, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(257, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(258, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(259, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(260, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(261, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(262, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(263, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(264, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(265, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(266, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(267, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(268, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(269, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(270, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(271, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(272, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(273, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(274, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(275, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-17 10:45:51'),
(276, 1, 'update_po', 'po_details', 183, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 09:32:54'),
(277, 1, 'update_po', 'po_details', 183, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 09:35:06'),
(278, 1, 'update_po', 'po_details', 183, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 09:39:42'),
(279, 1, 'create_po', 'po_details', 216, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:21:26'),
(280, 1, 'create_invoice', 'billing_details', 1574, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:22:37'),
(281, 1, 'create_outsourcing', 'outsourcing_detail', 391, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:23:41'),
(282, 1, 'create_invoice', 'billing_details', 1575, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:30:20'),
(283, 1, 'create_invoice', 'billing_details', 1576, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:31:20'),
(284, 1, 'create_outsourcing', 'outsourcing_detail', 392, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:33:22'),
(285, 1, 'create_outsourcing', 'outsourcing_detail', 393, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 10:33:39'),
(286, 1, 'update_outsourcing', 'outsourcing_detail', 393, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 11:22:53'),
(287, 1, 'update_outsourcing', 'outsourcing_detail', 392, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 11:23:05'),
(288, 1, 'update_outsourcing', 'outsourcing_detail', 392, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 11:23:17'),
(289, 1, 'update_outsourcing', 'outsourcing_detail', 392, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 11:23:50'),
(290, 1, 'update_outsourcing', 'outsourcing_detail', 391, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-18 11:25:51'),
(291, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 05:54:52'),
(292, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 06:19:38'),
(293, 1, 'create_invoice', 'billing_details', 1577, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 07:40:29'),
(294, 1, 'create_outsourcing', 'outsourcing_detail', 395, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-19 08:02:33'),
(295, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(296, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(297, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(298, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(299, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(300, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(301, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(302, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(303, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(304, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(305, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(306, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(307, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(308, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(309, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(310, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(311, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(312, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(313, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(314, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(315, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(316, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(317, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(318, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(319, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(320, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(321, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(322, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(323, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:17:46'),
(324, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(325, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(326, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(327, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(328, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(329, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(330, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(331, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(332, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(333, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(334, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(335, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(336, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(337, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(338, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(339, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(340, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(341, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(342, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(343, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(344, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(345, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(346, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(347, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(348, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(349, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(350, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(351, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(352, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(353, 1, 'create_po', 'po_details', NULL, NULL, NULL, NULL, NULL, '2025-09-19 09:36:50'),
(354, 1, 'create_po', 'po_details', 278, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 04:17:34'),
(355, 1, 'create_invoice', 'billing_details', 1578, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 04:17:46'),
(356, 1, 'create_outsourcing', 'outsourcing_detail', 397, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 04:18:04'),
(357, 1, 'update_outsourcing', 'outsourcing_detail', 397, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 04:18:31'),
(358, 1, 'update_invoice', 'billing_details', 1578, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-20 04:18:53'),
(359, 1, 'login', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 04:12:10'),
(360, 1, 'create_outsourcing', 'outsourcing_detail', 399, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 04:22:10'),
(361, 1, 'update_outsourcing', 'outsourcing_detail', 397, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 04:36:15'),
(362, 1, 'create_po', 'po_details', 279, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 06:10:22'),
(363, 1, 'create_invoice', 'billing_details', 1579, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 06:55:42'),
(364, 1, 'create_invoice', 'billing_details', 1580, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 06:57:57');

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
  `cantik_invoice_date` int(11) DEFAULT NULL,
  `cantik_inv_value_taxable` decimal(15,2) NOT NULL,
  `against_vendor_inv_number` varchar(100) DEFAULT NULL,
  `payment_receipt_date` int(11) DEFAULT NULL,
  `payment_advise_no` varchar(100) DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tds` decimal(18,2) GENERATED ALWAYS AS (round(`cantik_inv_value_taxable` * 0.02,2)) STORED,
  `receivable` decimal(18,2) GENERATED ALWAYS AS (round(`cantik_inv_value_taxable` * 1.18 - `cantik_inv_value_taxable` * 0.02,2)) STORED,
  `payment_received_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cantik_po_date` int(11) DEFAULT NULL,
  `cantik_po_value` decimal(15,2) NOT NULL,
  `remaining_bal_in_po` decimal(15,2) DEFAULT 0.00,
  `vendor_invoice_frequency` varchar(50) NOT NULL,
  `vendor_inv_number` varchar(100) NOT NULL,
  `vendor_inv_date` int(11) DEFAULT NULL,
  `vendor_inv_value` decimal(15,2) NOT NULL,
  `tds_ded` decimal(18,2) GENERATED ALWAYS AS (round(`vendor_inv_value` * 0.02,2)) STORED,
  `net_payble` decimal(18,2) GENERATED ALWAYS AS (round(`vendor_inv_value` * 1.18 - `vendor_inv_value` * 0.02,2)) STORED,
  `payment_status_from_ntt` varchar(100) DEFAULT NULL,
  `payment_value` decimal(15,2) DEFAULT 0.00,
  `payment_date` int(11) DEFAULT NULL,
  `pending_payment` decimal(18,2) GENERATED ALWAYS AS (`net_payble` - ifnull(`payment_value`,0)) STORED,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_advise_no` varchar(100) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `outsourcing_invoicing_latest`
-- (See below for the actual view)
--
CREATE TABLE `outsourcing_invoicing_latest` (
`customer_po` varchar(50)
,`latest_cantik_po_no` varchar(100)
,`vendor_invoicing_till_date` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `outsourcing_latest_po`
-- (See below for the actual view)
--
CREATE TABLE `outsourcing_latest_po` (
`customer_po` varchar(50)
,`latest_cantik_po_no` varchar(100)
,`latest_cantik_po_value` decimal(15,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `outsourcing_summary`
-- (See below for the actual view)
--
CREATE TABLE `outsourcing_summary` (
`customer_po` varchar(50)
,`cantik_po_no` varchar(100)
,`cantik_po_value` decimal(15,2)
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
  `start_date` int(11) DEFAULT NULL,
  `end_date` int(11) DEFAULT NULL,
  `po_number` varchar(50) NOT NULL,
  `po_date` int(11) DEFAULT NULL,
  `po_value` decimal(15,2) NOT NULL,
  `billing_frequency` varchar(50) NOT NULL,
  `target_gm` decimal(5,4) NOT NULL,
  `pending_amount` decimal(15,2) DEFAULT 0.00,
  `po_status` varchar(50) DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Stand-in structure for view `so_form_summary`
-- (See below for the actual view)
--
CREATE TABLE `so_form_summary` (
`project` varchar(500)
,`cost_center` varchar(100)
,`customer_po_no` varchar(50)
,`customer_po_value` decimal(15,2)
,`billed_till_date` decimal(37,2)
,`remaining_balance_po` decimal(38,2)
,`vendor_name` varchar(200)
,`cantik_po_no` varchar(100)
,`vendor_po_value` decimal(15,2)
,`vendor_invoicing_till_date` decimal(37,2)
,`remaining_balance_in_po` decimal(38,2)
,`margin_till_date` decimal(44,2)
,`target_gm` decimal(7,2)
,`variance_in_gm` decimal(45,2)
);

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
(1, 'admin', 'admin@123.com', '$2y$10$HaEMAktwEzYpBjMrwF/tg.XNu3o6dHHSd9LuRN7TalUDen.6Gi2mK', 'admin', 'Admin', 'User', NULL, 'IT', 1, '2025-09-22 04:12:10', '2025-09-22 04:12:10', '2025-08-19 11:40:55'),
(5, 'pardeepkumar23112000@gmail.com', 'pardeepkumar23112000@gmail.com', '$2y$10$QoWrjtLIN64ZhtEmJ/SqpesrqgaGl4y1iynjg6okg2Yg6Nv7WdZj6', 'employee', 'PARDEEP', 'KUMAR', '09971078958', 'IT', 1, '2025-09-16 06:00:01', '2025-09-16 06:00:01', '2025-09-12 06:20:43');

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

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `profile_picture`, `bio`, `address`, `city`, `state`, `country`, `postal_code`, `emergency_contact_name`, `emergency_contact_phone`, `hire_date`, `employee_id`, `created_at`, `updated_at`) VALUES
(4, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '12345', '2025-09-12 06:20:43', '2025-09-12 06:20:43');

-- --------------------------------------------------------

--
-- Structure for view `billing_summary`
--
DROP TABLE IF EXISTS `billing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `billing_summary`  AS SELECT `bd`.`customer_po` AS `customer_po`, `bd`.`cost_center` AS `cost_center`, sum(`bd`.`cantik_inv_value_taxable`) AS `cantik_inv_value_taxable` FROM `billing_details` AS `bd` GROUP BY `bd`.`customer_po`, `bd`.`cost_center` ;

-- --------------------------------------------------------

--
-- Structure for view `outsourcing_invoicing_latest`
--
DROP TABLE IF EXISTS `outsourcing_invoicing_latest`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `outsourcing_invoicing_latest`  AS SELECT `lp`.`customer_po` AS `customer_po`, `lp`.`latest_cantik_po_no` AS `latest_cantik_po_no`, coalesce(sum(`od`.`vendor_inv_value`),0) AS `vendor_invoicing_till_date` FROM (`outsourcing_latest_po` `lp` left join `outsourcing_detail` `od` on(`od`.`customer_po` = `lp`.`customer_po` and `od`.`cantik_po_no` = `lp`.`latest_cantik_po_no`)) GROUP BY `lp`.`customer_po`, `lp`.`latest_cantik_po_no` ;

-- --------------------------------------------------------

--
-- Structure for view `outsourcing_latest_po`
--
DROP TABLE IF EXISTS `outsourcing_latest_po`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `outsourcing_latest_po`  AS SELECT `od`.`customer_po` AS `customer_po`, max(`od`.`cantik_po_no`) AS `latest_cantik_po_no`, max(`od`.`cantik_po_value`) AS `latest_cantik_po_value` FROM `outsourcing_detail` AS `od` GROUP BY `od`.`customer_po` ;

-- --------------------------------------------------------

--
-- Structure for view `outsourcing_summary`
--
DROP TABLE IF EXISTS `outsourcing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `outsourcing_summary`  AS SELECT `lp`.`customer_po` AS `customer_po`, `lp`.`latest_cantik_po_no` AS `cantik_po_no`, `lp`.`latest_cantik_po_value` AS `cantik_po_value`, `il`.`vendor_invoicing_till_date` AS `vendor_inv_value` FROM (`outsourcing_latest_po` `lp` left join `outsourcing_invoicing_latest` `il` on(`il`.`customer_po` = `lp`.`customer_po` and `il`.`latest_cantik_po_no` = `lp`.`latest_cantik_po_no`)) ;

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

-- --------------------------------------------------------

--
-- Structure for view `so_form_summary`
--
DROP TABLE IF EXISTS `so_form_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `so_form_summary`  AS SELECT `ps`.`project_description` AS `project`, `ps`.`cost_center` AS `cost_center`, `ps`.`po_number` AS `customer_po_no`, `ps`.`po_value` AS `customer_po_value`, coalesce(`bsum`.`cantik_inv_value_taxable`,0) AS `billed_till_date`, greatest(`ps`.`po_value` - coalesce(`bsum`.`cantik_inv_value_taxable`,0),0) AS `remaining_balance_po`, `ps`.`vendor_name` AS `vendor_name`, `os`.`cantik_po_no` AS `cantik_po_no`, coalesce(`os`.`cantik_po_value`,0) AS `vendor_po_value`, coalesce(`os`.`vendor_inv_value`,0) AS `vendor_invoicing_till_date`, greatest(coalesce(`os`.`cantik_po_value`,0) - coalesce(`os`.`vendor_inv_value`,0),0) AS `remaining_balance_in_po`, coalesce(round((coalesce(`bsum`.`cantik_inv_value_taxable`,0) - coalesce(`os`.`vendor_inv_value`,0)) / nullif(coalesce(`bsum`.`cantik_inv_value_taxable`,0),0) * 100,2),0) AS `margin_till_date`, round(`ps`.`target_gm` * 100,2) AS `target_gm`, coalesce(round((coalesce(`bsum`.`cantik_inv_value_taxable`,0) - coalesce(`os`.`vendor_inv_value`,0)) / nullif(coalesce(`bsum`.`cantik_inv_value_taxable`,0),0) * 100 - `ps`.`target_gm` * 100,2),0) AS `variance_in_gm` FROM ((`posummary` `ps` left join `billing_summary` `bsum` on(`bsum`.`customer_po` = `ps`.`po_number`)) left join `outsourcing_summary` `os` on(`os`.`customer_po` = `ps`.`po_number`)) ORDER BY `ps`.`cost_center` ASC, `ps`.`po_number` ASC ;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=365;

--
-- AUTO_INCREMENT for table `billing_details`
--
ALTER TABLE `billing_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1581;

--
-- AUTO_INCREMENT for table `finance_tasks`
--
ALTER TABLE `finance_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `outsourcing_detail`
--
ALTER TABLE `outsourcing_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=400;

--
-- AUTO_INCREMENT for table `po_details`
--
ALTER TABLE `po_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users_login_signup`
--
ALTER TABLE `users_login_signup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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

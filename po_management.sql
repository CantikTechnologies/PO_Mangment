-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 08:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `tds` decimal(15,2) DEFAULT 0.00,
  `receivable` decimal(15,2) DEFAULT 0.00,
  `against_vendor_inv_number` varchar(100) DEFAULT NULL,
  `payment_receipt_date` int(11) DEFAULT NULL,
  `payment_advise_no` varchar(100) DEFAULT NULL,
  `vendor_name` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_details`
--

INSERT INTO `billing_details` (`id`, `project_details`, `cost_center`, `customer_po`, `remaining_balance_in_po`, `cantik_invoice_no`, `cantik_invoice_date`, `cantik_inv_value_taxable`, `tds`, `receivable`, `against_vendor_inv_number`, `payment_receipt_date`, `payment_advise_no`, `vendor_name`, `created_at`, `updated_at`) VALUES
(11, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/24-25/1312', 2025, 35225.00, 704.50, 40861.00, 'MAH/464/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(12, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/24-25/1521', 2025, 68250.00, 1365.00, 79170.00, 'MAH/558/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(13, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 0.00, 'CTPL/25-26/128', 2025, 68250.00, 1365.00, 79170.00, 'MAH/650/24-25', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(14, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 0.00, 'CTPL/25-26/306', 2025, 68250.00, 1365.00, 79170.00, 'M/2526/Jun/024', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44'),
(15, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 0.00, 'CTPL/25-26/307', 2025, 68250.00, 1365.00, 79170.00, 'M/2526/Jun/025', 2025, NULL, 'VRATA TECH SOLUTIONS PRIVATE LIMITED', '2025-09-03 05:27:44', '2025-09-03 05:27:44');

-- --------------------------------------------------------

--
-- Stand-in structure for view `billing_summary`
-- (See below for the actual view)
--
CREATE TABLE `billing_summary` (
`id` int(11)
,`project_details` varchar(500)
,`cost_center` varchar(100)
,`customer_po` varchar(50)
,`remaining_balance_in_po` decimal(15,2)
,`cantik_invoice_no` varchar(100)
,`cantik_invoice_date` int(11)
,`cantik_inv_value_taxable` decimal(15,2)
,`tds` decimal(15,2)
,`receivable` decimal(15,2)
,`against_vendor_inv_number` varchar(100)
,`payment_receipt_date` int(11)
,`payment_advise_no` varchar(100)
,`vendor_name` varchar(200)
,`tds_calculated` decimal(17,2)
,`receivable_calculated` decimal(18,2)
,`cantik_invoice_date_formatted` varchar(10)
,`payment_receipt_date_formatted` varchar(10)
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
(5, 'Maneesh', '2025-06-25', 'Finder Fees - PT', 'PO # 4500092198 - Check billing status', 'Sanjay', '', '2025-06-26', '', '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
(6, 'Maneesh', '2025-06-26', 'Finder Fees - PT', 'PO # 4500092198 - Check if payment has been made to vendor, else release PO', 'Akshay', '', '2025-06-27', 'Checked Invoice is pending from Vendor Auropro, Request Sanjay to issue PO once approved, hence the vendor can submit their invoice.', '2025-08-22 10:55:02', '2025-08-22 10:55:02'),
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
  `tds_ded` decimal(15,2) DEFAULT 0.00,
  `net_payble` decimal(15,2) DEFAULT 0.00,
  `payment_status_from_ntt` varchar(100) DEFAULT NULL,
  `payment_value` decimal(15,2) DEFAULT 0.00,
  `payment_date` int(11) DEFAULT NULL,
  `pending_payment` decimal(15,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outsourcing_detail`
--

INSERT INTO `outsourcing_detail` (`id`, `project_details`, `cost_center`, `customer_po`, `vendor_name`, `cantik_po_no`, `cantik_po_date`, `cantik_po_value`, `remaining_bal_in_po`, `vendor_invoice_frequency`, `vendor_inv_number`, `vendor_inv_date`, `vendor_inv_value`, `tds_ded`, `net_payble`, `payment_status_from_ntt`, `payment_value`, `payment_date`, `pending_payment`, `remarks`, `created_at`, `updated_at`) VALUES
(2, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/24-25/396', 45684, 167143.00, 0.00, 'Monthly', 'MAH/558/24-25', 45716, 65000.00, 1300.00, 75400.00, 'Paid', 75400.00, 2025, 0.00, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05'),
(3, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500095281', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/24-25/396', 45684, 167143.00, 0.00, 'Monthly', 'MAH/650/24-25', 45747, 65000.00, 1300.00, 75400.00, 'Paid', 75400.00, 2025, 0.00, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05'),
(4, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/25-26/104', 45753, 130000.00, 0.00, 'Monthly', 'M/2526/Jun/024', 45821, 65000.00, 1300.00, 75400.00, 'Paid', 75400.00, 2025, 0.00, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05'),
(5, 'Raptakos Resource Deployment - Anuj Kushwaha', 'Raptakos PT', '4500098831', 'VRATA TECH SOLUTIONS PRIVATE LIMITED', 'CTPL/PO/25-26/104', 45753, 130000.00, 0.00, 'Monthly', 'M/2526/Jun/025', 45821, 65000.00, 1300.00, 75400.00, 'Paid', 75400.00, 2025, 0.00, NULL, '2025-09-02 18:09:32', '2025-09-03 05:43:05');

-- --------------------------------------------------------

--
-- Stand-in structure for view `outsourcing_summary`
-- (See below for the actual view)
--
CREATE TABLE `outsourcing_summary` (
`id` int(11)
,`project_details` varchar(500)
,`cost_center` varchar(100)
,`customer_po` varchar(50)
,`vendor_name` varchar(200)
,`cantik_po_no` varchar(100)
,`cantik_po_date` int(11)
,`cantik_po_value` decimal(15,2)
,`remaining_bal_in_po` decimal(15,2)
,`vendor_invoice_frequency` varchar(50)
,`vendor_inv_number` varchar(100)
,`vendor_inv_date` int(11)
,`vendor_inv_value` decimal(15,2)
,`tds_ded` decimal(15,2)
,`net_payble` decimal(15,2)
,`payment_status_from_ntt` varchar(100)
,`payment_value` decimal(15,2)
,`payment_date` int(11)
,`pending_payment` decimal(15,2)
,`remarks` text
,`tds_calculated` decimal(17,2)
,`net_payable_calculated` decimal(18,2)
,`cantik_po_date_formatted` varchar(10)
,`vendor_inv_date_formatted` varchar(10)
,`payment_date_formatted` varchar(10)
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
-- Table structure for table `so_form`
--

CREATE TABLE `so_form` (
  `id` int(11) NOT NULL,
  `project` varchar(255) DEFAULT NULL,
  `cost_centre` varchar(255) DEFAULT NULL,
  `customer_po_no` varchar(50) DEFAULT NULL,
  `billed_po_no` decimal(12,2) DEFAULT NULL,
  `remaining_balance_in_po` decimal(12,2) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `cantik_po_no` varchar(50) DEFAULT NULL,
  `vendor_po_value` decimal(12,2) DEFAULT NULL,
  `vendor_invoicing_till_date` decimal(12,2) DEFAULT NULL,
  `remaining_balance_po` decimal(12,2) DEFAULT NULL,
  `margin_till_date` varchar(20) DEFAULT NULL,
  `target_gm` varchar(20) DEFAULT NULL,
  `variance_in_gm` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_login_signup`
--

CREATE TABLE `users_login_signup` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_login_signup`
--

INSERT INTO `users_login_signup` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@123.com', '$2y$10$HaEMAktwEzYpBjMrwF/tg.XNu3o6dHHSd9LuRN7TalUDen.6Gi2mK', '2025-08-19 11:40:55');

-- --------------------------------------------------------

--
-- Structure for view `billing_summary`
--
DROP TABLE IF EXISTS `billing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `billing_summary`  AS SELECT `bd`.`id` AS `id`, `bd`.`project_details` AS `project_details`, `bd`.`cost_center` AS `cost_center`, `bd`.`customer_po` AS `customer_po`, `bd`.`remaining_balance_in_po` AS `remaining_balance_in_po`, `bd`.`cantik_invoice_no` AS `cantik_invoice_no`, `bd`.`cantik_invoice_date` AS `cantik_invoice_date`, `bd`.`cantik_inv_value_taxable` AS `cantik_inv_value_taxable`, `bd`.`tds` AS `tds`, `bd`.`receivable` AS `receivable`, `bd`.`against_vendor_inv_number` AS `against_vendor_inv_number`, `bd`.`payment_receipt_date` AS `payment_receipt_date`, `bd`.`payment_advise_no` AS `payment_advise_no`, `bd`.`vendor_name` AS `vendor_name`, round(`bd`.`cantik_inv_value_taxable` * 0.02,2) AS `tds_calculated`, round(`bd`.`cantik_inv_value_taxable` * 1.18 - `bd`.`cantik_inv_value_taxable` * 0.02,2) AS `receivable_calculated`, date_format('1899-12-30' + interval `bd`.`cantik_invoice_date` day,'%d-%m-%Y') AS `cantik_invoice_date_formatted`, date_format('1899-12-30' + interval `bd`.`payment_receipt_date` day,'%d-%m-%Y') AS `payment_receipt_date_formatted` FROM `billing_details` AS `bd` ;

-- --------------------------------------------------------

--
-- Structure for view `outsourcing_summary`
--
DROP TABLE IF EXISTS `outsourcing_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `outsourcing_summary`  AS SELECT `od`.`id` AS `id`, `od`.`project_details` AS `project_details`, `od`.`cost_center` AS `cost_center`, `od`.`customer_po` AS `customer_po`, `od`.`vendor_name` AS `vendor_name`, `od`.`cantik_po_no` AS `cantik_po_no`, `od`.`cantik_po_date` AS `cantik_po_date`, `od`.`cantik_po_value` AS `cantik_po_value`, `od`.`remaining_bal_in_po` AS `remaining_bal_in_po`, `od`.`vendor_invoice_frequency` AS `vendor_invoice_frequency`, `od`.`vendor_inv_number` AS `vendor_inv_number`, `od`.`vendor_inv_date` AS `vendor_inv_date`, `od`.`vendor_inv_value` AS `vendor_inv_value`, `od`.`tds_ded` AS `tds_ded`, `od`.`net_payble` AS `net_payble`, `od`.`payment_status_from_ntt` AS `payment_status_from_ntt`, `od`.`payment_value` AS `payment_value`, `od`.`payment_date` AS `payment_date`, `od`.`pending_payment` AS `pending_payment`, `od`.`remarks` AS `remarks`, round(`od`.`vendor_inv_value` * 0.02,2) AS `tds_calculated`, round(`od`.`vendor_inv_value` * 1.18 - `od`.`vendor_inv_value` * 0.02,2) AS `net_payable_calculated`, date_format('1899-12-30' + interval `od`.`cantik_po_date` day,'%d-%m-%Y') AS `cantik_po_date_formatted`, date_format('1899-12-30' + interval `od`.`vendor_inv_date` day,'%d-%m-%Y') AS `vendor_inv_date_formatted`, date_format('1899-12-30' + interval `od`.`payment_date` day,'%d-%m-%Y') AS `payment_date_formatted` FROM `outsourcing_detail` AS `od` ;

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
-- Indexes for table `so_form`
--
ALTER TABLE `so_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_login_signup`
--
ALTER TABLE `users_login_signup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_details`
--
ALTER TABLE `billing_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `finance_tasks`
--
ALTER TABLE `finance_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `outsourcing_detail`
--
ALTER TABLE `outsourcing_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `po_details`
--
ALTER TABLE `po_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `so_form`
--
ALTER TABLE `so_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users_login_signup`
--
ALTER TABLE `users_login_signup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

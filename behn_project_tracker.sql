-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 07:54 AM
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
-- Database: `behn_project_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE IF NOT EXISTS `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `activity_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `responsible_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_participant_count` int(11) DEFAULT 0,
  `actual_participant_count` int(11) DEFAULT 0,
  `participant_count` int(11) DEFAULT 0,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_started','in_progress','completed','has_problem','cancelled') NOT NULL DEFAULT 'not_started',
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE IF NOT EXISTS `attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `activity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(65, 1, 'CREATE', 'ProjectCategory', 23, NULL, '{\"name\":\"ประเภท 1 กิจกรรมเพื่อสนับสนุน และส่งเสริมการจัดบริการสาธารณสุขของหน่วยบริการ หรือสถานบริการ หรือหน่วยงานสาธารณสุขในพื้นที่\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:10:19'),
(66, 1, 'CREATE', 'ProjectCategory', 24, NULL, '{\"name\":\"ประเภท 2 กิจกรรมเพื่อสนับสนุนให้กลุ่มหรือองค์กรประชาชน หรือหน่วยงานอื่นในพื้นที่\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:10:28'),
(67, 1, 'CREATE', 'ProjectCategory', 25, NULL, '{\"name\":\"ประเภท 3 กิจกรรมเพื่อสนับสนุนและส่งเสริมกิจกรรมการสร้างเสริมสุขภาพ ป้องกันโรคของศูนย์เด็กเล็ก ศูนย์พัฒนาคุณภาพชีวิตผู้สูงอายุและคนพิการ\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:10:39'),
(68, 1, 'CREATE', 'ProjectCategory', 26, NULL, '{\"name\":\"ประเภท 4 กิจกรรมเพื่อสนับสนุนค่าใช้จ่ายในการบริหารหรือพัฒนากองทุนหลักประกันสุขภาพ\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:12:31'),
(69, 1, 'CREATE', 'ProjectCategory', 27, NULL, '{\"name\":\"ประเภท 5 กิจกรรมกรณีเกิดโรคระบาดหรือภัยพิบัติในพื้นที่\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:12:40'),
(70, 1, 'CREATE', 'ProjectCategory', 28, NULL, '{\"name\":\"ประเภท 6 กิจกรรมกรณีนโยบายจากคณะกรรมการหลักประกันสุขภาพแห่งชาติ\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:12:49'),
(71, 1, 'CREATE', 'ProjectCategory', 29, NULL, '{\"name\":\"ประเภท 7 กิจกรรมเพื่อสนับสนุนและส่งเสริมการจัดบริการสาธารณสุขอื่นตามมติคณะกรรมการหลักประกันสุขภาพแห่งชาติ\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:12:55'),
(72, 1, 'CREATE', 'ProjectCategory', 30, NULL, '{\"name\":\"ประเภท 8 กิจกรรมเพื่อสนับสนุนและส่งเสริมการจัดบริการพาหนะรับส่งผู้พิการภาพเพื่อเข้ารับบริการสาธารณสุขตามที่สำนักงานกำหนด\",\"description\":\"\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:13:04'),
(73, 1, 'CREATE', 'Project', 90, NULL, '{\"name\":\"โครงการส่งเสริมสุขภาพและป้องกันโรคในชุมชน\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:15:08'),
(74, 1, 'CREATE_SUBPROJECT', 'Project', 91, NULL, '{\"name\":\"โครงการอบรมให้ความรู้การดูแลสุขภาพและป้องกันโรค\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:17:06'),
(75, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 94, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 04:29:42'),
(76, 1, 'DISBURSE', 'Budget', 68, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 04:29:42'),
(77, 1, 'DELETE_DISBURSEMENT', 'Budget', 68, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 04:29:42'),
(78, 1, 'DELETE', 'Project', 90, '{\"name\":\"โครงการส่งเสริมสุขภาพและป้องกันโรคในชุมชน\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:31:37'),
(79, 1, 'CREATE', 'Project', 95, NULL, '{\"name\":\"โครงการส่งเสริมสุขภาพและป้องกันโรคในชุมชน\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-08 04:32:08'),
(80, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 98, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:09:19'),
(81, 1, 'DISBURSE', 'Budget', 69, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:09:19'),
(82, 1, 'DELETE_DISBURSEMENT', 'Budget', 69, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 05:09:19'),
(83, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 99, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:34:18'),
(84, 1, 'DISBURSE', 'Budget', 70, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:34:18'),
(85, 1, 'DELETE_DISBURSEMENT', 'Budget', 70, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 05:34:18'),
(86, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 100, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:36:11'),
(87, 1, 'DISBURSE', 'Budget', 71, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:36:11'),
(88, 1, 'DELETE_DISBURSEMENT', 'Budget', 71, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 05:36:11'),
(89, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 101, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:46:42'),
(90, 1, 'DISBURSE', 'Budget', 72, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:46:42'),
(91, 1, 'DELETE_DISBURSEMENT', 'Budget', 72, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 05:46:42'),
(92, 1, 'UPDATE_STATUS_PROGRESS', 'Project', 102, '{\"status\":\"not_started\",\"progress\":0}', '{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:48:57'),
(93, 1, 'DISBURSE', 'Budget', 73, '{\"previous_disbursed\":0}', '{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', '127.0.0.1', 'CLI/Browser', '2026-09-08 05:48:57'),
(94, 1, 'DELETE_DISBURSEMENT', 'Budget', 73, '{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}', NULL, '127.0.0.1', 'CLI/Browser', '2026-09-08 05:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE IF NOT EXISTS `budgets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `received_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `disbursed_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) GENERATED ALWAYS AS (`received_amount` - `disbursed_amount`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `project_id`, `received_amount`, `allocated_amount`, `disbursed_amount`, `created_at`, `updated_at`) VALUES
(89, 90, 500000.00, 40000.00, 0.00, '2026-09-08 04:15:08', '2026-09-08 04:29:42'),
(90, 94, 100000.00, 100000.00, 0.00, '2026-09-08 04:29:42', '2026-09-08 04:29:42'),
(91, 95, 500000.00, 0.00, 0.00, '2026-09-08 04:32:08', '2026-09-08 05:48:57'),
(92, 98, 100000.00, 100000.00, 0.00, '2026-09-08 05:09:19', '2026-09-08 05:09:19'),
(93, 99, 100000.00, 100000.00, 0.00, '2026-09-08 05:34:18', '2026-09-08 05:34:18'),
(94, 100, 100000.00, 100000.00, 0.00, '2026-09-08 05:36:11', '2026-09-08 05:36:11'),
(95, 101, 100000.00, 100000.00, 0.00, '2026-09-08 05:46:42', '2026-09-08 05:46:42'),
(96, 102, 100000.00, 100000.00, 0.00, '2026-09-08 05:48:57', '2026-09-08 05:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `budget_disbursements`
--

CREATE TABLE IF NOT EXISTS `budget_disbursements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `budget_id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `disbursement_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `recipient` varchar(150) DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `description`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'ENG', 'กองช่าง', 'รับผิดชอบงานก่อสร้าง บำรุงรักษาทาง อาคาร และโครงสร้างพื้นฐาน', '043-222111', '2026-09-04 04:27:07', '2026-09-04 04:27:07'),
(2, 'HLT', 'กองสาธารณสุขและสิ่งแวดล้อม', 'ดูแลงานสุขอนามัย ควบคุมโรค กำจัดขยะมูลฝอย และอนามัยชุมชน', '043-222222', '2026-09-04 04:27:07', '2026-09-04 04:27:07'),
(3, 'EDU', 'กองการศึกษา', 'ส่งเสริมการเรียนรู้ พัฒนาศูนย์เด็กเล็ก โรงเรียน และศาสนาวัฒนธรรม', '043-222333', '2026-09-04 04:27:07', '2026-09-04 04:27:07'),
(4, 'FIN', 'กองคลัง', 'รับผิดชอบงานจัดเก็บรายได้ งบประมาณ การเงิน บัญชี และพัสดุ', '043-222444', '2026-09-04 04:27:07', '2026-09-04 04:27:07'),
(5, 'OFC', 'สำนักปลัดเทศบาล', 'ประสานงานทั่วไป นโยบายและแผน ทะเบียนราษฎร และบรรเทาสาธารณภัย', '043-222555', '2026-09-04 04:27:07', '2026-09-04 04:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_years`
--

CREATE TABLE IF NOT EXISTS `fiscal_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fiscal_years`
--

INSERT INTO `fiscal_years` (`id`, `year`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(18, 2568, 0, '2024-10-01', '2025-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(19, 2569, 1, '2025-10-01', '2026-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(20, 2570, 0, '2026-10-01', '2027-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(21, 2571, 0, '2027-10-01', '2028-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(22, 2572, 0, '2028-10-01', '2029-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(23, 2573, 0, '2029-10-01', '2030-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(24, 2574, 0, '2030-10-01', '2031-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58'),
(25, 2575, 0, '2031-10-01', '2032-09-30', '2026-09-08 04:09:58', '2026-09-08 04:09:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `project_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `project_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 6, 'problem', 'โครงการมีปัญหาต้องแก้ไขเร่งด่วน', 'โครงการ \'โครงการเพิ่มประสิทธิภาพระบบป้องกันและบรรเทาสาธารณภัย\' พบปัญหาอุปสรรค: อยู่ระหว่างประสานงานหน่วยงานที่เกี่ยวข้องเพื่อขออนุญาต', 0, '2026-09-07 05:44:38'),
(2, 3, 15, 'warning', 'ใกล้ถึงกำหนดสิ้นสุดงวดงานตามสัญญา', 'โครงการ \'โครงการก่อสร้างศูนย์บริการสาธารณสุขและฟื้นฟูสุขภาพอัจฉริยะ\' กำหนดส่งมอบงานงวดต่อไปในอีก 15 วัน กรุณาตรวจรับงานตามระเบียบ', 0, '2026-09-06 07:44:38'),
(3, 1, 19, 'success', 'โครงการเสร็จสิ้นสมบูรณ์ 100%', 'โครงการ \'โครงการส่งเสริมสุขภาพผู้สูงอายุ\' ดำเนินการและเบิกจ่ายงบประมาณครบถ้วนตามแผนงานเรียบร้อยแล้ว', 1, '2026-09-04 07:44:38'),
(4, 1, 1, 'info', 'อนุมัติงบประมาณและกรอบแผนพัฒนาเทศบาล', 'แผนการดำเนินโครงการและงบประมาณประจำปีงบประมาณ 2568 - 2569 ผ่านความเห็นชอบจากสภาเทศบาลเรียบร้อยแล้ว', 0, '2026-09-02 07:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `fiscal_year_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `responsible_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsible_person` varchar(255) DEFAULT NULL,
  `activity_type` varchar(100) DEFAULT NULL,
  `objective` text DEFAULT NULL,
  `target_group` varchar(255) DEFAULT NULL,
  `target_quantity` int(11) DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `methodology` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `planned_activity_count` int(11) NOT NULL DEFAULT 1,
  `actual_activity_count` int(11) NOT NULL DEFAULT 0,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `disbursed_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_started','in_progress','completed','has_problem','cancelled') NOT NULL DEFAULT 'not_started',
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `progress_mode` enum('auto','manual') NOT NULL DEFAULT 'auto',
  `problem_description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `parent_id`, `name`, `description`, `fiscal_year_id`, `category_id`, `department_id`, `responsible_user_id`, `responsible_person`, `activity_type`, `objective`, `target_group`, `target_quantity`, `location`, `methodology`, `start_date`, `end_date`, `completion_date`, `planned_activity_count`, `actual_activity_count`, `budget`, `disbursed_amount`, `status`, `progress`, `progress_mode`, `problem_description`, `notes`, `created_at`, `updated_at`) VALUES
(91, 90, 'โครงการอบรมให้ความรู้การดูแลสุขภาพและป้องกันโรค', '', 19, 23, 4, 1, 'นางสาวสุดา ใจดี', 'อบรม / ให้ความรู้ด้านสุขภาพ', 'เพื่อส่งเสริมความรู้และทักษะในการดูแลสุขภาพของประชาชน และป้องกันการเกิดโรคที่พบบ่อยในชุมชน', 'ประชาชนทั่วไปและผู้สูงอายุ', 50, 'ชุมชนวัดใหม่ เทศบาลนครนนทบุรี', '', '2026-08-01', '2026-09-01', NULL, 4, 0, 40000.00, 0.00, 'not_started', 0.00, 'auto', NULL, NULL, '2026-09-08 04:17:06', '2026-09-08 04:17:06'),
(95, NULL, 'โครงการส่งเสริมสุขภาพและป้องกันโรคในชุมชน', '', 19, 23, 4, 1, 'นายสมชาย ใจดี', NULL, NULL, NULL, 0, NULL, NULL, '2026-09-01', '2026-09-30', NULL, 1, 0, 500000.00, 0.00, 'in_progress', 65.00, 'auto', NULL, NULL, '2026-09-08 04:32:08', '2026-09-08 05:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `project_categories`
--

CREATE TABLE IF NOT EXISTS `project_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'folder',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_categories`
--

INSERT INTO `project_categories` (`id`, `name`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(23, 'ประเภท 1 กิจกรรมเพื่อสนับสนุน และส่งเสริมการจัดบริการสาธารณสุขของหน่วยบริการ หรือสถานบริการ หรือหน่วยงานสาธารณสุขในพื้นที่', NULL, 'folder', '2026-09-07 23:10:19', '2026-09-07 23:10:19'),
(24, 'ประเภท 2 กิจกรรมเพื่อสนับสนุนให้กลุ่มหรือองค์กรประชาชน หรือหน่วยงานอื่นในพื้นที่', NULL, 'folder', '2026-09-07 23:10:28', '2026-09-07 23:10:28'),
(25, 'ประเภท 3 กิจกรรมเพื่อสนับสนุนและส่งเสริมกิจกรรมการสร้างเสริมสุขภาพ ป้องกันโรคของศูนย์เด็กเล็ก ศูนย์พัฒนาคุณภาพชีวิตผู้สูงอายุและคนพิการ', NULL, 'folder', '2026-09-07 23:10:39', '2026-09-07 23:10:39'),
(26, 'ประเภท 4 กิจกรรมเพื่อสนับสนุนค่าใช้จ่ายในการบริหารหรือพัฒนากองทุนหลักประกันสุขภาพ', NULL, 'folder', '2026-09-07 23:12:31', '2026-09-07 23:12:31'),
(27, 'ประเภท 5 กิจกรรมกรณีเกิดโรคระบาดหรือภัยพิบัติในพื้นที่', NULL, 'folder', '2026-09-07 23:12:40', '2026-09-07 23:12:40'),
(28, 'ประเภท 6 กิจกรรมกรณีนโยบายจากคณะกรรมการหลักประกันสุขภาพแห่งชาติ', NULL, 'folder', '2026-09-07 23:12:49', '2026-09-07 23:12:49'),
(29, 'ประเภท 7 กิจกรรมเพื่อสนับสนุนและส่งเสริมการจัดบริการสาธารณสุขอื่นตามมติคณะกรรมการหลักประกันสุขภาพแห่งชาติ', NULL, 'folder', '2026-09-07 23:12:55', '2026-09-07 23:12:55'),
(30, 'ประเภท 8 กิจกรรมเพื่อสนับสนุนและส่งเสริมการจัดบริการพาหนะรับส่งผู้พิการภาพเพื่อเข้ารับบริการสาธารณสุขตามที่สำนักงานกำหนด', NULL, 'folder', '2026-09-07 23:13:04', '2026-09-07 23:13:04');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'ผู้ดูแลระบบ (Administrator)', 'จัดการทุกระบบ ผู้ใช้ สิทธิ์ และดู Audit Log', '2026-09-04 04:27:07', '2026-09-04 04:27:07'),
(2, 'executive', 'ผู้บริหาร (Executive)', 'ดู Dashboard, โครงการ, งบประมาณ และรายงานเชิงลึก', '2026-09-04 04:27:07', '2026-09-04 04:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `department_id`, `position`, `phone`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'สมเกียรติ มั่นคง (แอดมิน)', 'admin@municipality.go.th', '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO', 1, 4, 'ผู้อำนวยการศูนย์เทคโนโลยีและสารสนเทศ', '081-1111111', NULL, '2026-09-04 04:27:07', '2026-09-04 05:21:23'),
(2, 'ดร.สมชาย ทรงคุณ (นายกเทศมนตรี)', 'executive@municipality.go.th', '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO', 2, 4, 'นายกเทศมนตรี', '081-2222222', NULL, '2026-09-04 04:27:07', '2026-09-04 05:21:23'),
(3, 'วรรณา จันทร์เพ็ญ (เจ้าหน้าที่กองสาธารณสุข)', 'officer.health@municipality.go.th', '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO', 1, 2, 'นักวิชาการสาธารณสุขปฏิบัติการ', '081-3333333', NULL, '2026-09-04 04:27:07', '2026-09-04 07:42:54'),
(4, 'วิชัย ก่อสร้างดี (เจ้าหน้าที่กองช่าง)', 'officer.eng@municipality.go.th', '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO', 1, 1, 'วิศวกรโยธาชำนาญการ', '081-4444444', NULL, '2026-09-04 04:27:07', '2026-09-04 07:42:54'),
(5, 'สุดา นำสุข (ผู้จัดการโครงการ)', 'pm.suda@municipality.go.th', '$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO', 1, 2, 'พยาบาลวิชาชีพชำนาญการ', '081-5555555', NULL, '2026-09-04 04:27:07', '2026-09-04 07:42:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activities_project` (`project_id`),
  ADD KEY `idx_activities_status` (`status`),
  ADD KEY `fk_activities_responsible` (`responsible_user_id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attachments_project` (`project_id`),
  ADD KEY `fk_attachments_activity` (`activity_id`),
  ADD KEY `fk_attachments_uploader` (`uploaded_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_module` (`module`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`);

--
-- Indexes for table `budget_disbursements`
--
ALTER TABLE `budget_disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_disbursements_budget` (`budget_id`),
  ADD KEY `idx_disbursements_project` (`project_id`),
  ADD KEY `fk_disbursements_creator` (`created_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `fk_notifications_project` (`project_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_projects_parent` (`parent_id`),
  ADD KEY `idx_projects_status` (`status`),
  ADD KEY `idx_projects_fiscal` (`fiscal_year_id`),
  ADD KEY `idx_projects_dept` (`department_id`),
  ADD KEY `fk_projects_category` (`category_id`),
  ADD KEY `fk_projects_responsible` (`responsible_user_id`);

--
-- Indexes for table `project_categories`
--
ALTER TABLE `project_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_role` (`role_id`),
  ADD KEY `fk_users_department` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `budget_disbursements`
--
ALTER TABLE `budget_disbursements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `project_categories`
--
ALTER TABLE `project_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

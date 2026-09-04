-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: municipal_project_tracker
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `municipal_project_tracker`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `municipal_project_tracker` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;

USE `municipal_project_tracker`;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `activity_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `responsible_user_id` bigint(20) unsigned DEFAULT NULL,
  `participant_count` int(11) DEFAULT 0,
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('not_started','in_progress','completed','has_problem','cancelled') NOT NULL DEFAULT 'not_started',
  `progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_activities_project` (`project_id`),
  KEY `idx_activities_status` (`status`),
  KEY `fk_activities_responsible` (`responsible_user_id`),
  CONSTRAINT `fk_activities_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activities_responsible` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
INSERT INTO `activities` VALUES (1,4,'กิจกรรมที่ 1: ตรวจคัดกรองเบาหวานและความดันโลหิต','คัดกรองสุขภาพเบื้องต้น เจาะเลือดตรวจน้ำตาล และวัดความดัน','2024-10-20','ศาลาประชาคม ชุมชนวัดใหม่',3,120,60000.00,'completed',100.00,'ดำเนินการเรียบร้อย ผู้สูงอายุให้ความร่วมมือดี','2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,4,'กิจกรรมที่ 2: อบรมโภชนาการและการออกกำลังกายวัยชรา','บรรยายโดยนักโภชนาการ สาธิตเมนูลดเค็ม ลดหวาน','2024-11-18','ห้องประชุมเทศบาล',3,95,75000.00,'completed',100.00,'มีคู่มือแจกให้ผู้เข้าร่วมทุกคน','2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,4,'กิจกรรมที่ 3: กิจกรรมประเมินสุขภาพจิตและคลายเครียด','การทำสมาธิ งานประดิษฐ์บำบัด และดนตรีบำบัด','2024-12-15','สวนสุขภาพเทศบาล',3,80,75000.00,'completed',100.00,'บรรยากาศอบอุ่น ผู้สูงอายุมีความสุข','2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,4,'กิจกรรมที่ 4: สรุปผลและมอบเกียรติบัตรผู้สูงอายุต้นแบบ','สรุปรายงานผลการตรวจสุขภาพรอบ 1 ปี และมอบรางวัล','2025-01-25','อาคารอเนกประสงค์',3,110,90000.00,'completed',100.00,'เสร็จสิ้นครบถ้วนตามเป้าหมาย 100%','2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,5,'กิจกรรมที่ 1: เปิดลานแอโรบิกและจัดซื้อเครื่องเสียง','เปิดตัวโครงการและเริ่มคลาสเต้นแอโรบิกประจำสัปดาห์','2024-11-05','ลานกีฬาเทศบาล',5,250,100000.00,'completed',100.00,'ประชาชนมาร่วมงานหนาแน่น','2026-09-04 04:27:07','2026-09-04 04:27:07'),(6,5,'กิจกรรมที่ 2: กิจกรรมเดิน-วิ่ง เพื่อสุขภาพรับลมหนาว','จัดกิจกรรม Fun Run ระยะทาง 3.5 กม. ในเขตเทศบาล','2024-12-22','เส้นทางรอบคูเมืองเทศบาล',5,380,80000.00,'completed',100.00,'สำเร็จลุล่วงด้วยดี ปลอดภัย ไม่มีอุบัติเหตุ','2026-09-04 04:27:07','2026-09-04 04:27:07'),(7,5,'กิจกรรมที่ 3: ติดตั้งเครื่องออกกำลังกายกลางแจ้ง ชุมชนทุ่งทอง','จัดหาและติดตั้งเครื่องบริหารร่างกายกลางแจ้ง 6 ชุด','2025-01-15','สวนหย่อมชุมชนทุ่งทอง',5,50,60000.00,'completed',100.00,'ติดตั้งเสร็จสมบูรณ์ มีผู้ใช้งานทุกวัน','2026-09-04 04:27:07','2026-09-04 04:27:07'),(8,5,'กิจกรรมที่ 4: เวิร์กช็อปฝึกทักษะการเล่นแบดมินตันเยาวชน','อบรมพื้นฐานการเล่นแบดมินตันโดยผู้ฝึกสอนระดับชาติ','2025-03-10','โรงยิมเนเซียมเทศบาล',5,40,70000.00,'in_progress',40.00,'อยู่ระหว่างรับสมัครเยาวชนเข้าร่วม','2026-09-04 04:27:07','2026-09-04 04:27:07'),(9,5,'กิจกรรมที่ 5: การประกวดเต้นเพื่อสุขภาพรอบชิงชนะเลิศ','ประกวดทีมแอโรบิกจาก 8 ชุมชน','2025-05-20','เวทีกลางแจ้งสวนสาธารณะ',5,150,90000.00,'not_started',0.00,'เตรียมความพร้อมด้านสถานที่','2026-09-04 04:27:07','2026-09-04 04:27:07'),(10,6,'กิจกรรมที่ 1: ประชุมชี้แจงผู้ประกอบการร้านค้าในตลาดสด','แจ้งเกณฑ์มาตรฐานสุขาภิบาลอาหารและข้อกำหนดเทศบาล','2024-12-10','ห้องประชุมกองสาธารณสุข',3,90,40000.00,'completed',100.00,'พ่อค้าแม่ค้าเข้าร่วมประชุมครบถ้วน','2026-09-04 04:27:07','2026-09-04 04:27:07'),(11,6,'กิจกรรมที่ 2: สุ่มตรวจสารเคมีตกค้างและฟอร์มาลินในอาหารสด','สุ่มตรวจอาหารทะเล เนื้อสัตว์ และผักสด','2025-01-20','ตลาดสดเทศบาล',3,30,70000.00,'has_problem',20.00,'รอชุดทดสอบ Test Kit เพิ่มเติมจากกรมวิทยาศาสตร์การแพทย์','2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attachments_project` (`project_id`),
  KEY `fk_attachments_activity` (`activity_id`),
  KEY `fk_attachments_uploader` (`uploaded_by`),
  CONSTRAINT `fk_attachments_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attachments_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attachments_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_module` (`module`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'INITIALIZE','System',1,NULL,'{\"event\":\"เริ่มต้นระบบติดตามโครงการเทศบาล\",\"version\":\"1.0\"}','127.0.0.1','System Initializer','2026-09-04 04:27:07'),(2,3,'CREATE','Project',4,NULL,'{\"project_code\":\"SUB-2568-001-01\",\"name\":\"โครงการส่งเสริมสุขภาพผู้สูงอายุ\",\"budget\":300000}','127.0.0.1','Mozilla/5.0','2026-09-04 04:27:07'),(3,3,'UPDATE_PROGRESS','Project',4,'{\"progress\":75,\"status\":\"in_progress\"}','{\"progress\":100,\"status\":\"completed\"}','127.0.0.1','Mozilla/5.0','2026-09-04 04:27:07'),(4,3,'REPORT_PROBLEM','Project',6,'{\"status\":\"in_progress\"}','{\"status\":\"has_problem\",\"problem\":\"ชุดทดสอบสารปนเปื้อนขาดแคลน\"}','127.0.0.1','Mozilla/5.0','2026-09-04 04:27:07'),(5,4,'DISBURSE','Budget',7,'{\"disbursed_amount\":600000}','{\"disbursed_amount\":1500000,\"added\":900000}','127.0.0.1','Mozilla/5.0','2026-09-04 04:27:07'),(6,1,'UPDATE_PROGRESS','Project',7,'{\"progress\":75,\"status\":\"in_progress\",\"actual\":3}','{\"progress\":100,\"status\":\"completed\",\"actual\":4}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 04:29:59'),(7,1,'UPDATE_PROGRESS','Project',8,'{\"progress\":0,\"status\":\"not_started\",\"actual\":0}','{\"progress\":33.33,\"status\":\"in_progress\",\"actual\":1}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 04:32:22'),(8,1,'UPDATE_PROGRESS','Project',8,'{\"progress\":33.33,\"status\":\"in_progress\",\"actual\":1}','{\"progress\":66.67,\"status\":\"in_progress\",\"actual\":2}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 04:32:27'),(9,2,'SWITCH_ROLE','Auth',2,NULL,'{\"switched_to\":\"executive\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:35:21'),(10,4,'SWITCH_ROLE','Auth',4,NULL,'{\"switched_to\":\"officer\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:35:23'),(11,5,'SWITCH_ROLE','Auth',5,NULL,'{\"switched_to\":\"project_manager\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:35:25'),(12,5,'LOGOUT','Auth',5,NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:38:00'),(13,1,'UPDATE_PROGRESS','Project',6,'{\"progress\":25,\"status\":\"has_problem\",\"actual\":1}','{\"progress\":50,\"status\":\"has_problem\",\"actual\":2}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:40:35'),(14,1,'UPDATE_PROGRESS','Project',6,'{\"progress\":50,\"status\":\"has_problem\",\"actual\":2}','{\"progress\":75,\"status\":\"has_problem\",\"actual\":3}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:40:37'),(15,1,'UPDATE_PROGRESS','Project',6,'{\"progress\":75,\"status\":\"has_problem\",\"actual\":3}','{\"progress\":100,\"status\":\"completed\",\"actual\":4}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0','2026-09-04 04:40:38'),(16,1,'UPDATE_STATUS_PROGRESS','Project',8,'{\"status\":\"in_progress\",\"progress\":66.67}','{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}','127.0.0.1','CLI/Browser','2026-09-04 04:50:01'),(17,1,'UPDATE_STATUS_PROGRESS','Project',8,'{\"status\":\"in_progress\",\"progress\":65}','{\"status\":\"completed\",\"progress\":100,\"problem_description\":null}','127.0.0.1','CLI/Browser','2026-09-04 04:50:01'),(18,1,'UPDATE_STATUS_PROGRESS','Project',8,'{\"status\":\"completed\",\"progress\":100}','{\"status\":\"not_started\",\"progress\":0,\"problem_description\":null}','127.0.0.1','CLI/Browser','2026-09-04 04:50:01'),(19,2,'SWITCH_ROLE','Auth',2,NULL,'{\"switched_to\":\"executive\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:01:09'),(20,1,'SWITCH_ROLE','Auth',1,NULL,'{\"switched_to\":\"admin\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:01:36'),(21,2,'SWITCH_ROLE','Auth',2,NULL,'{\"switched_to\":\"executive\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:10:31'),(22,3,'SWITCH_ROLE','Auth',3,NULL,'{\"switched_to\":\"officer\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:10:33'),(23,5,'SWITCH_ROLE','Auth',5,NULL,'{\"switched_to\":\"project_manager\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:10:36'),(24,4,'SWITCH_ROLE','Auth',4,NULL,'{\"switched_to\":\"officer\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:10:37'),(25,1,'SWITCH_ROLE','Auth',1,NULL,'{\"switched_to\":\"admin\"}','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','2026-09-04 05:10:43'),(26,1,'UPDATE_STATUS_PROGRESS','Project',9,'{\"status\":\"not_started\",\"progress\":0}','{\"status\":\"in_progress\",\"progress\":65,\"problem_description\":null}','127.0.0.1','CLI/Browser','2026-09-04 05:34:00'),(27,1,'DISBURSE','Budget',9,'{\"previous_disbursed\":0}','{\"amount\":35000,\"new_disbursed\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}','127.0.0.1','CLI/Browser','2026-09-04 05:34:00'),(28,1,'DELETE_DISBURSEMENT','Budget',9,'{\"amount\":35000,\"description\":\"ค่าใช้จ่ายกิจกรรมทดสอบที่ 1\"}',NULL,'127.0.0.1','CLI/Browser','2026-09-04 05:34:00');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budget_disbursements`
--

DROP TABLE IF EXISTS `budget_disbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budget_disbursements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `budget_id` bigint(20) unsigned NOT NULL,
  `project_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `disbursement_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `recipient` varchar(150) DEFAULT NULL,
  `evidence_file` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_disbursements_budget` (`budget_id`),
  KEY `idx_disbursements_project` (`project_id`),
  KEY `fk_disbursements_creator` (`created_by`),
  CONSTRAINT `fk_disbursements_budget` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_disbursements_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_disbursements_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budget_disbursements`
--

LOCK TABLES `budget_disbursements` WRITE;
/*!40000 ALTER TABLE `budget_disbursements` DISABLE KEYS */;
INSERT INTO `budget_disbursements` VALUES (1,4,4,150000.00,'2024-10-18','เบิกจ่ายงวดที่ 1 ค่าเวชภัณฑ์และชุดตรวจสุขภาพผู้สูงอายุ','บริษัท สยามการแพทย์ จำกัด','evidence_01.pdf',3,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,4,4,150000.00,'2025-01-20','เบิกจ่ายงวดสุดท้าย ค่าวิทยากรและของรางวัลผู้สูงอายุต้นแบบ','กลุ่มผู้สูงอายุเทศบาล','evidence_02.pdf',3,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,5,5,140000.00,'2024-11-02','เบิกจ่ายค่าชุดเครื่องเสียงและเวทีลานแอโรบิก','หจก. เมืองไทยซาวด์','evidence_03.pdf',5,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,5,5,100000.00,'2024-12-18','เบิกจ่ายค่าเสื้อและเหรียญรางวัล กิจกรรมเดิน-วิ่ง Fun Run','โรงงานสปอร์ตเชียงใหม่','evidence_04.pdf',5,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,6,6,110000.00,'2024-12-05','เบิกจ่ายค่าเอกสารประชาสัมพันธ์และชุดตรวจ Test Kit ล็อตแรก','องค์การเภสัชกรรม','evidence_05.pdf',3,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(6,7,7,600000.00,'2024-12-10','เงินล่วงหน้าตามสัญญาจ้างปรับปรุงผิวจราจร ซอย 1-5','บจก. ธนทรัพย์โยธาการ','evidence_contract_01.pdf',4,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(7,7,7,900000.00,'2025-02-05','เบิกจ่ายเงินค่างวดที่ 1 งานปูแอสฟัลท์ติก ซอย 1 และ 2','บจก. ธนทรัพย์โยธาการ','evidence_contract_02.pdf',4,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(8,8,8,350000.00,'2025-01-15','เงินมัดจำการสั่งซื้อเสาและชุดโคมไฟ Solar LED 80 ชุด','บจก. กรีนโซลาร์เทค','evidence_solar_01.pdf',4,'2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `budget_disbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `budgets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint(20) unsigned NOT NULL,
  `received_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `disbursed_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) GENERATED ALWAYS AS (`received_amount` - `disbursed_amount`) STORED,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`),
  CONSTRAINT `fk_budgets_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `budgets`
--

LOCK TABLES `budgets` WRITE;
/*!40000 ALTER TABLE `budgets` DISABLE KEYS */;
INSERT INTO `budgets` VALUES (1,1,1000000.00,1000000.00,650000.00,350000.00,'2026-09-04 04:27:07','2026-09-04 05:34:00'),(2,2,3500000.00,3500000.00,1850000.00,1650000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,3,600000.00,600000.00,0.00,600000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,4,300000.00,300000.00,300000.00,0.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,5,400000.00,400000.00,240000.00,160000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(6,6,300000.00,300000.00,110000.00,190000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(7,7,2200000.00,2200000.00,1500000.00,700000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(8,8,1300000.00,1300000.00,350000.00,950000.00,'2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'DP-ENG','กองช่าง','รับผิดชอบงานก่อสร้าง บำรุงรักษาทาง อาคาร และโครงสร้างพื้นฐาน','053-123451','2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,'DP-HEALTH','กองสาธารณสุขและสิ่งแวดล้อม','รับผิดชอบงานสร้างเสริมสุขภาพ ป้องกันโรค และจัดการสิ่งแวดล้อม','053-123452','2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,'DP-EDU','สำนักการศึกษา','รับผิดชอบงานส่งเสริมการศึกษา ศูนย์พัฒนาเด็กเล็ก และวัฒนธรรมประเพณี','053-123453','2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,'DP-OFFICE','สำนักปลัดเทศบาล','รับผิดชอบงานธุรการ การประสานงานนโยบาย และความปลอดภัยในชุมชน','053-123454','2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,'DP-FIN','กองคลัง','รับผิดชอบงานงบประมาณ การเงิน บัญชี และพัสดุ','053-123455','2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fiscal_years`
--

DROP TABLE IF EXISTS `fiscal_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fiscal_years` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fiscal_years`
--

LOCK TABLES `fiscal_years` WRITE;
/*!40000 ALTER TABLE `fiscal_years` DISABLE KEYS */;
INSERT INTO `fiscal_years` VALUES (1,2567,0,'2023-10-01','2024-09-30','2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,2568,1,'2024-10-01','2025-09-30','2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,2569,0,'2025-10-01','2026-09-30','2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `fiscal_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `project_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user` (`user_id`),
  KEY `fk_notifications_project` (`project_id`),
  CONSTRAINT `fk_notifications_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,3,6,'problem','โครงการมีปัญหาต้องแก้ไข','โครงการส่งเสริมสุขภาพประชาชนและตรวจสารปนเปื้อนในอาหาร พบปัญหา: ชุดทดสอบขาดแคลน',0,'2026-09-04 04:27:07'),(2,4,7,'warning','ใกล้ถึงกำหนดสิ้นสุดงวดงาน','โครงการปรับปรุงผิวจราจร ซอยเทศบาล 1-5 ครบกำหนดงวดที่ 2 ในอีก 15 วัน',0,'2026-09-04 04:27:07'),(3,1,4,'success','โครงการเสร็จสิ้นสมบูรณ์ 100%','โครงการส่งเสริมสุขภาพผู้สูงอายุ ดำเนินการเสร็จสิ้นครบ 4 กิจกรรมแล้ว',1,'2026-09-04 04:27:07');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_categories`
--

DROP TABLE IF EXISTS `project_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'folder',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_categories`
--

LOCK TABLES `project_categories` WRITE;
/*!40000 ALTER TABLE `project_categories` DISABLE KEYS */;
INSERT INTO `project_categories` VALUES (1,'โครงสร้างพื้นฐาน','งานคมนาคม ไฟฟ้า ประปา ระบายน้ำ และผังเมือง','truck','2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,'สาธารณสุขและคุณภาพชีวิต','การดูแลสุขภาพ สุขาภิบาล และการดูแลผู้สูงอายุ','heart-pulse','2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,'การศึกษาและวัฒนธรรม','การส่งเสริมการเรียนรู้ ทักษะอาชีพ และศิลปวัฒนธรรม','graduation-cap','2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,'สิ่งแวดล้อมและทรัพยากร','การจัดการขยะ น้ำเสีย พื้นที่สีเขียว และพลังงานทดแทน','leaf','2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,'สังคมและเศรษฐกิจชุมชน','การสงเคราะห์ผู้ด้อยโอกาส วิสาหกิจชุมชน และท่องเที่ยว','users','2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `project_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `project_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `fiscal_year_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `responsible_user_id` bigint(20) unsigned DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_code` (`project_code`),
  KEY `idx_projects_parent` (`parent_id`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_fiscal` (`fiscal_year_id`),
  KEY `idx_projects_dept` (`department_id`),
  KEY `fk_projects_category` (`category_id`),
  KEY `fk_projects_responsible` (`responsible_user_id`),
  CONSTRAINT `fk_projects_category` FOREIGN KEY (`category_id`) REFERENCES `project_categories` (`id`),
  CONSTRAINT `fk_projects_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `fk_projects_fiscal` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`),
  CONSTRAINT `fk_projects_parent` FOREIGN KEY (`parent_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_projects_responsible` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,NULL,'PRJ-2568-001','โครงการพัฒนาคุณภาพชีวิตและส่งเสริมสุขภาพประชาชนแบบยั่งยืน','โครงการบูรณาการเพื่อดูแลสุขภาพของประชาชนทุกกลุ่มวัย ทั้งผู้สูงอายุ วัยทำงาน และเด็กในเขตเทศบาล',2,2,2,3,NULL,NULL,NULL,0,NULL,NULL,'2024-10-01','2025-09-30',NULL,1,0,1000000.00,650000.00,'in_progress',86.67,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 05:34:00'),(2,NULL,'PRJ-2568-002','โครงการยกระดับโครงสร้างพื้นฐานและเมืองน่าอยู่อัจฉริยะ','โครงการปรับปรุงระบบคมนาคม ผิวจราจร และระบบไฟส่องสว่างพลังงานแสงอาทิตย์ทั่วเขตเทศบาล',2,1,1,4,NULL,NULL,NULL,0,NULL,NULL,'2024-11-01','2025-08-31',NULL,1,0,3500000.00,1850000.00,'in_progress',50.00,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:50:01'),(3,NULL,'PRJ-2568-003','โครงการจัดการขยะชุมชนและอนุรักษ์สิ่งแวดล้อม','โครงการรณรงค์คัดแยกขยะต้นทาง จัดตั้งธนาคารขยะ และลดมลพิษทางอากาศ',2,4,2,3,NULL,NULL,NULL,0,NULL,NULL,'2025-01-01','2025-07-31',NULL,1,0,600000.00,0.00,'not_started',0.00,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,1,'SUB-2568-001-01','โครงการส่งเสริมสุขภาพผู้สูงอายุ','ตรวจสุขภาพ คัดกรองโรคเรื้อรัง และกิจกรรมนันทนาการสำหรับชมรมผู้สูงอายุ',2,2,2,3,'ตรวจสุขภาพและอบรมเชิงปฏิบัติการ','เพื่อให้ผู้สูงอายุมีความรู้และได้รับการตรวจสุขภาพประจำปีอย่างครอบคลุม','ผู้สูงอายุในเขตเทศบาล',350,'อาคารอเนกประสงค์เทศบาล','ประสานงาน รพ.สต. และจัดอบรมภาคปฏิบัติ','2024-10-15','2025-01-31','2025-01-28',4,4,300000.00,300000.00,'completed',100.00,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(5,1,'SUB-2568-001-02','โครงการออกกำลังกายเพื่อสุขภาพ','จัดกิจกรรมเต้นแอโรบิก เดิน-วิ่งเพื่อสุขภาพ และจัดหาอุปกรณ์กีฬาประจำชุมชน',2,2,2,5,'จัดลานกีฬาและนำเต้นแอโรบิก','เพื่อกระตุ้นให้ประชาชนออกกำลังกายอย่างสม่ำเสมอ ลดความเสี่ยงโรค NCDs','ประชาชนทั่วไป',500,'สวนสาธารณะเฉลิมพระเกียรติ','จ้างวิทยากรผู้นำเต้นและจัดกิจกรรมสัปดาห์ละ 3 วัน','2024-11-01','2025-06-30',NULL,5,3,400000.00,240000.00,'in_progress',60.00,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:27:07'),(6,1,'SUB-2568-001-03','โครงการส่งเสริมสุขภาพประชาชนและตรวจสารปนเปื้อนในอาหาร','ตรวจสารเคมีตกค้างในตลาดสด และรณรงค์ร้านอาหารปลอดภัย',2,2,2,3,'สุ่มตรวจสารปนเปื้อนและให้ความรู้ผู้ค้า','เพื่อยกระดับมาตรฐานความปลอดภัยด้านอาหารในตลาดสดและร้านอาหาร','ผู้ประกอบการค้าอาหาร',120,'ตลาดสดเทศบาล','ลงพื้นที่เก็บตัวอย่างและใช้ชุดทดสอบเบื้องต้น (Test Kit)','2024-12-01','2025-05-31','2026-09-04',4,4,300000.00,110000.00,'completed',100.00,'auto','ชุดทดสอบสารปนเปื้อน (Test Kit) ขาดแคลนเนื่องจากบริษัทผู้ผลิตส่งมอบล่าช้ากว่ากำหนด',NULL,'2026-09-04 04:27:07','2026-09-04 04:40:38'),(7,2,'SUB-2568-002-01','โครงการปรับปรุงผิวจราจรแอสฟัลท์ติกคอนกรีต ซอยเทศบาล 1-5','ปรับปรุงผิวทางที่ชำรุดเป็นหลุมบ่อเพื่อความปลอดภัยในการสัญจร',2,1,1,4,'งานก่อสร้างและปรับปรุงทาง','เพื่อแก้ปัญหาน้ำท่วมขังและอุบัติเหตุจากการสัญจร','ประชาชนผู้ใช้เส้นทาง',2500,'ซอยเทศบาล 1 ถึง ซอย 5','ประกวดราคาอิเล็กทรอนิกส์ (e-bidding) และควบคุมงานก่อสร้าง','2024-11-15','2025-04-30','2026-09-04',4,4,2200000.00,1500000.00,'completed',100.00,'auto',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:29:59'),(8,2,'SUB-2568-002-02','โครงการติดตั้งไฟฟ้าแสงสว่างพลังงานแสงอาทิตย์ (Solar LED)','ติดตั้งโคมไฟ Solar LED ในจุดเสี่ยงและซอยเปลี่ยวเพื่อป้องกันอาชญากรรม',2,1,1,4,'งานจัดซื้อพร้อมติดตั้งระบบไฟฟ้า','เพื่อเพิ่มแสงสว่างในยามค่ำคืนและประหยัดพลังงานไฟฟ้าของเทศบาล','ประชาชนในชุมชนรอบนอก',1800,'ชุมชนทุ่งทอง และชุมชนร่วมใจ','จัดซื้อจัดจ้างตามระเบียบพัสดุและติดตั้งเสาพร้อมโคมไฟ 80 จุด','2025-02-01','2025-08-15',NULL,3,0,1300000.00,350000.00,'not_started',0.00,'manual',NULL,NULL,'2026-09-04 04:27:07','2026-09-04 04:50:01');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','ผู้ดูแลระบบ (Administrator)','จัดการทุกระบบ ผู้ใช้ สิทธิ์ และดู Audit Log','2026-09-04 04:27:07','2026-09-04 04:27:07'),(2,'executive','ผู้บริหาร (Executive)','ดู Dashboard, โครงการ, งบประมาณ และรายงานเชิงลึก','2026-09-04 04:27:07','2026-09-04 04:27:07'),(3,'officer','เจ้าหน้าที่ (Officer)','สร้างและแก้ไขโครงการ, เพิ่มกิจกรรม, อัปเดตความคืบหน้า','2026-09-04 04:27:07','2026-09-04 04:27:07'),(4,'project_manager','ผู้ดูแลโครงการ (Project Manager)','ดูแลโครงการที่ได้รับมอบหมาย, อัปเดตสถานะและกิจกรรม','2026-09-04 04:27:07','2026-09-04 04:27:07');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_department` (`department_id`),
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'สมเกียรติ มั่นคง (แอดมิน)','admin@municipality.go.th','$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO',1,4,'ผู้อำนวยการศูนย์เทคโนโลยีและสารสนเทศ','081-1111111',NULL,'2026-09-04 04:27:07','2026-09-04 05:21:23'),(2,'ดร.สมชาย ทรงคุณ (นายกเทศมนตรี)','executive@municipality.go.th','$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO',2,4,'นายกเทศมนตรี','081-2222222',NULL,'2026-09-04 04:27:07','2026-09-04 05:21:23'),(3,'วรรณา จันทร์เพ็ญ (เจ้าหน้าที่กองสาธารณสุข)','officer.health@municipality.go.th','$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO',3,2,'นักวิชาการสาธารณสุขปฏิบัติการ','081-3333333',NULL,'2026-09-04 04:27:07','2026-09-04 05:21:23'),(4,'วิชัย ก่อสร้างดี (เจ้าหน้าที่กองช่าง)','officer.eng@municipality.go.th','$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO',3,1,'วิศวกรโยธาชำนาญการ','081-4444444',NULL,'2026-09-04 04:27:07','2026-09-04 05:21:23'),(5,'สุดา นำสุข (ผู้จัดการโครงการ)','pm.suda@municipality.go.th','$2y$10$EulcO7BKJ9sqnUMTvwK8z.hXVkqIbW480F5H/MFrScpCTlxgfWfzO',4,2,'พยาบาลวิชาชีพชำนาญการ','081-5555555',NULL,'2026-09-04 04:27:07','2026-09-04 05:21:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-04 12:34:46

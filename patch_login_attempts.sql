-- ==============================================================================
-- SQL Patch: Create login_attempts table for Rate Limiting & Brute-Force Defense
-- Target Database: behn_project_tracker (or municipal_project_tracker)
-- Compatible with: MySQL 8.0+, MariaDB 10.4+
-- ==============================================================================

-- คำแนะนำการใช้งานบน Plesk Obsidian:
-- 1. เข้าสู่ Plesk Panel -> เมนู "Databases"
-- 2. เลือกฐานข้อมูล "behn_project_tracker" แล้วคลิก "phpMyAdmin"
-- 3. คลิกแท็บ "SQL" ที่เมนูด้านบน
-- 4. คัดลอกคำสั่งด้านล่างนี้ไปวาง แล้วกดปุ่ม "Go" (ลงมือ)

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IPv4 or IPv6 client address',
  `username` VARCHAR(150) NOT NULL COMMENT 'Attempted username or email',
  `status` ENUM('success', 'failure', 'locked') NOT NULL DEFAULT 'failure',
  `user_agent` VARCHAR(255) NULL COMMENT 'Client User-Agent signature',
  `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of attempt',
  PRIMARY KEY (`id`),
  INDEX `idx_ip_attempted` (`ip_address`, `attempted_at`),
  INDEX `idx_user_attempted` (`username`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

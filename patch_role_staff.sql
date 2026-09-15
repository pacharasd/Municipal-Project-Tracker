-- ==============================================================================
-- SQL Patch: Add 'staff' (เจ้าหน้าที่) Role into roles table
-- Target Database: behn_project_tracker (or municipal_project_tracker)
-- Compatible with: MySQL 8.0+, MariaDB 10.4+
-- ==============================================================================

-- ตรวจสอบและเพิ่มบทบาท staff หากยังไม่มีในระบบ
INSERT INTO `roles` (`name`, `display_name`, `description`, `created_at`, `updated_at`)
SELECT 'staff', 'เจ้าหน้าที่ (Staff)', 'จัดการเพิ่ม ลบ แก้ไข กิจกรรมหลักและกิจกรรมย่อย', NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE `name` = 'staff');

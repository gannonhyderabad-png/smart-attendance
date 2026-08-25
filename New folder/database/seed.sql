-- ==========================================================
-- Attendance Management Server Seed Data
-- ==========================================================

-- Default Admin User (Password: admin123)
-- Hash generated using PASSWORD_BCRYPT for 'admin123'
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`) 
VALUES 
(1, 'System Administrator', 'admin@attendance.local', '$2y$10$wN1o8aT4oQv9Zz0u1e2y.e4w5q6r7t8y9u0i1o2p3a4s5d6f7g8h9', 'admin', 'active')
ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`);

-- Default Departments
INSERT INTO `departments` (`id`, `name`, `code`, `description`) 
VALUES 
(1, 'Engineering & Tech', 'ENG', 'Software Development & IT Support'),
(2, 'Human Resources', 'HR', 'People Operations & Talent Management'),
(3, 'Sales & Marketing', 'MKT', 'Direct Sales, Growth & Client Success'),
(4, 'Operations', 'OPS', 'Logistics, Support & Administration')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) 
VALUES 
('company_name', 'Smart Attendance Management'),
('company_email', 'admin@attendance.com'),
('company_phone', '+91 99999 99999'),
('work_hours_per_day', '8'),
('grace_period_minutes', '15'),
('allow_geo_capture', '1'),
('auto_calculate_hours', '1'),
('site_logo_text', 'SmartAttendance')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) 
VALUES 
('company_name', 'TechCorp Solutions Inc.'),
('company_email', 'hr@techcorp.com'),
('company_phone', '+1 (800) 555-0199'),
('work_hours_per_day', '8'),
('grace_period_minutes', '15'),
('allow_geo_capture', '1'),
('auto_calculate_hours', '1'),
('site_logo_text', 'SmartAttendance')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

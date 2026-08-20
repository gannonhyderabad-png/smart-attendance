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

-- Sample Employees with pre-assigned punch tokens and projects
INSERT INTO `employees` (`id`, `employee_code`, `name`, `email`, `phone`, `department_id`, `designation`, `project`, `punch_token`, `shift_start`, `shift_end`, `status`) 
VALUES 
(1, 'EMP001', 'Alex Johnson', 'alex.j@company.com', '+1 (555) 019-2831', 1, 'Senior Backend Engineer', 'Enterprise Helpdesk ERP', 'tok_alex_emp001_87a6', '09:00:00', '18:00:00', 'active'),
(2, 'EMP002', 'Sarah Williams', 'sarah.w@company.com', '+1 (555) 019-2832', 2, 'HR Business Partner', 'People & Talent Portal', 'tok_sarah_emp002_94c1', '09:00:00', '18:00:00', 'active'),
(3, 'EMP003', 'Michael Chen', 'michael.c@company.com', '+1 (555) 019-2833', 1, 'DevOps Specialist', 'Cloud Infrastructure 2.0', 'tok_mike_emp003_12d4', '10:00:00', '19:00:00', 'active'),
(4, 'EMP004', 'Emily Davis', 'emily.d@company.com', '+1 (555) 019-2834', 3, 'Marketing Lead', 'Global Growth 2026', 'tok_emily_emp004_55f8', '09:30:00', '18:30:00', 'active')
ON DUPLICATE KEY UPDATE `employee_code` = VALUES(`employee_code`);

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

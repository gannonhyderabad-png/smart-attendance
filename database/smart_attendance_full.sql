-- ========================================================
-- Smart Attendance SQL Dump for Visual Studio / Any SQL Server
-- Generated: 2026-11-15 08:13:37
-- ========================================================

-- Table structure for `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'admin',
            status TEXT DEFAULT 'active',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        , company_mail TEXT, assigned_site VARCHAR(150) DEFAULT NULL);

-- Dumping data for `users` (2 rows)
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `last_login`, `created_at`, `updated_at`, `company_mail`, `assigned_site`) VALUES ('1', 'Saikumar', 'hyd.itserv@gdcl.in', '$2y$10$bmKP/S6b6t56MXIRHec2FOHZLFHU0xdF3sLOJMpIhGrWqcE6NGoeK', 'admin', 'active', '2026-08-19 18:20:00', '2026-08-18 09:21:46', '2026-08-18 09:21:46', NULL, NULL);
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `last_login`, `created_at`, `updated_at`, `company_mail`, `assigned_site`) VALUES ('2', 'System Administrator', 'admin@attendance.local', '$2y$10$KD4Jo4AsywetvMtVxA4fRu9lpsJLCaIcPZOjEbO4dDm16Qua97U2i', 'admin', 'active', '2026-11-11 08:49:10', '2026-08-19 18:32:42', '2026-08-19 18:32:42', NULL, NULL);

-- Table structure for `departments`
DROP TABLE IF EXISTS `departments`;
CREATE TABLE departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            code TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        , head_name TEXT, head_email TEXT);

-- Dumping data for `departments` (3 rows)
INSERT INTO `departments` (`id`, `name`, `code`, `description`, `created_at`, `head_name`, `head_email`) VALUES ('1', 'Engineering & Tech', 'ENG', 'Software Development & IT Support', '2026-08-18 09:21:46', NULL, NULL);
INSERT INTO `departments` (`id`, `name`, `code`, `description`, `created_at`, `head_name`, `head_email`) VALUES ('4', 'Operations', 'OPS', 'Logistics, Support & Administration', '2026-08-18 09:21:46', NULL, NULL);
INSERT INTO `departments` (`id`, `name`, `code`, `description`, `created_at`, `head_name`, `head_email`) VALUES ('40', 'it', 'IT', NULL, '2026-08-20 08:29:56', NULL, NULL);

-- Table structure for `employees`
DROP TABLE IF EXISTS `employees`;
CREATE TABLE employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            department_id INTEGER,
            designation TEXT,
            project TEXT,
            punch_token TEXT NOT NULL UNIQUE,
            shift_start TEXT DEFAULT '09:00:00',
            shift_end TEXT DEFAULT '18:00:00',
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, site TEXT, site_latitude REAL, site_longitude REAL, site_radius INTEGER DEFAULT 200, geofence_enabled INTEGER DEFAULT 1, photo TEXT, department TEXT, deleted_at DATETIME NULL, cl_quota REAL DEFAULT NULL, sl_quota REAL DEFAULT NULL, pl_quota REAL DEFAULT NULL, employee_type VARCHAR(50) DEFAULT 'Permanent', contract_details VARCHAR(255) DEFAULT NULL,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        );

-- Dumping data for `employees` (4 rows)
INSERT INTO `employees` (`id`, `employee_code`, `name`, `email`, `phone`, `department_id`, `designation`, `project`, `punch_token`, `shift_start`, `shift_end`, `status`, `created_at`, `updated_at`, `site`, `site_latitude`, `site_longitude`, `site_radius`, `geofence_enabled`, `photo`, `department`, `deleted_at`, `cl_quota`, `sl_quota`, `pl_quota`, `employee_type`, `contract_details`) VALUES ('6', 'EMP-005', 'Saikumar', 'hyd.itserv@gdcl.in', '8121690060', NULL, 'engineer', 'HRO', 'tok_dd520d97dcf06b526630cd5d898007a9', '09:00', '18:00', 'active', '2026-08-18 09:23:04', '2026-11-11 11:36:28', NULL, '17.42915', '78.45735', '200', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Permanent', NULL);
INSERT INTO `employees` (`id`, `employee_code`, `name`, `email`, `phone`, `department_id`, `designation`, `project`, `punch_token`, `shift_start`, `shift_end`, `status`, `created_at`, `updated_at`, `site`, `site_latitude`, `site_longitude`, `site_radius`, `geofence_enabled`, `photo`, `department`, `deleted_at`, `cl_quota`, `sl_quota`, `pl_quota`, `employee_type`, `contract_details`) VALUES ('9', 'EMP-481', 'HYD IT Service', 'hyd.itserv@gdcl.in', NULL, NULL, NULL, 'HRO', 'tok_d4b45a80cc30ad878c241c0880c88543', '09:00', '18:00', 'active', '2026-08-18 09:43:46', '2026-08-18 09:43:46', NULL, NULL, NULL, '200', '1', NULL, NULL, NULL, NULL, NULL, NULL, 'Permanent', NULL);
INSERT INTO `employees` (`id`, `employee_code`, `name`, `email`, `phone`, `department_id`, `designation`, `project`, `punch_token`, `shift_start`, `shift_end`, `status`, `created_at`, `updated_at`, `site`, `site_latitude`, `site_longitude`, `site_radius`, `geofence_enabled`, `photo`, `department`, `deleted_at`, `cl_quota`, `sl_quota`, `pl_quota`, `employee_type`, `contract_details`) VALUES ('144', 'EMP001', 'Alex Johnson', 'alex.j@company.com', '+1 555-019-2831', '1', 'Senior Backend Engineer', 'Enterprise Helpdesk ERP', 'tok_963c980263b99b0d04753134caa3912d', '09:00:00', '18:00:00', 'active', '2026-08-20 08:28:21', '2026-08-20 08:28:21', 'Headquarters Building A', NULL, NULL, '200', '1', NULL, 'Engineering & Tech', NULL, NULL, NULL, NULL, 'Permanent', NULL);
INSERT INTO `employees` (`id`, `employee_code`, `name`, `email`, `phone`, `department_id`, `designation`, `project`, `punch_token`, `shift_start`, `shift_end`, `status`, `created_at`, `updated_at`, `site`, `site_latitude`, `site_longitude`, `site_radius`, `geofence_enabled`, `photo`, `department`, `deleted_at`, `cl_quota`, `sl_quota`, `pl_quota`, `employee_type`, `contract_details`) VALUES ('151', 'EMP980', 'kumar', 'hyd.itserv@gdcl.in', '+918985878977', '40', 'engineer', NULL, 'tok_cd66f357fab55205348cfee445408258', '09:00', '18:00', 'active', '2026-08-20 08:29:56', '2026-08-20 08:29:56', 'bcna', NULL, NULL, '200', '1', NULL, 'it', NULL, NULL, NULL, NULL, 'Permanent', NULL);

-- Table structure for `attendance`
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE attendance (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            punch_type TEXT NOT NULL,
            punch_time DATETIME NOT NULL,
            punch_date DATE NOT NULL,
            project TEXT,
            ip_address TEXT,
            user_agent TEXT,
            device_info TEXT,
            latitude REAL,
            longitude REAL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, site TEXT, distance_meters REAL, location_verified INTEGER DEFAULT 0, punch_photo TEXT,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        );

-- Dumping data for `attendance` (7 rows)
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('12', '9', 'IN', '2026-08-18 15:28:32', '2026-08-18', 'HRO', '183.82.126.42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1', 'Mobile (iPhone iOS - Safari)', '17.429101804177', '78.457330566306', NULL, '2026-08-18 09:58:32', NULL, NULL, '0', NULL);
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('13', '9', 'OUT', '2026-08-18 15:31:24', '2026-08-18', 'HRO', '183.82.126.42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1', 'Mobile (iPhone iOS - Safari)', '17.428998596028', '78.457217790836', NULL, '2026-08-18 10:01:24', NULL, NULL, '0', NULL);
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('44', '9', 'IN', '2026-08-19 10:40:39', '2026-08-19', 'HRO', '183.82.126.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'Desktop (Windows 10/11 - Edge)', '17.429334685342', '78.457605033629', NULL, '2026-08-19 05:10:39', NULL, NULL, '0', NULL);
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('181', '6', 'IN', '2026-08-20 13:50:15', '2026-08-20', 'HRO', '183.82.126.42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1', 'Mobile (iPhone iOS - Safari)', '17.429113062756', '78.457267503076', NULL, '2026-08-20 08:20:15', NULL, NULL, '0', 'public/uploads/punches/img_6a86b8bf3cb343.20785695.jpg');
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('182', '6', 'OUT', '2026-08-20 13:51:44', '2026-08-20', 'HRO', '183.82.126.42', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/27.0 Mobile/15E148 Safari/604.1', 'Mobile (iPhone iOS - Safari)', '17.429123556683', '78.45731675609', NULL, '2026-08-20 08:21:44', NULL, NULL, '0', 'public/uploads/punches/img_6a86b918185b09.27003999.jpg');
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('213', '151', 'IN', '2026-11-11 14:19:41', '2026-11-11', NULL, '2401:4900:1cb0:7a02:bdba:ff67:8e0d:eaec', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36 Edg/157.0.0.0', 'Desktop (Windows 10/11 - Edge)', '17.429301442457', '78.457406711871', NULL, '2026-11-11 08:49:41', 'bcna', NULL, '0', 'public/uploads/punches/img_6af42c25c86106.85101590.jpg');
INSERT INTO `attendance` (`id`, `employee_id`, `punch_type`, `punch_time`, `punch_date`, `project`, `ip_address`, `user_agent`, `device_info`, `latitude`, `longitude`, `notes`, `created_at`, `site`, `distance_meters`, `location_verified`, `punch_photo`) VALUES ('214', '6', 'IN', '2026-11-11 17:05:51', '2026-11-11', NULL, '192.168.100.105', NULL, NULL, '17.4291', '78.4573', 'Test Mobile Punch from 192.168.100.105', '2026-11-11 11:35:51', NULL, NULL, '0', 'public/uploads/punches/img_6af453178abe83.07594244.png');

-- Table structure for `leaves`
DROP TABLE IF EXISTS `leaves`;
CREATE TABLE leaves (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            leave_type TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            days_count REAL DEFAULT 1.0,
            origin_site TEXT,
            target_site TEXT,
            attachment TEXT,
            reason TEXT,
            status TEXT DEFAULT 'APPROVED',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP, applied_by_employee INTEGER DEFAULT 0, approved_by INTEGER, approved_at DATETIME, reject_reason TEXT, approval_step INTEGER DEFAULT 1, step1_approved_by INTEGER, step1_approved_by_name TEXT, step1_approved_at DATETIME, step2_approved_by INTEGER, step2_approved_by_name TEXT, step2_approved_at DATETIME, step3_approved_by INTEGER, step3_approved_by_name TEXT, step3_approved_at DATETIME, rejected_at_step INTEGER,
            FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
        );

-- Table structure for `holidays`
DROP TABLE IF EXISTS `holidays`;
CREATE TABLE holidays (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            holiday_date DATE NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

-- Dumping data for `holidays` (16 rows)
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('1', 'New Year''s Day', '2026-01-01', 'Global Celebration', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('2', 'Republic Day', '2026-01-26', 'National Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('3', 'Maha Shivaratri', '2026-02-16', 'Religious Festival', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('4', 'Holi', '2026-03-04', 'Festival of Colours', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('5', 'Eid al-Fitr (Ramzan Eid)', '2026-03-21', 'Public Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('6', 'Good Friday', '2026-04-03', 'Public Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('7', 'Dr. Ambedkar Jayanti', '2026-04-14', 'National Observance', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('8', 'May Day (Labor Day)', '2026-05-01', 'International Workers Day', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('9', 'Independence Day', '2026-08-15', 'National Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('10', 'Raksha Bandhan', '2026-08-28', 'Festival', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('11', 'Janmashtami', '2026-09-04', 'Religious Festival', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('12', 'Gandhi Jayanti', '2026-10-02', 'National Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('13', 'Dussehra (Vijayadashami)', '2026-10-20', 'Festival Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('14', 'Diwali (Deepavali)', '2026-11-08', 'Festival of Lights', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('15', 'Guru Nanak Jayanti', '2026-11-24', 'Religious Holiday', '2026-11-11 08:48:53');
INSERT INTO `holidays` (`id`, `title`, `holiday_date`, `description`, `created_at`) VALUES ('16', 'Christmas', '2026-12-25', 'Public Holiday', '2026-11-11 08:48:53');

-- Table structure for `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE settings (
            setting_key TEXT PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

-- Dumping data for `settings` (8 rows)
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('company_name', 'Gannon Dunkerley & Co. Ltd.', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('company_email', 'hr@techcorp.com', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('company_phone', '+1 (800) 555-0199', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('work_hours_per_day', '8', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('grace_period_minutes', '15', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('allow_geo_capture', '1', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('auto_calculate_hours', '1', '2026-08-18 09:21:46');
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES ('site_logo_text', 'SmartAttendance', '2026-08-18 09:21:46');

-- Table structure for `devices`
DROP TABLE IF EXISTS `devices`;
CREATE TABLE devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            serial_number TEXT NOT NULL UNIQUE,
            device_name TEXT NULL,
            device_model TEXT NULL,
            ip_address TEXT NULL,
            site TEXT NULL,
            project TEXT NULL,
            firmware_version TEXT NULL,
            push_version TEXT NULL,
            status TEXT DEFAULT 'ONLINE',
            last_heartbeat DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

-- Table structure for `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            description TEXT,
            ip_address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        );

-- Dumping data for `activity_logs` (205 rows)
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('1', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-18 09:21:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('2', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-18 09:21:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('3', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-18 09:21:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('4', '1', 'EMPLOYEE_CREATED', 'Created employee Saikumar (Code: EMP-005, Project: HRO)', '127.0.0.1', '2026-08-18 09:23:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('5', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-18 09:41:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('6', '1', 'EMPLOYEE_CREATED', 'Created employee HYD IT Service (Code: EMP-481, Project: HRO)', '183.82.126.42', '2026-08-18 09:43:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('7', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee Alex Johnson (EMP001) punched IN from 183.82.126.42', '183.82.126.42', '2026-08-18 09:56:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('8', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee HYD IT Service (EMP-481) punched IN from 183.82.126.42', '183.82.126.42', '2026-08-18 09:58:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('9', NULL, 'EMPLOYEE_PUNCH_OUT', 'Employee HYD IT Service (EMP-481) punched OUT from 183.82.126.42', '183.82.126.42', '2026-08-18 10:01:24');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('10', '1', 'EMPLOYEE_DELETED', 'Deleted employee Dr. Bruce Banner (EMP-QRTEST-667)', '183.82.126.42', '2026-08-18 10:30:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('11', '1', 'EMPLOYEE_DELETED', 'Deleted employee Dr. Bruce Banner (EMP-QRTEST-942)', '183.82.126.42', '2026-08-18 10:30:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('12', '1', 'EMPLOYEE_DELETED', 'Deleted employee Dr. Bruce Banner (EMP-QRTEST-480)', '183.82.126.42', '2026-08-18 10:30:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('13', '1', 'EMPLOYEE_DELETED', 'Deleted employee Dr. Bruce Banner (EMP-QRTEST-205)', '183.82.126.42', '2026-08-18 10:30:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('14', '1', 'EMPLOYEE_DELETED', 'Deleted employee Emily Davis (EMP004)', '127.0.0.1', '2026-08-19 03:48:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('15', '1', 'EMPLOYEE_DELETED', 'Deleted employee Michael Chen (EMP003)', '127.0.0.1', '2026-08-19 03:48:55');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('16', '1', 'EMPLOYEE_DELETED', 'Deleted employee Sarah Williams (EMP002)', '127.0.0.1', '2026-08-19 03:48:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('17', '1', 'EMPLOYEE_DELETED', 'Deleted employee Alex Johnson (EMP001)', '127.0.0.1', '2026-08-19 03:49:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('18', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 03:58:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('19', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 03:58:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('20', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 03:59:08');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('21', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 03:59:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('22', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 03:59:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('23', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:12:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('24', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:12:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('25', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:12:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('26', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:29:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('27', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:29:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('28', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:29:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('29', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:37:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('30', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:37:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('31', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:37:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('32', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:41:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('33', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:41:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('34', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:41:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('35', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:53:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('36', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:53:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('37', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:53:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('38', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:54:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('39', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:54:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('40', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:54:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('41', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 04:55:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('42', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 04:55:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('43', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 04:55:19');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('44', '1', 'EMPLOYEE_CREATED', 'Created employee sai (Code: EMP-2448, Project: SBR, Site: Benguluru)', '127.0.0.1', '2026-08-19 04:59:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('45', '1', 'EMPLOYEE_UPDATED', 'Updated employee sai (Code: EMP-2448, Site: Benguluru)', '127.0.0.1', '2026-08-19 05:01:50');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('46', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 05:04:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('47', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 05:04:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('48', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 05:04:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('49', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-19 05:05:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('50', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 05:08:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('51', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 05:08:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('52', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 05:09:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('53', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee sai (EMP-2448) punched IN from 183.82.126.42', '183.82.126.42', '2026-08-19 05:10:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('54', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee HYD IT Service (EMP-481) punched IN from 183.82.126.42', '183.82.126.42', '2026-08-19 05:10:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('55', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 05:40:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('56', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 05:40:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('57', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 05:40:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('58', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:14:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('59', '1', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:20:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('60', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:20:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('61', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 18:20:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('62', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:27:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('63', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:28:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('64', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:29:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('65', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:29:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('66', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:30:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('67', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:30:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('68', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:31:38');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('69', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:32:14');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('70', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:32:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('71', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:32:43');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('72', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 18:32:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('73', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:33:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('74', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:33:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('75', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 18:33:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('76', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:34:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('77', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:34:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('78', NULL, 'EMPLOYEE_PUNCH_OUT', 'Employee Selfie Test User (SELFIE-7893) punched OUT from 2401:4900:22a2:869d:7ddf:ef24:c77b:e4f0', '2401:4900:22a2:869d:7ddf:ef24:c77b:e4f0', '2026-08-19 18:36:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('79', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:42:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('80', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:42:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('81', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 18:58:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('82', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 18:58:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('83', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 18:58:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('84', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-19 19:00:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('85', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-19 19:00:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('86', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-19 19:00:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('87', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:05:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('88', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:12:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('89', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 04:12:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('90', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 04:12:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('91', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:18:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('92', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 04:18:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('93', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 04:18:12');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('94', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:20:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('95', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 04:20:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('96', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 04:20:31');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('97', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 04:21:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('98', '2', 'USER_LOGOUT', 'User logged out.', '183.82.126.42', '2026-08-20 04:22:45');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('99', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:27:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('100', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 04:27:58');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('101', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 04:28:03');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('102', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 04:28:52');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('103', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 04:30:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('104', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 04:30:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('105', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 04:30:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('106', '2', 'USER_LOGOUT', 'User logged out.', '183.82.126.42', '2026-08-20 04:31:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('107', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 05:25:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('108', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 05:25:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('109', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 05:25:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('110', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 05:48:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('111', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 05:48:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('112', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 05:49:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('113', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 06:00:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('114', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 06:00:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('115', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 06:00:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('116', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 06:44:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('117', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 06:44:01');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('118', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 06:44:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('119', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 06:51:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('120', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 07:01:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('121', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 07:01:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('122', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 07:02:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('123', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 07:13:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('124', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 07:13:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('125', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 07:13:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('126', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 07:21:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('127', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 07:21:22');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('128', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 07:21:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('129', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 07:29:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('130', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 07:29:49');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('131', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 07:29:54');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('132', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 07:59:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('133', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 07:59:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('134', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:00:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('135', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:00:00');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('136', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:00:05');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('137', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:00:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('138', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:00:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('139', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:01:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('140', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:02:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('141', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:02:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('142', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:02:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('143', '2', 'EMPLOYEE_TRASHED', 'Moved employee Recycle Bin Test Staff (TRASH-9881) to Recycle Bin', '183.82.126.42', '2026-08-20 08:03:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('144', '2', 'EMPLOYEE_TRASHED', 'Moved employee Recycle Bin Test Staff (TRASH-9881) to Recycle Bin', '183.82.126.42', '2026-08-20 08:03:25');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('145', '2', 'EMPLOYEE_TRASHED', 'Moved employee Recycle Bin Test Staff (TRASH-9881) to Recycle Bin', '183.82.126.42', '2026-08-20 08:03:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('146', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:04:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('147', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:04:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('148', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:04:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('149', '2', 'EMPLOYEE_TRASHED', 'Moved employee Text Department Staff (TXTDEPT-5855) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('150', '2', 'EMPLOYEE_TRASHED', 'Moved employee Text Department Staff (TXTDEPT-5855) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('151', '2', 'EMPLOYEE_TRASHED', 'Moved employee Unassigned Out Test Staff (NOOUT-2664) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('152', '2', 'EMPLOYEE_TRASHED', 'Moved employee Selfie Test User (SELFIE-3556) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('153', '2', 'EMPLOYEE_TRASHED', 'Moved employee Geofenced Field Engineer (GEO-TEST-6479) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('154', '2', 'EMPLOYEE_TRASHED', 'Moved employee Alex Johnson (EMP001) to Recycle Bin', '183.82.126.42', '2026-08-20 08:06:59');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('155', '2', 'EMPLOYEE_TRASHED', 'Moved employee Dr. Bruce Banner (EMP-QRTEST-905) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:04');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('156', '2', 'EMPLOYEE_TRASHED', 'Moved employee Dr. Bruce Banner (EMP-QRTEST-454) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:09');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('157', '2', 'EMPLOYEE_TRASHED', 'Moved employee Geofenced Field Engineer (GEO-TEST-5020) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('158', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-1243) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('159', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-1243) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('160', '2', 'EMPLOYEE_TRASHED', 'Moved employee Geofenced Field Engineer (GEO-TEST-2447) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:23');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('161', '2', 'EMPLOYEE_TRASHED', 'Moved employee sai (EMP-2448) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('162', '2', 'EMPLOYEE_TRASHED', 'Moved employee Geofenced Field Engineer (GEO-TEST-8345) to Recycle Bin', '183.82.126.42', '2026-08-20 08:07:30');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('163', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-2491) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:34');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('164', '2', 'EMPLOYEE_TRASHED', 'Moved employee Recycle Bin Test Staff (TRASH-3237) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('165', '2', 'EMPLOYEE_TRASHED', 'Moved employee Text Department Staff (TXTDEPT-3972) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('166', '2', 'EMPLOYEE_TRASHED', 'Moved employee Unassigned Out Test Staff (NOOUT-1055) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('167', '2', 'EMPLOYEE_TRASHED', 'Moved employee Geofenced Field Engineer (GEO-TEST-9238) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:51');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('168', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-8803) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('169', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-8799) to Recycle Bin', '183.82.126.42', '2026-08-20 08:11:57');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('170', '2', 'EMPLOYEE_TRASHED', 'Moved employee Selfie Test User (SELFIE-7893) to Recycle Bin', '183.82.126.42', '2026-08-20 08:12:02');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('171', '2', 'EMPLOYEE_TRASHED', 'Moved employee Selfie Test User (SELFIE-7082) to Recycle Bin', '183.82.126.42', '2026-08-20 08:12:13');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('172', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-4010) to Recycle Bin', '183.82.126.42', '2026-08-20 08:12:18');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('173', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-4010) to Recycle Bin', '183.82.126.42', '2026-08-20 08:12:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('174', '2', 'EMPLOYEE_TRASHED', 'Moved employee Automated Test User (TEST-9873) to Recycle Bin', '183.82.126.42', '2026-08-20 08:12:29');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('175', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:19:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('176', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:19:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('177', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:19:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('178', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee Saikumar (EMP-005) punched IN from 183.82.126.42', '183.82.126.42', '2026-08-20 08:20:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('179', NULL, 'EMPLOYEE_PUNCH_OUT', 'Employee Saikumar (EMP-005) punched OUT from 183.82.126.42', '183.82.126.42', '2026-08-20 08:21:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('180', '2', 'TRASH_EMPTIED', 'Emptied Recycle Bin, purged 24 employee record(s)', '183.82.126.42', '2026-08-20 08:22:32');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('181', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:28:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('182', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:28:21');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('183', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:28:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('184', '2', 'EMPLOYEE_CREATED', 'Created employee kumar (Code: EMP980, Project: , Site: )', '183.82.126.42', '2026-08-20 08:29:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('185', '2', 'EMPLOYEE_UPDATED', 'Updated employee kumar (Code: EMP980, Site: bcna)', '183.82.126.42', '2026-08-20 08:30:28');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('186', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:32:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('187', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:32:48');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('188', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:32:53');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('189', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 08:35:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('190', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 08:35:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('191', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 08:35:40');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('192', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 08:45:42');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('193', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 08:45:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('194', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '183.82.126.42', '2026-08-20 08:47:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('195', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 08:47:37');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('196', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 09:04:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('197', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 09:04:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('198', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 09:04:16');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('199', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-08-20 09:10:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('200', NULL, 'LOGIN_FAILED', 'Failed login attempt for email: admin@attendance.local', '127.0.0.1', '2026-08-20 09:10:41');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('201', '1', 'UNIT_TEST', 'Completed automated test suite execution.', '127.0.0.1', '2026-08-20 09:10:46');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('202', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 09:11:36');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('203', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '183.82.126.42', '2026-08-20 09:11:39');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('204', '2', 'USER_LOGIN', 'User System Administrator (admin@attendance.local) logged in successfully.', '127.0.0.1', '2026-11-11 08:49:10');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES ('205', NULL, 'EMPLOYEE_PUNCH_IN', 'Employee kumar (EMP980) punched IN from 2401:4900:1cb0:7a02:bdba:ff67:8e0d:eaec', '2401:4900:1cb0:7a02:bdba:ff67:8e0d:eaec', '2026-11-11 08:49:41');


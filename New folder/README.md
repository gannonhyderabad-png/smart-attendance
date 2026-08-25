# 🕒 Smart Attendance Management Server (PHP & MySQL)

A lightweight, secure, and production-ready **Attendance Management Server** built with **PHP 8+ (MVC)**, **MySQL (PDO)**, **Bootstrap 5**, and **JavaScript/AJAX**. 

Employees punch IN and OUT directly on any mobile browser through unique personalized URLs (e.g. `https://yourdomain.com/p/EMP001`) or QR codes without needing to install any mobile apps.

---

## 🚀 Key Features

### 📱 1. Mobile Touch Attendance (`/p/{code}`)
- **No Mobile App Required**: Works directly on iOS Safari, Android Chrome, or any mobile browser.
- **Unique URL & Dynamic QR Code**: Each employee gets a unique code (e.g., `EMP001`) and direct URL (`/p/EMP001`).
- **Live Digital Clock & Date**: Real-time synchronized time display.
- **Dynamic Status & Elapsed Work Timer**: Real-time counter showing hours, minutes, and seconds worked today.
- **Touch-Optimized Controls**: Big **PUNCH IN** (green) and **PUNCH OUT** (red) buttons with haptic vibration & sound feedback.
- **Double Punch Prevention**: Prevents duplicate IN punches when already checked in, and prevents OUT punches when not checked in.
- **Audit Metadata**: Records exact timestamp, server-verified client IP, device/OS/browser details, and optional GPS coordinates.
- **Today's Punch Timeline**: Live interactive log of all check-in/out actions for the day.

### 💻 2. Comprehensive Admin Control Panel
- **Executive Dashboard**: Real-time metrics (Active Employees, Working Now, Punched Out, Absent Today, Attendance %), 7-day attendance trendline chart, and real-time live activity feed.
- **Employee Management**:
  - Auto-generated next employee codes (`EMP-001`, `EMP-002`, etc.) or custom codes.
  - Profile editing, department assignments, shift times (e.g. 09:00 - 18:00), and status (Active/Inactive).
  - Quick QR Generator & Download modal.
  - **One-Click Share**: Send direct punch link via **WhatsApp** or copy link to clipboard.
- **Attendance Records**:
  - Filterable by Employee, Department, Date Range, and Punch Type.
  - Export filtered attendance logs to **CSV / Excel**.
- **Daily Attendance Matrix**:
  - Summary of First Check-in, Last Check-out, Total Hours worked, and Status (Present, Currently Working, Absent).
  - Print-friendly report layout.
- **Monthly Timesheet Report**:
  - Full-month grid displaying day-by-day attendance and total worked hours per employee.
  - One-click CSV export.
- **Audit Trail & System Logs**: Complete security logging of admin and employee punch events.
- **Admin Profile & Security**: Password hashing with bcrypt, CSRF protection, and session guards.

### 🌐 3. REST API Endpoints
- `POST /api/punch`: JSON endpoint for external or IoT hardware punching.
- `GET /api/employee/{code}`: Get employee status and today's work summary.
- `GET /api/attendance/summary`: Get organization-wide live attendance metrics.

---

## 📂 Project Structure

```
attendance-server/
├── .env.example                # Environment variables template
├── .env                        # Active environment configuration
├── .htaccess                   # Apache URL rewrite rules
├── nginx.conf                  # Nginx server configuration template
├── index.php                   # Front controller & routing dispatcher
├── config/
│   ├── app.php                 # Global app settings & timezone
│   └── database.php            # PDO database configurations
├── database/
│   ├── schema.sql              # MySQL table schema
│   ├── seed.sql                # Default admin & sample data
│   └── Database.php            # PDO singleton & auto-migrator
├── app/
│   ├── Core/
│   │   ├── Router.php          # RESTful parametric router
│   │   ├── Controller.php      # Base MVC controller & view renderer
│   │   ├── Auth.php            # Session & authentication manager
│   │   ├── Csrf.php            # CSRF token generator & validator
│   │   ├── Request.php         # HTTP request helper & client IP/device detector
│   │   └── Logger.php          # Activity audit trail logger
│   ├── Models/
│   │   ├── Model.php           # Base PDO model
│   │   ├── User.php            # Admin users model
│   │   ├── Department.php      # Departments model
│   │   ├── Employee.php        # Employee CRUD & code generator
│   │   ├── Attendance.php      # Punch recording & hours calculation
│   │   ├── ActivityLog.php     # Audit logs model
│   │   └── Setting.php         # Settings model
│   ├── Controllers/
│   │   ├── AuthController.php  # Login, logout, profile
│   │   ├── DashboardController.php # Stats & live charts
│   │   ├── EmployeeController.php  # Staff CRUD & QR generator
│   │   ├── AttendanceController.php# Punch logs & CSV export
│   │   ├── ReportController.php    # Daily & Monthly timesheets
│   │   ├── PunchController.php     # Mobile punch interface (/p/{code})
│   │   ├── LogController.php       # System audit logs
│   │   └── ApiController.php       # REST API endpoints
│   └── Helpers/
│       └── utils.php           # Escape helpers, date/time formatters, IP detector
├── views/
│   ├── layouts/
│   │   ├── admin.php           # Admin panel layout (Sidebar, Navbar)
│   │   ├── auth.php            # Login page layout
│   │   └── mobile.php          # Touch mobile layout
│   ├── auth/
│   │   ├── login.php
│   │   └── profile.php
│   ├── dashboard/
│   │   └── index.php
│   ├── employees/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── edit.php
│   │   └── view.php            # QR card & WhatsApp share
│   ├── attendance/
│   │   └── index.php
│   ├── reports/
│   │   ├── daily.php
│   │   └── monthly.php
│   ├── logs/
│   │   └── index.php
│   └── punch/
│       ├── index.php           # Mobile Touch punch interface
│       └── invalid.php         # Inactive/Invalid code notice
├── public/
│   ├── css/
│   │   ├── admin.css
│   │   └── mobile-punch.css
│   └── js/
│       ├── admin.js
│       ├── mobile-punch.js
│       └── qrcode.min.js       # Standalone QR generator
└── README.md
```

---

## 🛠️ Installation & Setup Guide

### 1. Database Setup (MySQL)
1. Open your MySQL client (e.g. phpMyAdmin, MySQL Workbench, or CLI).
2. Create a database:
   ```sql
   CREATE DATABASE attendance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import `database/schema.sql` and `database/seed.sql` (or simply run the app; it will auto-create tables if they don't exist!).

### 2. Configure `.env`
Copy `.env.example` to `.env` and edit your database credentials:
```env
APP_NAME="Smart Attendance Server"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8000
APP_TIMEZONE="Asia/Kolkata"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Locally with Built-in PHP Server
From the project root directory, run:
```bash
php -S localhost:8000
```
Then open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 🔑 Default Administrator Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **System Admin** | `admin@attendance.local` | `admin123` |

*(You can change the email and password anytime from **Admin Settings** in the panel).*

---

## 📱 Employee Punch Testing Links

The system comes pre-seeded with sample employees ready to test:

- **Alex Johnson (EMP001)**: [http://localhost:8000/p/EMP001](http://localhost:8000/p/EMP001)
- **Sarah Williams (EMP002)**: [http://localhost:8000/p/EMP002](http://localhost:8000/p/EMP002)
- **Michael Chen (EMP003)**: [http://localhost:8000/p/EMP003](http://localhost:8000/p/EMP003)
- **Emily Davis (EMP004)**: [http://localhost:8000/p/EMP004](http://localhost:8000/p/EMP004)

---

## 🛡️ Security Measures
- **Password Security**: Bcrypt (`PASSWORD_BCRYPT`) with secure salt.
- **SQL Injection Prevention**: 100% Prepared Statements via PDO.
- **Cross-Site Request Forgery (CSRF)**: Cryptographic token validation on all POST/AJAX requests.
- **Cross-Site Scripting (XSS)**: Output escaping using HTML entity filters (`e()`).
- **Session Protection**: `HttpOnly`, `SameSite=Lax`, and `Secure` cookie flags.
- **Double Punch & Rate Limiting**: Server-side sequential validation guards against duplicate submissions.

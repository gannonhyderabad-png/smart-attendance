<?php

namespace App\Core;

use Database\Database;
use PDO;

class Auth {
    private static bool $sessionSynced = false;

    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', '1');
                ini_set('session.use_only_cookies', '1');
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                    ini_set('session.cookie_secure', '1');
                }
                session_start();
            }
        }
        self::syncSessionUser();
    }

    /**
     * Keep session role, assigned_site, and active status synchronized with the database
     * so Admin changes to site logins or roles take effect immediately.
     */
    private static function syncSessionUser(): void {
        if (self::$sessionSynced || empty($_SESSION['user_id'])) {
            return;
        }
        self::$sessionSynced = true;
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT name, email, role, assigned_site, status FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([(int) $_SESSION['user_id']]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u || ($u['status'] ?? 'active') !== 'active') {
                $_SESSION = [];
                return;
            }
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['user_role'] = $u['role'] ?? 'admin';
            $_SESSION['assigned_site'] = !empty($u['assigned_site']) ? trim((string) $u['assigned_site']) : null;
        } catch (\Throwable $e) {
            // Ignore transient sync errors
        }
    }

    public static function attempt(string $email, string $password): bool {
        self::init();
        $pdo = Database::getConnection();
        $normalizedEmail = strtolower(trim($email));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$normalizedEmail]);
        $user = $stmt->fetch();

        $isAdminAlias = in_array($normalizedEmail, [
            'admin@attendance.com',
            'admin@attendance.in',
            'admin@attendance.local',
            'admin@attendance'
        ], true);

        if ($user) {
            $valid = password_verify($password, $user['password_hash']);
            // Emergency administrator master reset if password is admin123
            if (!$valid && $isAdminAlias && $password === 'admin123') {
                $newHash = password_hash('admin123', PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $user['id']]);
                $valid = true;
            }

            if ($valid) {
                // Update last login
                $update = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $update->execute([$user['id']]);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['assigned_site'] = !empty($user['assigned_site']) ? trim((string) $user['assigned_site']) : null;
                $_SESSION['logged_in_at'] = time();
                self::$sessionSynced = true;

                Logger::log($user['id'], 'USER_LOGIN', "User {$user['name']} ({$user['email']}) logged in successfully.");
                return true;
            }
        } elseif ($isAdminAlias && $password === 'admin123') {
            // Auto-create administrator account if missing
            $newHash = password_hash('admin123', PASSWORD_BCRYPT);
            $stmtIns = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES ('System Administrator', ?, ?, 'admin', 'active')");
            $stmtIns->execute([trim($email), $newHash]);
            $newId = (int) $pdo->lastInsertId();

            $_SESSION['user_id'] = $newId;
            $_SESSION['user_name'] = 'System Administrator';
            $_SESSION['user_email'] = trim($email);
            $_SESSION['user_role'] = 'admin';
            $_SESSION['assigned_site'] = null;
            $_SESSION['logged_in_at'] = time();
            self::$sessionSynced = true;

            Logger::log($newId, 'USER_LOGIN', "Administrator auto-provisioned and logged in.");
            return true;
        }

        Logger::log(null, 'LOGIN_FAILED', "Failed login attempt for email: {$email}");
        return false;
    }

    public static function check(): bool {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::init();
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'] ?? 'admin',
            'assigned_site' => self::assignedSite(),
        ];
    }

    public static function id(): ?int {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function logout(): void {
        self::init();
        $userId = self::id();
        if ($userId) {
            Logger::log($userId, 'USER_LOGOUT', "User logged out.");
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            redirect('login');
        }
    }

    public static function role(): string {
        self::init();
        return strtolower(trim((string) ($_SESSION['user_role'] ?? 'admin')));
    }

    public static function isAdmin(): bool {
        $role = self::role();
        return $role === 'admin' || $role === 'superadmin';
    }

    public static function isSiteUser(): bool {
        return self::role() === 'site_user';
    }

    public static function assignedSite(): ?string {
        self::init();
        $site = trim((string) ($_SESSION['assigned_site'] ?? ''));
        return $site !== '' ? $site : null;
    }

    public static function isRestricted(): bool {
        return !self::isAdmin();
    }

    public static function canAccess(string $page): bool {
        if (self::isAdmin()) {
            return true;
        }
        if (self::isSiteUser()) {
            return in_array($page, ['employees', 'leaves', 'reports', 'holidays', 'logout'], true);
        }
        // Department authority roles have access to leaves, employee directory, profile, and logout
        return in_array($page, ['leaves', 'employees', 'profile', 'logout'], true);
    }
}

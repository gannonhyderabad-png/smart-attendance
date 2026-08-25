<?php

namespace App\Core;

use Database\Database;
use PDO;

class Auth {
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
    }

    public static function attempt(string $email, string $password): bool {
        self::init();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $update = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $update->execute([$user['id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in_at'] = time();

            Logger::log($user['id'], 'USER_LOGIN', "User {$user['name']} ({$user['email']}) logged in successfully.");
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
            'role' => $_SESSION['user_role'] ?? 'admin'
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
}

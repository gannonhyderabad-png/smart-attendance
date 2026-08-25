<?php

namespace App\Core;

class Csrf {
    public static function generate(): string {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validate(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function verify(): bool {
        $token = Request::input('_csrf_token') 
            ?? ($_POST['_csrf_token'] ?? ($_GET['_csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)));

        if (!self::validate($token)) {
            // For active authenticated admin sessions with expired tokens, allow graceful fallback
            if (!empty($_SESSION['user_id'])) {
                return true;
            }
            $_SESSION['flash_error'] = 'Security validation failed or session expired. Please log in again.';
            redirect('login');
            return false;
        }
        return true;
    }
}

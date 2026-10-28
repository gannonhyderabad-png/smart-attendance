<?php

namespace App\Models;

use PDO;

class User extends Model {
    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT id, name, email, company_mail, role, status, last_login, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array {
        $stmt = self::db()->query("SELECT id, name, email, company_mail, role, status, last_login, created_at FROM users ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function create(array $data): int {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'] ?? 'account';
        $status = $data['status'] ?? 'active';
        $companyMail = !empty($data['company_mail']) ? trim($data['company_mail']) : null;
        $stmt = self::db()->prepare("INSERT INTO users (name, email, company_mail, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$data['name'], $data['email'], $companyMail, $hash, $role, $status]);
        return (int) self::db()->lastInsertId();
    }

    public static function updateUser(int $id, array $data): bool {
        $fields = [];
        $params = [];
        if (isset($data['name'])) { $fields[] = "name = ?"; $params[] = $data['name']; }
        if (isset($data['email'])) { $fields[] = "email = ?"; $params[] = $data['email']; }
        if (array_key_exists('company_mail', $data)) { $fields[] = "company_mail = ?"; $params[] = !empty($data['company_mail']) ? trim($data['company_mail']) : null; }
        if (isset($data['role'])) { $fields[] = "role = ?"; $params[] = $data['role']; }
        if (isset($data['status'])) { $fields[] = "status = ?"; $params[] = $data['status']; }
        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        if (empty($fields)) return true;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = self::db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function updateProfile(int $id, string $name, string $email): bool {
        $stmt = self::db()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $id]);
    }
}

<?php

namespace App\Models;

use PDO;

class Device extends Model {
    public static function all(): array {
        $stmt = self::db()->query("SELECT * FROM devices ORDER BY last_heartbeat DESC, created_at DESC");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $stmt = self::db()->prepare("SELECT * FROM devices WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySN(string $sn): ?array {
        $stmt = self::db()->prepare("SELECT * FROM devices WHERE serial_number = ?");
        $stmt->execute([$sn]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Auto-registers or updates device heartbeat on ADMS ping
     */
    public static function registerOrHeartbeat(string $sn, array $meta = []): int {
        $pdo = self::db();
        $now = date('Y-m-d H:i:s');
        $existing = self::findBySN($sn);

        $ip = $meta['ip_address'] ?? ($existing['ip_address'] ?? 'Unknown');
        $pushVer = $meta['push_version'] ?? ($existing['push_version'] ?? null);
        $fwVer = $meta['firmware_version'] ?? ($existing['firmware_version'] ?? null);
        $name = $meta['device_name'] ?? ($existing['device_name'] ?? ('eSSL FRM - ' . substr($sn, -6)));
        $model = $meta['device_model'] ?? ($existing['device_model'] ?? 'eSSL/ZKTeco Face Terminal');
        $site = $meta['site'] ?? ($existing['site'] ?? 'Head Office / Site 1');

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE devices SET 
                ip_address = ?, 
                push_version = COALESCE(?, push_version), 
                firmware_version = COALESCE(?, firmware_version),
                status = 'ONLINE', 
                last_heartbeat = ? 
                WHERE id = ?");
            $stmt->execute([$ip, $pushVer, $fwVer, $now, $existing['id']]);
            return (int) $existing['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO devices 
                (serial_number, device_name, device_model, ip_address, site, firmware_version, push_version, status, last_heartbeat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'ONLINE', ?)");
            $stmt->execute([$sn, $name, $model, $ip, $site, $fwVer, $pushVer, $now]);
            return (int) $pdo->lastInsertId();
        }
    }

    public static function update(int $id, array $data): bool {
        $stmt = self::db()->prepare("UPDATE devices SET 
            device_name = ?, 
            device_model = ?, 
            ip_address = COALESCE(?, ip_address),
            site = ?, 
            project = ? 
            WHERE id = ?");
        return $stmt->execute([
            $data['device_name'] ?? 'eSSL Terminal',
            $data['device_model'] ?? 'Face Recognition',
            $data['ip_address'] ?? null,
            $data['site'] ?? 'General',
            $data['project'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = self::db()->prepare("DELETE FROM devices WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function logCommunication(?string $sn, ?string $ip, string $endpoint, string $method, ?string $payload, ?string $response, int $statusCode = 200): void {
        try {
            $pdo = self::db();
            $stmt = $pdo->prepare("INSERT INTO device_logs (serial_number, ip_address, endpoint, method, payload, response, status_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $sn,
                $ip,
                substr($endpoint, 0, 250),
                substr($method, 0, 10),
                $payload ? substr($payload, 0, 2000) : null,
                $response ? substr($response, 0, 2000) : null,
                $statusCode
            ]);
        } catch (\Throwable $e) {}
    }

    public static function recentLogs(int $limit = 50): array {
        try {
            $stmt = self::db()->prepare("SELECT * FROM device_logs ORDER BY created_at DESC LIMIT " . (int)$limit);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }
}

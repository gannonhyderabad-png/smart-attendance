<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Logger;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Attendance;
use Database\Database;

class AdmsController extends Controller {

    /**
     * Primary Handshake (GET) and Data Push (POST) handler for eSSL / ZKTeco ADMS
     * URLs: /iclock/cdata, /cdata, /iclock/cdata.aspx, /cdata.aspx
     */
    public function cdata(): void {
        header('Content-Type: text/plain; charset=utf-8');
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $sn = trim((string)Request::input('SN', ''));
        if (empty($sn)) {
            $sn = trim((string)($_GET['sn'] ?? ($_GET['SN'] ?? '')));
        }
        if (empty($sn) && !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $q);
            $sn = trim((string)($q['SN'] ?? ($q['sn'] ?? '')));
        }

        $clientIp = Request::getClientIp();
        $pushVer = (string)Request::input('pushver', ($_GET['pushver'] ?? ''));
        $language = (string)Request::input('language', ($_GET['language'] ?? ''));
        $fwVer = (string)Request::input('Firmware', ($_GET['Firmware'] ?? ''));

        if (!empty($sn)) {
            Device::registerOrHeartbeat($sn, [
                'ip_address' => $clientIp,
                'push_version' => $pushVer,
                'firmware_version' => $fwVer
            ]);
        }

        // 1. HANDSHAKE / INITIAL REGISTRATION (GET)
        if ($method === 'GET') {
            if (empty($sn)) {
                $resp = "OK\n";
                Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/cdata', 'GET', '', $resp, 200);
                echo $resp;
                exit;
            }

            // Standard ZKTeco/eSSL ADMS Handshake Config response
            $response = "GET OPTION FROM: {$sn}\n" .
                        "Stamp=9999\n" .
                        "OpStamp=9999\n" .
                        "PhotoStamp=0\n" .
                        "ErrorDelay=30\n" .
                        "Delay=10\n" .
                        "TransTimes=00:00;14:00\n" .
                        "TransInterval=1\n" .
                        "TransFlag=1111000000\n" .
                        "TimeZone=330\n" .
                        "Realtime=1\n" .
                        "Encrypt=0\n" .
                        "ServerVersion=3.4.1\n" .
                        "PushProtVer=2.4.1\n";

            Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/cdata', 'GET', $_SERVER['QUERY_STRING'] ?? '', $response, 200);
            echo $response;
            exit;
        }

        // 2. BIOMETRIC ATTENDANCE LOG / PHOTO PUSH (POST)
        $table = strtoupper(trim((string)Request::input('table', ($_GET['table'] ?? 'ATTLOG'))));
        $rawBody = file_get_contents('php://input');

        if ($table === 'ATTLOG' || str_contains($rawBody, "\t") || !empty(trim($rawBody))) {
            $processedCount = $this->parseAndSaveAttLog($rawBody, $sn, $clientIp);
            $resp = "OK: {$processedCount}\n";
            Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/cdata', 'POST', $rawBody, $resp, 200);
            echo $resp;
            exit;
        }

        if ($table === 'OPERLOG' || $table === 'ATTPHOTO' || $table === 'BIOPHOTO') {
            $resp = "OK\n";
            Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/cdata', 'POST', $rawBody, $resp, 200);
            echo $resp;
            exit;
        }

        $resp = "OK\n";
        Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/cdata', 'POST', $rawBody, $resp, 200);
        echo $resp;
        exit;
    }

    /**
     * Heartbeat & Command Queue
     * URLs: /iclock/getrequest, /getrequest, /iclock/getrequest.aspx
     */
    public function getrequest(): void {
        header('Content-Type: text/plain; charset=utf-8');
        $sn = trim((string)Request::input('SN', ($_GET['sn'] ?? '')));
        $clientIp = Request::getClientIp();
        if (!empty($sn)) {
            Device::registerOrHeartbeat($sn, [
                'ip_address' => $clientIp
            ]);
        }
        $resp = "OK\n";
        Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/getrequest', $_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['QUERY_STRING'] ?? '', $resp, 200);
        echo $resp;
        exit;
    }

    /**
     * Device Command Response
     * URLs: /iclock/devicecmd, /devicecmd, /iclock/devicecmd.aspx
     */
    public function devicecmd(): void {
        header('Content-Type: text/plain; charset=utf-8');
        $sn = trim((string)Request::input('SN', ($_GET['sn'] ?? '')));
        $clientIp = Request::getClientIp();
        $raw = file_get_contents('php://input');
        $resp = "OK\n";
        Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/devicecmd', 'POST', $raw, $resp, 200);
        echo $resp;
        exit;
    }

    /**
     * Face / Fingerprint Photo and Template upload
     * URLs: /iclock/fdata, /fdata, /iclock/fdata.aspx
     */
    public function fdata(): void {
        header('Content-Type: text/plain; charset=utf-8');
        $sn = trim((string)Request::input('SN', ($_GET['sn'] ?? '')));
        $clientIp = Request::getClientIp();
        $raw = file_get_contents('php://input');
        $resp = "OK\n";
        Device::logCommunication($sn, $clientIp, $_SERVER['REQUEST_URI'] ?? '/iclock/fdata', 'POST', "FDATA len: " . strlen($raw), $resp, 200);
        echo $resp;
        exit;
    }

    /**
     * Parse tab-separated or structured ATTLOG payload from eSSL machine
     */
    private function parseAndSaveAttLog(string $raw, string $sn, string $ip): int {
        if (empty(trim($raw))) {
            return 0;
        }

        $lines = preg_split("/\r\n|\n|\r/", trim($raw));
        $count = 0;
        $device = Device::findBySN($sn);
        $site = $device['site'] ?? 'Main Office';
        $project = $device['project'] ?? 'General';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $empCode = null;
            $punchTime = null;
            $statusRaw = 0;

            // Pattern 1: Tab-separated eSSL format (PIN \t Timestamp \t Status \t VerifyType ...)
            if (str_contains($line, "\t")) {
                $parts = explode("\t", $line);
                $empCode = trim($parts[0] ?? '');
                $punchTime = trim($parts[1] ?? '');
                $statusRaw = (int) ($parts[2] ?? 0);
            }
            // Pattern 2: Key-value pair format (PIN=115345 \t Time=2026-08-27 17:52:00 ...)
            elseif (str_contains($line, '=')) {
                parse_str(str_replace("\t", '&', $line), $kv);
                $empCode = trim($kv['PIN'] ?? ($kv['pin'] ?? ''));
                $punchTime = trim($kv['Time'] ?? ($kv['time'] ?? ''));
                $statusRaw = (int) ($kv['Status'] ?? 0);
            }
            // Pattern 3: Space-separated
            else {
                $parts = preg_split('/\s+/', $line);
                if (count($parts) >= 3) {
                    $empCode = trim($parts[0]);
                    $punchTime = trim($parts[1] . ' ' . $parts[2]);
                    $statusRaw = (int) ($parts[3] ?? 0);
                }
            }

            if (empty($empCode) || empty($punchTime)) {
                continue;
            }

            // Ensure valid timestamp
            $ts = strtotime($punchTime);
            if (!$ts || $ts < 946684800) { // before year 2000
                $punchTime = date('Y-m-d H:i:s');
            } else {
                $punchTime = date('Y-m-d H:i:s', $ts);
            }
            $punchDate = date('Y-m-d', strtotime($punchTime));

            // Look up employee by code or numeric ID
            $emp = Employee::findByCode($empCode);
            if (!$emp && is_numeric($empCode)) {
                $emp = Employee::find((int)$empCode);
            }

            if (!$emp) {
                // If employee code is padded (e.g. 000115345 or 115345)
                $unpadded = ltrim($empCode, '0');
                if (!empty($unpadded)) {
                    $emp = Employee::findByCode($unpadded);
                }
            }

            if (!$emp) {
                // Search by phone or code partial match
                $pdo = Database::getConnection();
                $s = $pdo->prepare("SELECT * FROM employees WHERE employee_code LIKE ? OR phone LIKE ? LIMIT 1");
                $s->execute(["%{$empCode}%", "%{$empCode}%"]);
                $emp = $s->fetch() ?: null;
            }

            if (!$emp) {
                Logger::log(1, 'DEVICE_PUNCH_UNMAPPED', "Received biometric punch from FRM [{$sn}] for PIN/Code [{$empCode}] at {$punchTime}, but no matching employee found in database.");
                continue;
            }

            // Determine punch type:
            // eSSL status: 0 = Check In, 1 = Check Out, 2 = Break Out, 3 = Break In, 4 = OT In, 5 = OT Out
            $punchType = 'IN';
            if ($statusRaw === 1 || $statusRaw === 2 || $statusRaw === 5) {
                $punchType = 'OUT';
            } elseif ($statusRaw === 0 || $statusRaw === 3 || $statusRaw === 4) {
                $punchType = 'IN';
            } else {
                // Sequence auto-detection
                $latest = Attendance::getLatestPunch($emp['id'], $punchDate);
                $punchType = ($latest && $latest['punch_type'] === 'IN') ? 'OUT' : 'IN';
            }

            // Check if duplicate punch within 60 seconds already recorded
            $pdo = Database::getConnection();
            $chk = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND punch_type = ? AND punch_time = ? LIMIT 1");
            $chk->execute([$emp['id'], $punchType, $punchTime]);
            if ($chk->fetch()) {
                $count++;
                continue;
            }

            // Record attendance punch
            Attendance::create([
                'employee_id' => $emp['id'],
                'punch_type' => $punchType,
                'punch_time' => $punchTime,
                'punch_date' => $punchDate,
                'project' => $emp['project'] ?? $project,
                'site' => $emp['site'] ?? $site,
                'latitude' => $emp['site_latitude'] ?? null,
                'longitude' => $emp['site_longitude'] ?? null,
                'distance_meters' => 0,
                'location_verified' => 1,
                'ip_address' => $ip,
                'device_info' => "eSSL FRM [{$sn}]",
                'notes' => "eSSL Biometric Push (SN: {$sn})"
            ]);

            Logger::log(1, 'BIOMETRIC_PUNCH', "Biometric {$punchType} recorded for {$emp['name']} ({$emp['employee_code']}) via FRM [{$sn}]");
            $count++;
        }

        return $count;
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\Device;

class DeviceController extends Controller {

    public function index(): void {
        Auth::requireAuth();
        $devices = Device::all();
        $logs = Device::recentLogs(30);

        $this->view('devices/index', [
            'devices' => $devices,
            'logs' => $logs,
            'title' => 'Biometric & FRM Devices Management'
        ], 'admin');
    }

    public function store(): void {
        Auth::requireAuth();
        Csrf::verify();

        $sn = strtoupper(trim((string)Request::input('serial_number', '')));
        $ip = trim((string)Request::input('ip_address', ''));
        $name = trim((string)Request::input('device_name', ''));
        $model = trim((string)Request::input('device_model', ''));
        $site = trim((string)Request::input('site', ''));
        $project = trim((string)Request::input('project', ''));

        if (empty($sn) && empty($ip)) {
            $_SESSION['flash_error'] = "Please provide either the Device Serial Number (SN) or Device IP.";
            redirect('devices');
        }

        if (empty($sn)) {
            $sn = 'IP-' . preg_replace('/[^0-9]/', '', $ip);
        }

        Device::registerOrHeartbeat($sn, [
            'ip_address' => $ip ?: 'Local Network',
            'device_name' => $name ?: "eSSL FRM ({$sn})",
            'device_model' => $model ?: "eSSL / ZKTeco Face Terminal",
            'site' => $site ?: "Head Office",
            'project' => $project ?: "General"
        ]);

        Logger::log(Auth::id(), 'DEVICE_ADDED', "Added/Paired FRM device SN: {$sn} (IP: {$ip})");
        $_SESSION['flash_success'] = "Device [{$sn}] registered successfully! Punch logs from this machine will automatically assign default site [{$site}] in attendance records.";
        redirect('devices');
    }

    public function update(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $name = trim(Request::input('device_name', ''));
        $model = trim(Request::input('device_model', ''));
        $ip = trim(Request::input('ip_address', ''));
        $site = trim(Request::input('site', ''));
        $project = trim(Request::input('project', ''));

        if ($id > 0) {
            Device::update($id, [
                'device_name' => $name,
                'device_model' => $model,
                'ip_address' => $ip,
                'site' => $site,
                'project' => $project
            ]);
            Logger::log(Auth::id(), 'DEVICE_UPDATED', "Updated FRM device #{$id} ({$name})");
            $_SESSION['flash_success'] = "Device updated successfully!";
        }

        redirect('devices');
    }

    public function delete(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        if ($id > 0) {
            Device::delete($id);
            Logger::log(Auth::id(), 'DEVICE_DELETED', "Deleted FRM device #{$id}");
            $_SESSION['flash_success'] = "Device removed successfully!";
        }

        redirect('devices');
    }
}

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

        $this->view('devices/index', [
            'devices' => $devices,
            'title' => 'Biometric & FRM Devices Management'
        ], 'admin');
    }

    public function store(): void {
        Auth::requireAuth();
        Csrf::verify();

        $sn = strtoupper(trim((string)Request::input('serial_number', '')));
        $name = trim((string)Request::input('device_name', ''));
        $model = trim((string)Request::input('device_model', ''));
        $site = trim((string)Request::input('site', ''));
        $project = trim((string)Request::input('project', ''));

        if (empty($sn)) {
            $_SESSION['flash_error'] = "Device Serial Number is required.";
            redirect('devices');
        }

        Device::registerOrHeartbeat($sn, [
            'device_name' => $name ?: "eSSL FRM - {$sn}",
            'device_model' => $model ?: "eSSL / ZKTeco Face Terminal",
            'site' => $site ?: "Head Office",
            'project' => $project ?: "General"
        ]);

        Logger::log(Auth::id(), 'DEVICE_ADDED', "Added/Paired FRM device SN: {$sn}");
        $_SESSION['flash_success'] = "Device [{$sn}] registered successfully! As soon as it pushes punches, attendance will sync automatically.";
        redirect('devices');
    }

    public function update(): void {
        Auth::requireAuth();
        Csrf::verify();

        $id = (int) Request::input('id', 0);
        $name = trim(Request::input('device_name', ''));
        $model = trim(Request::input('device_model', ''));
        $site = trim(Request::input('site', ''));
        $project = trim(Request::input('project', ''));

        if ($id > 0) {
            Device::update($id, [
                'device_name' => $name,
                'device_model' => $model,
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

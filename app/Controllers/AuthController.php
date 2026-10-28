<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Core\Logger;
use App\Models\User;
use App\Models\Setting;

class AuthController extends Controller {
    public function showLogin(): void {
        if (Auth::check()) {
            redirect('dashboard');
        }
        $this->view('auth.login', ['title' => 'Admin Login'], 'auth');
    }

    public function login(): void {
        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            redirect('login');
        }

        $email = trim(Request::input('email', ''));
        $password = trim(Request::input('password', ''));

        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Email and password are required.');
            redirect('login');
        }

        if (Auth::attempt($email, $password)) {
            redirect('dashboard');
        } else {
            $this->setFlash('error', 'Invalid email or password.');
            redirect('login');
        }
    }

    public function logout(): void {
        Auth::logout();
        redirect('login');
    }

    public function profile(): void {
        Auth::requireAuth();
        $user = User::find(Auth::id());
        $allUsers = [];
        $role = $user['role'] ?? Auth::role();
        if ($role === 'admin' || $role === 'superadmin') {
            $allUsers = User::all();
        }

        $smtpSettings = [
            'enabled' => Setting::get('smtp_enabled', '1'),
            'host' => Setting::get('smtp_host', ''),
            'port' => Setting::get('smtp_port', '587'),
            'encryption' => Setting::get('smtp_encryption', 'tls'),
            'username' => Setting::get('smtp_username', ''),
            'password' => Setting::get('smtp_password', ''),
            'from_email' => Setting::get('smtp_from_email', ''),
            'from_name' => Setting::get('smtp_from_name', ''),
        ];

        $this->view('auth.profile', [
            'title' => 'Admin Profile & Security',
            'currentUser' => $user,
            'allUsers' => $allUsers,
            'smtpSettings' => $smtpSettings
        ], 'admin');
    }

    public function updateProfile(): void {
        Auth::requireAuth();
        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $id = Auth::id();
        $name = trim(Request::input('name', ''));
        $email = trim(Request::input('email', ''));
        $newPassword = trim(Request::input('new_password', ''));
        $confirmPassword = trim(Request::input('confirm_password', ''));

        if (empty($name) || empty($email)) {
            $this->setFlash('error', 'Name and Email are required.');
            redirect('profile');
        }

        User::updateProfile($id, $name, $email);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        if (!empty($newPassword)) {
            if (strlen($newPassword) < 6) {
                $this->setFlash('error', 'New password must be at least 6 characters long.');
                redirect('profile');
            }
            if ($newPassword !== $confirmPassword) {
                $this->setFlash('error', 'New passwords do not match.');
                redirect('profile');
            }
            User::updatePassword($id, $newPassword);
        }

        $this->setFlash('success', 'Profile updated successfully!');
        redirect('profile');
    }

    /**
     * Create a new system user login (Admin only)
     */
    public function createUser(): void {
        Auth::requireAuth();
        if (Auth::role() !== 'admin' && Auth::role() !== 'superadmin') {
            $this->setFlash('error', 'Unauthorized access.');
            redirect('profile');
        }

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $name = trim(Request::input('name', ''));
        $email = strtolower(trim(Request::input('email', '')));
        $companyMail = strtolower(trim(Request::input('company_mail', '')));
        $password = trim(Request::input('password', ''));
        $role = trim(Request::input('role', ''));
        $status = trim(Request::input('status', 'active'));

        if (empty($name) || empty($email) || empty($password)) {
            $this->setFlash('error', 'Name, Email, and Password are required.');
            redirect('profile');
        }

        // Clean role and allow custom authority names (e.g. technical, engineering, operations)
        $role = strtolower(trim(preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $role)));
        $role = preg_replace('/\s+/', '_', $role);
        if (empty($role) || $role === 'custom') {
            $role = 'incharge';
        }

        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters.');
            redirect('profile');
        }

        $existing = User::findByEmail($email);
        if ($existing) {
            $this->setFlash('error', "User login with email '{$email}' already exists.");
            redirect('profile');
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'company_mail' => !empty($companyMail) ? $companyMail : null,
            'password' => $password,
            'role' => $role,
            'status' => $status
        ]);

        Logger::log(Auth::id(), 'USER_LOGIN_CREATED', "Created new {$role} login: {$name} ({$email}, Company Mail: " . ($companyMail ?: 'none') . ")");
        $this->setFlash('success', "New system login for '{$name}' ({$email}) created successfully!");
        redirect('profile');
    }

    /**
     * Update an existing system user login (Admin only)
     */
    public function updateUser(): void {
        Auth::requireAuth();
        if (Auth::role() !== 'admin' && Auth::role() !== 'superadmin') {
            $this->setFlash('error', 'Unauthorized access.');
            redirect('profile');
        }

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $id = (int) Request::input('id', 0);
        $user = User::find($id);
        if (!$user) {
            $this->setFlash('error', 'User login not found.');
            redirect('profile');
        }

        $name = trim(Request::input('name', ''));
        $email = strtolower(trim(Request::input('email', '')));
        $companyMail = strtolower(trim(Request::input('company_mail', '')));
        $password = trim(Request::input('password', ''));
        $role = trim(Request::input('role', $user['role']));
        $status = trim(Request::input('status', $user['status']));

        if (empty($name) || empty($email)) {
            $this->setFlash('error', 'Name and Email are required.');
            redirect('profile');
        }

        // Clean role and allow custom authority names
        $role = strtolower(trim(preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $role)));
        $role = preg_replace('/\s+/', '_', $role);
        if (empty($role) || $role === 'custom') {
            $role = $user['role'] ?: 'incharge';
        }

        if (!in_array($status, ['active', 'inactive'])) {
            $status = $user['status'];
        }

        $existing = User::findByEmail($email);
        if ($existing && (int)$existing['id'] !== $id) {
            $this->setFlash('error', "Email '{$email}' is already in use by another user.");
            redirect('profile');
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'company_mail' => !empty($companyMail) ? $companyMail : null,
            'role' => $role,
            'status' => $status
        ];

        if (!empty($password)) {
            if (strlen($password) < 6) {
                $this->setFlash('error', 'Password must be at least 6 characters.');
                redirect('profile');
            }
            $updateData['password'] = $password;
        }

        User::updateUser($id, $updateData);

        if ($id === Auth::id()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
        }

        Logger::log(Auth::id(), 'USER_LOGIN_UPDATED', "Updated login #{$id}: {$name} ({$email}, Role: {$role})");
        $this->setFlash('success', "User login for '{$name}' updated successfully!");
        redirect('profile');
    }

    /**
     * Delete a system user login (Admin only)
     */
    public function deleteUser(): void {
        Auth::requireAuth();
        if (Auth::role() !== 'admin' && Auth::role() !== 'superadmin') {
            $this->setFlash('error', 'Unauthorized access.');
            redirect('profile');
        }

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $id = (int) Request::input('id', 0);
        if ($id === Auth::id()) {
            $this->setFlash('error', 'You cannot delete your own logged-in administrator account.');
            redirect('profile');
        }

        $user = User::find($id);
        if ($user) {
            User::delete($id);
            Logger::log(Auth::id(), 'USER_LOGIN_DELETED', "Deleted system login #{$id}: {$user['name']} ({$user['email']})");
            $this->setFlash('success', "Login for '{$user['name']}' ({$user['email']}) deleted.");
        }

        redirect('profile');
    }

    /**
     * Update Outbound SMTP Mail Server Settings (Admin only)
     */
    public function updateSmtp(): void {
        Auth::requireAuth();
        if (Auth::role() !== 'admin' && Auth::role() !== 'superadmin') {
            $this->setFlash('error', 'Unauthorized access.');
            redirect('profile');
        }

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $enabled = Request::input('smtp_enabled') ? '1' : '0';
        $host = trim((string)Request::input('smtp_host', ''));
        $port = trim((string)Request::input('smtp_port', '587'));
        $encryption = trim((string)Request::input('smtp_encryption', 'tls'));
        $username = trim((string)Request::input('smtp_username', ''));
        $password = trim((string)Request::input('smtp_password', ''));
        $fromEmail = trim((string)Request::input('smtp_from_email', ''));
        $fromName = trim((string)Request::input('smtp_from_name', ''));

        Setting::set('smtp_enabled', $enabled);
        Setting::set('smtp_host', $host);
        Setting::set('smtp_port', $port);
        Setting::set('smtp_encryption', $encryption);
        Setting::set('smtp_username', $username);
        if ($password !== '') {
            Setting::set('smtp_password', $password);
        }
        Setting::set('smtp_from_email', $fromEmail);
        Setting::set('smtp_from_name', $fromName);

        Logger::log(Auth::id(), 'SMTP_CONFIG_UPDATED', "Updated SMTP server configuration: Host={$host}:{$port}, Enc={$encryption}, User={$username}, Enabled={$enabled}");
        $this->setFlash('success', "SMTP Mail Server settings saved successfully!");
        redirect('profile');
    }

    /**
     * Send a Live Test Email to verify SMTP configuration
     */
    public function testSmtp(): void {
        Auth::requireAuth();
        if (Auth::role() !== 'admin' && Auth::role() !== 'superadmin') {
            $this->setFlash('error', 'Unauthorized access.');
            redirect('profile');
        }

        if (!Csrf::validate(Request::input('_csrf_token'))) {
            $this->setFlash('error', 'Invalid security token.');
            redirect('profile');
        }

        $testEmail = trim((string)Request::input('test_email', ''));
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', "Please enter a valid email address to send the test message.");
            redirect('profile');
        }

        $res = \App\Services\MailService::sendTestEmail($testEmail);
        if ($res['success']) {
            $this->setFlash('success', "✅ Test email successfully delivered to '{$testEmail}'! Outbound leave notifications are ready.");
        } else {
            $err = $res['error'] ?? 'Connection error';
            $this->setFlash('error', "❌ SMTP Delivery failed for '{$testEmail}': {$err}. Please verify host, port, credentials, and encryption.");
        }
        redirect('profile');
    }
}

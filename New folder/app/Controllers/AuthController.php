<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Csrf;
use App\Models\User;

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
            $this->setFlash('error', 'Invalid email or password. Default: admin@attendance.local / admin123');
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
        $this->view('auth.profile', [
            'title' => 'Admin Profile & Security',
            'currentUser' => $user
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
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                setFlash('error', 'Please fill in all fields.');
                logWarning('Login attempt with empty fields', ['email' => $email]);
                $this->view('auth/login', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            $user = $this->userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                setFlash('error', 'Invalid email or password.');
                logWarning('Failed login attempt', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
                $this->view('auth/login', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['church_id'] = $user['church_id'] ?? null;

            logInfo('User logged in successfully', [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            $this->redirect('/dashboard');
        } else {
            $this->view('auth/login', ['layout' => 'layouts/unauthenticated']);
        }
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                setFlash('error', 'Please fill in all fields.');
                logWarning('Registration attempt with empty fields', ['email' => $email]);
                $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            if ($password !== $confirmPassword) {
                setFlash('error', 'Passwords do not match.');
                logWarning('Registration attempt with password mismatch', ['email' => $email]);
                $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            if (strlen($password) < 6) {
                setFlash('error', 'Password must be at least 6 characters long.');
                logWarning('Registration attempt with weak password', ['email' => $email]);
                $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            // Check if email already exists
            if ($this->userModel->findByEmail($email)) {
                setFlash('error', 'Email already exists.');
                logWarning('Registration attempt with existing email', ['email' => $email]);
                $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
                return;
            }

            // Create user
            $userId = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => ROLE_MEMBER // Default role
            ]);

            if ($userId) {
                logInfo('New user registered successfully', [
                    'user_id' => $userId,
                    'email' => $email,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                setFlash('success', 'Registration successful! Please log in.');
                $this->redirect('/auth/login');
            } else {
                logError('Failed to create user during registration', [
                    'email' => $email,
                    'name' => $name
                ]);
                setFlash('error', 'Registration failed. Please try again.');
                $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
            }
        } else {
            $this->view('auth/register', ['layout' => 'layouts/unauthenticated']);
        }
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            logInfo('User logged out', [
                'user_id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }

        session_destroy();
        setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/auth/login');
    }

    public function profile(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/auth/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $user = $this->userModel->find($_SESSION['user_id']);

            if (!$user) {
                setFlash('error', 'User not found.');
                logError('Profile update failed - user not found', ['user_id' => $_SESSION['user_id']]);
                $this->redirect('/auth/profile');
                return;
            }

            // Validate current password if changing password
            if (!empty($newPassword)) {
                if (!password_verify($currentPassword, $user['password'])) {
                    setFlash('error', 'Current password is incorrect.');
                    logWarning('Profile update failed - incorrect current password', [
                        'user_id' => $_SESSION['user_id'],
                        'email' => $user['email']
                    ]);
                    $this->redirect('/auth/profile');
                    return;
                }

                if ($newPassword !== $confirmPassword) {
                    setFlash('error', 'New passwords do not match.');
                    logWarning('Profile update failed - password mismatch', [
                        'user_id' => $_SESSION['user_id'],
                        'email' => $user['email']
                    ]);
                    $this->redirect('/auth/profile');
                    return;
                }

                if (strlen($newPassword) < 6) {
                    setFlash('error', 'New password must be at least 6 characters long.');
                    logWarning('Profile update failed - weak password', [
                        'user_id' => $_SESSION['user_id'],
                        'email' => $user['email']
                    ]);
                    $this->redirect('/auth/profile');
                    return;
                }
            }

            // Check if email is already taken by another user
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] !== $_SESSION['user_id']) {
                setFlash('error', 'Email is already taken.');
                logWarning('Profile update failed - email already taken', [
                    'user_id' => $_SESSION['user_id'],
                    'email' => $email
                ]);
                $this->redirect('/auth/profile');
                return;
            }

            // Update user
            $updateData = [
                'name' => $name,
                'email' => $email
            ];

            if (!empty($newPassword)) {
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if ($this->userModel->update($_SESSION['user_id'], $updateData)) {
                // Update session
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $name;

                logInfo('User profile updated successfully', [
                    'user_id' => $_SESSION['user_id'],
                    'email' => $email,
                    'password_changed' => !empty($newPassword)
                ]);

                setFlash('success', 'Profile updated successfully.');
            } else {
                logError('Failed to update user profile', [
                    'user_id' => $_SESSION['user_id'],
                    'email' => $email
                ]);
                setFlash('error', 'Failed to update profile. Please try again.');
            }

            $this->redirect('/auth/profile');
        } else {
            $user = $this->userModel->find($_SESSION['user_id']);
            $this->view('auth/profile', ['user' => $user]);
        }
    }
} 
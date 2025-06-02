<?php
class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function login() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';

                if ($this->user->authenticate($email, $password)) {
                    $_SESSION['success'] = "Welcome back!";
                    $this->redirect('/');
                } else {
                    $_SESSION['error'] = "Invalid email or password.";
                    $this->redirect('/login');
                }
            }

            $this->render('auth/login', [
                'title' => 'Login'
            ]);
        } catch (Exception $e) {
            error_log("Error in AuthController::login: " . $e->getMessage());
            $_SESSION['error'] = "Failed to login.";
            $this->redirect('/error');
        }
    }

    public function logout() {
        try {
            session_destroy();
            $_SESSION['success'] = "You have been logged out successfully.";
            $this->redirect('/login');
        } catch (Exception $e) {
            error_log("Error in AuthController::logout: " . $e->getMessage());
            $_SESSION['error'] = "Failed to logout.";
            $this->redirect('/error');
        }
    }

    public function register() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($this->user->create($_POST)) {
                    $_SESSION['success'] = "Registration successful. Please login.";
                    $this->redirect('/login');
                } else {
                    $_SESSION['error'] = "Registration failed. Please try again.";
                    $this->redirect('/register');
                }
            }

            $this->render('auth/register', [
                'title' => 'Register'
            ]);
        } catch (Exception $e) {
            error_log("Error in AuthController::register: " . $e->getMessage());
            $_SESSION['error'] = "Failed to register.";
            $this->redirect('/error');
        }
    }

    public function forgotPassword() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'] ?? '';
                if ($this->user->sendPasswordResetLink($email)) {
                    $_SESSION['success'] = "Password reset link has been sent to your email.";
                    $this->redirect('/login');
                } else {
                    $_SESSION['error'] = "Email not found.";
                    $this->redirect('/forgot-password');
                }
            }

            $this->render('auth/forgot-password', [
                'title' => 'Forgot Password'
            ]);
        } catch (Exception $e) {
            error_log("Error in AuthController::forgotPassword: " . $e->getMessage());
            $_SESSION['error'] = "Failed to process forgot password request.";
            $this->redirect('/error');
        }
    }

    public function resetPassword() {
        try {
            $token = $_GET['token'] ?? '';
            if (!$token) {
                throw new Exception("Invalid reset token");
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($this->user->resetPassword($token, $_POST['password'])) {
                    $_SESSION['success'] = "Password has been reset successfully.";
                    $this->redirect('/login');
                } else {
                    $_SESSION['error'] = "Failed to reset password.";
                    $this->redirect('/reset-password?token=' . $token);
                }
            }

            $this->render('auth/reset-password', [
                'title' => 'Reset Password',
                'token' => $token
            ]);
        } catch (Exception $e) {
            error_log("Error in AuthController::resetPassword: " . $e->getMessage());
            $_SESSION['error'] = "Failed to reset password.";
            $this->redirect('/error');
        }
    }

    private function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }

    private function redirect($url) {
        header("Location: {$url}");
        exit;
    }
} 

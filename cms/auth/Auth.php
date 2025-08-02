<?php
/**
 * Authentication System
 * BPO CMS Authentication and Authorization
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->initSession();
    }

    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login user with email and password
     */
    public function login($email, $password) {
        try {
            // Check if user exists
            $stmt = $this->db->query(
                "SELECT id, username, email, password, role FROM users WHERE email = ?",
                [$email]
            );
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();

            // Log login activity
            $this->logActivity($user['id'], 'login', 'User logged in successfully');

            return ['success' => true, 'user' => $user];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $role = 'user') {
        try {
            // Check if user already exists
            $stmt = $this->db->query(
                "SELECT id FROM users WHERE email = ? OR username = ?",
                [$email, $username]
            );
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'User already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->query(
                "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$username, $email, $hashedPassword, $role]
            );

            return ['success' => true, 'message' => 'User registered successfully'];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) return false;

        // Get user permissions from database
        $stmt = $this->db->query(
            "SELECT p.name FROM permissions p 
             JOIN user_permissions up ON p.id = up.permission_id 
             WHERE up.user_id = ?",
            [$user['id']]
        );

        $permissions = $stmt->fetchAll();
        $userPermissions = array_column($permissions, 'name');

        return in_array($permission, $userPermissions);
    }

    /**
     * Log user activity
     */
    private function logActivity($userId, $action, $description) {
        try {
            $this->db->query(
                "INSERT INTO user_activity (user_id, action, description, ip_address, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$userId, $action, $description, $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
        } catch (Exception $e) {
            error_log("Activity logging failed: " . $e->getMessage());
        }
    }

    /**
     * Require authentication for page access
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.html');
            exit();
        }
    }

    /**
     * Require specific role for page access
     */
    public function requireRole($role) {
        $this->requireAuth();
        
        if (!$this->hasRole($role)) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied. Insufficient permissions.';
            exit();
        }
    }
} 
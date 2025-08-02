<?php
/**
 * Session Management
 * Secure session handling with CSRF protection
 */

class Session {
    private static $instance = null;
    
    private function __construct() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set session value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session key
     */
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    /**
     * Destroy session
     */
    public function destroy() {
        session_destroy();
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Flash message (temporary session data)
     */
    public function flash($key, $value = null) {
        if ($value === null) {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        
        $_SESSION['flash'][$key] = $value;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current user ID
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Check if session has expired
     */
    public function isExpired($timeout = 3600) { // 1 hour default
        if (!isset($_SESSION['login_time'])) {
            return true;
        }
        
        return (time() - $_SESSION['login_time']) > $timeout;
    }
    
    /**
     * Extend session timeout
     */
    public function extend() {
        $_SESSION['login_time'] = time();
    }
} 
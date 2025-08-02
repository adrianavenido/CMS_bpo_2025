<?php
/**
 * Middleware System
 * Authentication and authorization middleware
 */

require_once __DIR__ . '/Session.php';

class Middleware {
    private $session;
    
    public function __construct() {
        $this->session = Session::getInstance();
    }
    
    /**
     * Require authentication for page access
     */
    public function requireAuth() {
        if (!$this->session->isAuthenticated()) {
            $this->redirectToLogin();
        }
        
        // Check if session has expired
        if ($this->session->isExpired()) {
            $this->session->destroy();
            $this->redirectToLogin('Session expired. Please login again.');
        }
        
        // Extend session
        $this->session->extend();
    }
    
    /**
     * Require specific role for page access
     */
    public function requireRole($role) {
        $this->requireAuth();
        
        $userRole = $this->session->getUserRole();
        if ($userRole !== $role) {
            $this->redirectToError('Access denied. Insufficient permissions.');
        }
    }
    
    /**
     * Require any of the specified roles
     */
    public function requireAnyRole($roles) {
        $this->requireAuth();
        
        $userRole = $this->session->getUserRole();
        if (!in_array($userRole, $roles)) {
            $this->redirectToError('Access denied. Insufficient permissions.');
        }
    }
    
    /**
     * Check if user has permission (for AJAX requests)
     */
    public function checkPermission($permission) {
        if (!$this->session->isAuthenticated()) {
            return ['success' => false, 'message' => 'Authentication required'];
        }
        
        // This would typically check against the database
        // For now, we'll use role-based checks
        $userRole = $this->session->getUserRole();
        
        $rolePermissions = [
            'admin' => ['*'], // All permissions
            'manager' => ['user.view', 'user.edit', 'hr.view', 'hr.create', 'hr.edit', 'client.view', 'client.create', 'client.edit', 'ticket.view', 'ticket.create', 'ticket.edit', 'task.view', 'task.create', 'task.edit'],
            'supervisor' => ['user.view', 'hr.view', 'hr.create', 'hr.edit', 'client.view', 'client.create', 'ticket.view', 'ticket.create', 'ticket.edit', 'task.view', 'task.create', 'task.edit'],
            'agent' => ['user.view', 'ticket.view', 'ticket.create', 'ticket.edit', 'task.view', 'task.edit'],
            'hr' => ['user.view', 'user.edit', 'hr.view', 'hr.create', 'hr.edit', 'hr.delete']
        ];
        
        $userPermissions = $rolePermissions[$userRole] ?? [];
        
        if (in_array('*', $userPermissions) || in_array($permission, $userPermissions)) {
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Insufficient permissions'];
    }
    
    /**
     * Redirect to login page
     */
    private function redirectToLogin($message = null) {
        if ($message) {
            $this->session->flash('error', $message);
        }
        
        header('Location: /login.html');
        exit();
    }
    
    /**
     * Redirect to error page
     */
    private function redirectToError($message) {
        $this->session->flash('error', $message);
        header('Location: /error.html');
        exit();
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        if (!$this->session->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $this->session->getUserId(),
            'username' => $this->session->get('username'),
            'email' => $this->session->get('email'),
            'role' => $this->session->getUserRole(),
            'first_name' => $this->session->get('first_name'),
            'last_name' => $this->session->get('last_name'),
            'department' => $this->session->get('department'),
            'position' => $this->session->get('position')
        ];
    }
    
    /**
     * Log user activity
     */
    public function logActivity($action, $description = '') {
        if (!$this->session->isAuthenticated()) {
            return;
        }
        
        $userId = $this->session->getUserId();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // This would typically be logged to database
        error_log("User Activity: User ID {$userId}, Action: {$action}, Description: {$description}, IP: {$ipAddress}");
    }
}

// Initialize middleware for page protection
function requireAuth() {
    $middleware = new Middleware();
    $middleware->requireAuth();
}

function requireRole($role) {
    $middleware = new Middleware();
    $middleware->requireRole($role);
}

function requireAnyRole($roles) {
    $middleware = new Middleware();
    $middleware->requireAnyRole($roles);
} 
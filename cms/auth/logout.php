<?php
/**
 * Logout Endpoint
 * Handles user logout and session cleanup
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/Auth.php';

try {
    // Initialize session and auth
    $session = Session::getInstance();
    $auth = new Auth();

    // Log the logout activity
    if ($session->isAuthenticated()) {
        $auth->logActivity($session->getUserId(), 'logout', 'User logged out');
    }

    // Perform logout
    $result = $auth->logout();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'You have been logged out successfully.'
    ]);

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Logout failed. Please try again.'
    ]);
} 
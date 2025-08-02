<?php
/**
 * Authentication Check Endpoint
 * Verifies if user is currently logged in
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/Auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $session = Session::getInstance();
    $auth = new Auth();
    
    // Check if user is logged in
    $currentUser = $auth->getCurrentUser();
    
    if ($currentUser) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $currentUser['id'],
                'username' => $currentUser['username'],
                'email' => $currentUser['email'],
                'role' => $currentUser['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not authenticated'
        ]);
    }
} catch (Exception $e) {
    error_log("Auth check error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Authentication check failed'
    ]);
} 
<?php
/**
 * CSRF Token Endpoint
 * Provides CSRF tokens for secure form submissions
 */

require_once __DIR__ . '/../core/Session.php';

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
    $token = $session->generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'token' => $token
    ]);
} catch (Exception $e) {
    error_log("CSRF token generation error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate CSRF token'
    ]);
} 
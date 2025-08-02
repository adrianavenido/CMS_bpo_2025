<?php
/**
 * Login Processing Endpoint
 * Handles login form submission and authentication
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/Auth.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Verify CSRF token
    $session = Session::getInstance();
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    if (!$session->verifyCSRFToken($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token']);
        exit();
    }

    // Get POST data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }

    // Initialize authentication
    $auth = new Auth();
    
    // Attempt login
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        // Login successful
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/index.html'
        ]);
    } else {
        // Login failed
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }

} catch (Exception $e) {
    error_log("Login processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
} 
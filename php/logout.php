<?php
// Include configuration file
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit;
}

// Get token from headers
$headers = apache_request_headers();
$token = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Authorization token is required']);
    exit;
}

try {
    // Delete token from Redis
    $redis = getRedisConnection();
    $redis->del("token:$token");
    
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
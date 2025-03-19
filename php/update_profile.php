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

// Get profile data
$age = isset($_POST['age']) ? trim($_POST['age']) : '';
$dob = isset($_POST['dob']) ? trim($_POST['dob']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

try {
    // Verify token in Redis
    $redis = getRedisConnection();
    $tokenData = $redis->get("token:$token");
    
    if (!$tokenData) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }
    
    $tokenData = json_decode($tokenData, true);
    $email = $tokenData['email'];
    
    // Get MongoDB connection
    $mongoDB = getMongoDBConnection();
    $collection = $mongoDB->profiles;
    
    // Update profile
    $result = $collection->updateOne(
        ['email' => $email],
        ['$set' => [
            'age' => $age,
            'dob' => $dob,
            'phone' => $phone,
            'address' => $address,
            'bio' => $bio,
            'updated_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
        ]]
    );
    
    if ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile or no changes made']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
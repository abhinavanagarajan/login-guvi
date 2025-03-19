<?php
// Include configuration file
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Only GET method is allowed']);
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
    // Verify token in Redis
    $redis = getRedisConnection();
    $tokenData = $redis->get("token:$token");
    
    if (!$tokenData) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }
    
    $tokenData = json_decode($tokenData, true);
    $email = $tokenData['email'];
    
    // Get user details from MySQL
    $conn = getMySQLConnection();
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $user['username'];
    
    // Initialize default profile
    $profile = [
        'username' => $username,
        'email' => $email,
        'age' => '',
        'dob' => '',
        'phone' => '',
        'address' => '',
        'bio' => ''
    ];
    
    try {
        // Get profile from MongoDB
        $mongoDB = getMongoDBConnection();
        $collection = $mongoDB->profiles;
        
        $mongoProfile = $collection->findOne(['email' => $email]);
        
        if (!$mongoProfile) {
            // Create a new profile if it doesn't exist
            $collection->insertOne([
                'email' => $email,
                'username' => $username,
                'age' => '',
                'dob' => '',
                'phone' => '',
                'address' => '',
                'bio' => '',
                'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
            ]);
        } else {
            // Update profile with MongoDB data
            $profile = [
                'username' => $mongoProfile['username'],
                'email' => $mongoProfile['email'],
                'age' => $mongoProfile['age'] ?? '',
                'dob' => $mongoProfile['dob'] ?? '',
                'phone' => $mongoProfile['phone'] ?? '',
                'address' => $mongoProfile['address'] ?? '',
                'bio' => $mongoProfile['bio'] ?? ''
            ];
        }
    } catch (Exception $e) {
        error_log("MongoDB Error: " . $e->getMessage());
        // Continue with default profile if MongoDB fails
    }
    
    echo json_encode([
        'success' => true, 
        'profile' => $profile
    ]);
    
} catch (PDOException $e) {
    error_log("MySQL Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
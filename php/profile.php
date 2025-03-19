<?php
// Include configuration file
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

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
    
    // Get profile from MongoDB
    $mongoDB = getMongoDBConnection();
    $collection = $mongoDB->profiles;
    
    $profile = $collection->findOne(['email' => $email]);
    
    if (!$profile) {
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
        
        $profile = [
            'username' => $username,
            'email' => $email,
            'age' => '',
            'dob' => '',
            'phone' => '',
            'address' => '',
            'bio' => ''
        ];
    } else {
        // Convert MongoDB document to array
        $profile = iterator_to_array($profile);
        // Format the profile data
        $profile = [
            'username' => $profile['username'],
            'email' => $profile['email'],
            'age' => $profile['age'],
            'dob' => $profile['dob'],
            'phone' => $profile['phone'],
            'address' => $profile['address'],
            'bio' => $profile['bio']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'profile' => $profile
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
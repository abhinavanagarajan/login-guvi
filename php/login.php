<?php
// Include configuration file
require_once 'config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit;
}

// Get user input
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

try {
    // Get MySQL connection
    $conn = getMySQLConnection();
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid password']);
        exit;
    }
    
    try {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $userId = $user['id'];
        $expiresAt = time() + TOKEN_EXPIRATION;
        
        // Store token in Redis
        $redis = getRedisConnection();
        $redis->setex("token:$token", TOKEN_EXPIRATION, json_encode([
            'user_id' => $userId,
            'email' => $email,
            'expires_at' => $expiresAt
        ]));
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'token' => $token
        ]);
    } catch (Exception $e) {
        error_log("Redis Error: " . $e->getMessage());
        // If Redis fails, we can still allow login but without session persistence
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful (session may not persist)',
            'token' => $token
        ]);
    }
    
} catch (PDOException $e) {
    error_log("MySQL Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
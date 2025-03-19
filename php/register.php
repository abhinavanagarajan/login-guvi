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
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

try {
    // Get MySQL connection
    $conn = getMySQLConnection();
    
    // Debug log
    error_log("Starting registration process for username: " . $username . " and email: " . $email);
    
    // Check if username already exists
    $query = "SELECT id FROM users WHERE username = :username";
    error_log("Checking username query: " . $query);
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }
    
    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = :email";
    error_log("Checking email query: " . $query);
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user into database
    $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
    error_log("Insert user query: " . $query);
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->execute();
    
    // Create MongoDB document for user profile
    try {
        $mongoDB = getMongoDBConnection();
        $collection = $mongoDB->profiles;
        
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
        
        echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        
    } catch (MongoDB\Driver\Exception\Exception $e) {
        error_log("MongoDB Error: " . $e->getMessage());
        // If MongoDB fails but MySQL succeeded, we still consider it a success
        // as the profile can be created later
        echo json_encode(['success' => true, 'message' => 'User registered successfully']);
    }
    
} catch (PDOException $e) {
    error_log("MySQL Error: " . $e->getMessage());
    error_log("MySQL Error Code: " . $e->getCode());
    error_log("MySQL Error File: " . $e->getFile());
    error_log("MySQL Error Line: " . $e->getLine());
    error_log("MySQL Error Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?> 
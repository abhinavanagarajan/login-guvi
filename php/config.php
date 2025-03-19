<?php
// Include Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$env = parse_url(getenv("CLEARDB_DATABASE_URL")); // For MySQL
$mongo = parse_url(getenv("MONGODB_URI")); // For MongoDB
$redis = parse_url(getenv("REDIS_URL")); // For Redis

// MySQL Configuration
define('DB_HOST', $env["host"] ?? 'localhost');
define('DB_USER', $env["user"] ?? 'root');
define('DB_PASS', $env["pass"] ?? '12345678');
define('DB_NAME', 'login_guvi');

// MongoDB Configuration
$mongoUri = getenv("MONGODB_URI");
if (!$mongoUri) {
    $mongoUri = 'mongodb://localhost:27017';
}
define('MONGO_URI', $mongoUri);
define('MONGO_DB', 'user_profiles');

// Redis Configuration
define('REDIS_HOST', $redis["host"] ?? 'localhost');
define('REDIS_PORT', $redis["port"] ?? 6379);
define('REDIS_PASSWORD', $redis["pass"] ?? null);

// Token expiration time (in seconds)
define('TOKEN_EXPIRATION', 3600); // 1 hour

// Create MySQL connection
function getMySQLConnection() {
    try {
        // First connect without database
        $conn = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            )
        );
        
        // Create database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        
        // Select the database
        $conn->exec("USE " . DB_NAME);
        
        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        
        return $conn;
    } catch(PDOException $e) {
        error_log("MySQL Connection Error: " . $e->getMessage());
        throw $e;
    }
}

// Create MongoDB connection
function getMongoDBConnection() {
    try {
        error_log("Connecting to MongoDB with URI: " . MONGO_URI);
        $client = new MongoDB\Client(MONGO_URI);
        return $client->selectDatabase(MONGO_DB);
    } catch(Exception $e) {
        error_log("MongoDB Connection Error: " . $e->getMessage());
        throw $e;
    }
}

// Create Redis connection
function getRedisConnection() {
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        if (REDIS_PASSWORD) {
            $redis->auth(REDIS_PASSWORD);
        }
        return $redis;
    } catch(Exception $e) {
        error_log("Redis Connection Error: " . $e->getMessage());
        throw $e;
    }
}
?> 
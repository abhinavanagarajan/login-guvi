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
define('DB_NAME', substr($env["path"], 1) ?? 'login_guvi');

// MongoDB Configuration
define('MONGO_URI', getenv("MONGODB_URI") ?? 'mongodb://localhost:27017');
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
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("MySQL Connection Error: " . $e->getMessage());
        throw $e;
    }
}

// Create MongoDB connection
function getMongoDBConnection() {
    try {
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
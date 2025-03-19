# Guvi Login System

A full-stack login system with user profile management using PHP, MySQL, MongoDB, and Redis.

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.0 or higher
- MySQL 8.0 or higher
- MongoDB 6.0 or higher
- Redis 7.0 or higher
- Composer (PHP package manager)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd login-guvi
```

2. Install PHP dependencies using Composer:
```bash
composer install
```

3. Set up MySQL:
```bash
# Log into MySQL
mysql -u root -p

# Create database and tables (in MySQL prompt)
CREATE DATABASE login_guvi;
USE login_guvi;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

# Exit MySQL
exit;
```

4. Start required services:
```bash
# Start MySQL
brew services start mysql

# Start MongoDB
brew services start mongodb-community

# Start Redis
brew services start redis
```

5. Configure your environment:
- MySQL configuration:
  - Host: localhost
  - Username: root
  - Password: 12345678 (change this in config.php)
  - Database: login_guvi

- MongoDB configuration:
  - URI: mongodb://localhost:27017
  - Database: user_profiles

- Redis configuration:
  - Host: localhost
  - Port: 6379

## Running the Application

1. Start the PHP development server:
```bash
php -S localhost:8000
```

2. Open your web browser and navigate to:
```
http://localhost:8000
```

## Features

- User Registration
- User Login with Token-based Authentication
- Profile Management
- Session Management using Redis
- Profile Data Storage using MongoDB
- Secure Password Hashing
- Input Validation
- Error Handling

## Project Structure

```
login-guvi/
├── css/
│   └── styles.css
├── js/
│   ├── login.js
│   ├── register.js
│   └── profile.js
├── php/
│   ├── config.php
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   ├── update_profile.php
│   └── logout.php
├── vendor/
├── composer.json
├── index.html
├── login.html
├── register.html
└── profile.html
```

## Security Notes

1. Change default database credentials in production
2. Enable HTTPS in production
3. Set secure session configurations
4. Implement rate limiting
5. Add input sanitization

## Troubleshooting

1. **Database Connection Issues**
   - Verify MySQL service is running
   - Check database credentials in config.php
   - Ensure database and tables exist

2. **MongoDB Issues**
   - Verify MongoDB service is running
   - Check MongoDB connection string
   - Ensure MongoDB PHP driver is installed

3. **Redis Issues**
   - Verify Redis service is running
   - Check Redis connection settings
   - Ensure Redis PHP extension is installed

## License

MIT License

<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');  // Update these with your actual database credentials
define('DB_PASS', 'your_db_pass');
define('DB_NAME', 'n8ndash');

// Create connection with error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting charset: " . $conn->error);
    }
} catch (Exception $e) {
    // Log the error
    error_log("Database connection error: " . $e->getMessage());
    
    // If it's not an AJAX request, show a user-friendly error
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die("
            <div style='text-align: center; margin-top: 50px;'>
                <h1>Database Connection Error</h1>
                <p>We're experiencing technical difficulties. Please try again later.</p>
                <p>If the problem persists, please contact your administrator.</p>
            </div>
        ");
    } else {
        // For AJAX requests, return JSON error
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'error' => 'Database connection error']));
    }
}

// Function to safely close the database connection
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db(DB_NAME);

// Create users table with updated schema
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_first_login BOOLEAN DEFAULT 1,
    is_admin BOOLEAN DEFAULT 0,
    logo_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Create dashboards table
$sql = "CREATE TABLE IF NOT EXISTS dashboards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    layout JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating dashboards table: " . $conn->error);
}

// Check if admin user exists, if not create it
$admin_email = 'admin@example.com';
$default_password = password_hash('password', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, email, password, is_admin) VALUES ('admin', ?, ?, 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $admin_email, $default_password);
if ($stmt->execute() === FALSE) {
    die("Error creating admin user: " . $conn->error);
}
?> 
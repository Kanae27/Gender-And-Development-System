<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
define('DB_SERVER', 'localhost');     // Usually 'localhost'
define('DB_USERNAME', 'root');        // Your database username
define('DB_PASSWORD', '');            // Your database password
define('DB_NAME', 'gad_db');      // Your database name

// Attempt to connect to MySQL database using mysqli
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Also create a PDO connection for API endpoints
try {
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    error_log("PDO connection error: " . $e->getMessage());
    die("PDO Connection failed: " . $e->getMessage());
}

// Create tables if they don't exist
$sql_programs = "CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

$sql_projects = "CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

// Create target table if it doesn't exist
$sql_target = "CREATE TABLE IF NOT EXISTS target (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campus VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    total_gaa DECIMAL(15,2) DEFAULT 0,
    total_gad_fund DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY campus_year_unique (campus, year)
) ENGINE=InnoDB;";

try {
    if (!$conn->query($sql_programs)) {
        throw new Exception("Error creating programs table: " . $conn->error);
    }
    if (!$conn->query($sql_projects)) {
        throw new Exception("Error creating projects table: " . $conn->error);
    }
    if (!$conn->query($sql_target)) {
        throw new Exception("Error creating target table: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Table creation error: " . $e->getMessage());
}
?>

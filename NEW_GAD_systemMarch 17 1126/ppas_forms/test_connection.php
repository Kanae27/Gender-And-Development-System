<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Include config file
    require_once('../config.php');
    
    // Test connection
    if (!$conn) {
        throw new Exception("Connection variable is null");
    }
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Database connection successful!<br>";
    echo "Server info: " . $conn->server_info;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 
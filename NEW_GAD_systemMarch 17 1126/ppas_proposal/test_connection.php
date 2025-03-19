<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../includes/config.php');

try {
    echo "Attempting to connect to database...<br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Database: " . DB_NAME . "<br>";
    
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connection successful!<br>";
    
    // Test the ppas_forms table
    echo "Testing ppas_forms table...<br>";
    $query = "SHOW TABLES LIKE 'ppas_forms'";
    $result = $pdo->query($query);
    
    if ($result->rowCount() > 0) {
        echo "ppas_forms table exists!<br>";
        
        // Check table structure
        echo "Table structure:<br>";
        $query = "DESCRIBE ppas_forms";
        $result = $pdo->query($query);
        echo "<pre>";
        print_r($result->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        // Check for data
        $query = "SELECT DISTINCT year FROM ppas_forms ORDER BY year DESC";
        $result = $pdo->query($query);
        echo "Years in database:<br>";
        echo "<pre>";
        print_r($result->fetchAll(PDO::FETCH_COLUMN));
        echo "</pre>";
    } else {
        echo "ppas_forms table does not exist!<br>";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} 
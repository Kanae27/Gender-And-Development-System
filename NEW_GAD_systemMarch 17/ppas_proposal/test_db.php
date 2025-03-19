<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// Check if configuration file exists
$configFile = __DIR__ . '/../includes/db_connection.php';
echo "Looking for config file at: " . $configFile . "<br>";
if (!file_exists($configFile)) {
    die("Error: Configuration file not found!");
}

// Include the configuration file
require_once $configFile;

// Verify constants are defined
echo "<h3>Configuration Check:</h3>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'Not defined') . "<br>";
echo "DB_PASS: " . (defined('DB_PASS') ? '[Set]' : 'Not defined') . "<br>";

// Test MySQL server connection first (without database)
echo "<h3>MySQL Server Connection Test:</h3>";
try {
    echo "Attempting to connect to MySQL server...<br>";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "MySQL server connection successful!<br>";

    // Test ppas_forms table
    echo "<h3>PPAS Forms Table Test:</h3>";
    $query = "SHOW TABLES LIKE 'ppas_forms'";
    $result = $pdo->query($query);
    if ($result->rowCount() > 0) {
        echo "ppas_forms table exists!<br>";
        
        // Check table structure
        echo "<h4>PPAS Forms Table Structure:</h4>";
        $query = "DESCRIBE ppas_forms";
        $result = $pdo->query($query);
        echo "<pre>";
        print_r($result->fetchAll());
        echo "</pre>";
        
        // Check for data
        $query = "SELECT * FROM ppas_forms LIMIT 5";
        $result = $pdo->query($query);
        echo "<h4>Sample PPAS Forms Data (First 5 rows):</h4>";
        echo "<pre>";
        print_r($result->fetchAll());
        echo "</pre>";
    } else {
        echo "<strong style='color: red;'>Warning: ppas_forms table does not exist!</strong><br>";
    }

    // Test ppas_personnel table
    echo "<h3>PPAS Personnel Table Test:</h3>";
    $query = "SHOW TABLES LIKE 'ppas_personnel'";
    $result = $pdo->query($query);
    if ($result->rowCount() > 0) {
        echo "ppas_personnel table exists!<br>";
        
        // Check table structure
        echo "<h4>PPAS Personnel Table Structure:</h4>";
        $query = "DESCRIBE ppas_personnel";
        $result = $pdo->query($query);
        echo "<pre>";
        print_r($result->fetchAll());
        echo "</pre>";
        
        // Check for data
        $query = "SELECT * FROM ppas_personnel LIMIT 5";
        $result = $pdo->query($query);
        echo "<h4>Sample PPAS Personnel Data (First 5 rows):</h4>";
        echo "<pre>";
        print_r($result->fetchAll());
        echo "</pre>";

        // Test join between tables
        echo "<h3>Testing Table Relationship:</h3>";
        $query = "SELECT p.*, f.year, f.quarter 
                 FROM ppas_personnel p 
                 LEFT JOIN ppas_forms f ON p.ppas_id = f.id 
                 LIMIT 5";
        try {
            $result = $pdo->query($query);
            echo "<h4>Sample Joined Data (First 5 rows):</h4>";
            echo "<pre>";
            print_r($result->fetchAll());
            echo "</pre>";
        } catch (PDOException $e) {
            echo "<strong style='color: red;'>Join query failed: " . $e->getMessage() . "</strong><br>";
        }
    } else {
        echo "<strong style='color: red;'>Warning: ppas_personnel table does not exist!</strong><br>";
    }

    // Show all tables in the database
    echo "<h3>All Tables in Database:</h3>";
    $result = $pdo->query("SHOW TABLES");
    echo "<pre>";
    print_r($result->fetchAll());
    echo "</pre>";

} catch (PDOException $e) {
    echo "<strong style='color: red;'>Database connection failed: " . $e->getMessage() . "</strong><br>";
    echo "Error code: " . $e->getCode() . "<br>";
} 
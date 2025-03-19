<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

try {
    // Include database connection
    require_once '../includes/db_connection.php';
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check if gad_proposals table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'gad_proposals'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ gad_proposals table exists</p>";
        
        // Show table structure
        $stmt = $conn->query("DESCRIBE gad_proposals");
        echo "<h2>gad_proposals Table Structure:</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>❌ gad_proposals table does not exist!</p>";
        echo "<p>Please run the <a href='create_gad_tables.php'>create_gad_tables.php</a> script first.</p>";
    }
    
    // Check database connection details
    echo "<h2>Database Connection Details:</h2>";
    echo "<ul>";
    echo "<li>Host: " . DB_HOST . "</li>";
    echo "<li>Database: " . DB_NAME . "</li>";
    echo "<li>User: " . DB_USER . "</li>";
    echo "</ul>";
    
    // Check PDO attributes
    echo "<h2>PDO Connection Attributes:</h2>";
    echo "<ul>";
    echo "<li>Driver Name: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "</li>";
    echo "<li>Server Version: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "</li>";
    echo "<li>Client Version: " . $conn->getAttribute(PDO::ATTR_CLIENT_VERSION) . "</li>";
    echo "<li>Connection Status: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
    
    // Additional debugging information
    echo "<h2>Error Details:</h2>";
    echo "<pre>";
    print_r($e);
    echo "</pre>";
}
?> 
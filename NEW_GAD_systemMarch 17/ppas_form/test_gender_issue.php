<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

echo "<h1>Testing Gender Issue Field in PPAS Form</h1>";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Connected to database</p>";

    // Check if gender_issue column exists
    $result = $conn->query("SHOW COLUMNS FROM ppas_forms LIKE 'gender_issue'");
    if ($result->rowCount() > 0) {
        echo "<p style='color:green'>The 'gender_issue' column exists in ppas_forms table.</p>";
        
        $column = $result->fetch(PDO::FETCH_ASSOC);
        echo "<p>Column Details: " . print_r($column, true) . "</p>";
    } else {
        echo "<p style='color:red'>The 'gender_issue' column does NOT exist in ppas_forms table!</p>";
        
        // Try to add the column
        $conn->exec("ALTER TABLE ppas_forms ADD COLUMN gender_issue TEXT NULL AFTER quarter");
        echo "<p style='color:blue'>Added 'gender_issue' column to the table.</p>";
    }

    // Insert a test record with gender issue
    $stmt = $conn->prepare("
        INSERT INTO ppas_forms (
            year, quarter, gender_issue, type, title, location, start_date, end_date, 
            start_time, end_time, has_lunch_break, total_duration, 
            approved_budget, source_of_budget, created_by
        ) VALUES (
            '2025', 'Q2', 'Test Gender Issue', 'Program', 'Test Program', 'Test Location', '2025-03-20', '2025-03-20',
            '09:00', '17:00', 1, 8.00, 
            1000.00, 'Test Budget', 'Test User'
        )
    ");
    
    $stmt->execute();
    $testId = $conn->lastInsertId();
    echo "<p style='color:green'>Successfully inserted a test record with ID: $testId</p>";

    // Verify the data
    $stmt = $conn->prepare("SELECT id, title, gender_issue, type FROM ppas_forms WHERE id = ?");
    $stmt->execute([$testId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Verification Results:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Title</th><th>Gender Issue</th><th>Type</th></tr>";
    
    foreach ($results as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['gender_issue'] . "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Clean up test data
    $stmt = $conn->prepare("DELETE FROM ppas_forms WHERE id = ?");
    $stmt->execute([$testId]);
    echo "<p>Test data has been removed from the database.</p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='ppas.php'>Return to PPAS Form</a></p>";
?> 
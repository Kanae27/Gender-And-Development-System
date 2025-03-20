<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check structure of gad_proposal_activities table
    $stmt = $conn->query("DESCRIBE gad_proposal_activities");
    echo "Structure of gad_proposal_activities table:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " - " . $row['Default'] . "\n";
    }
    
    echo "\n";
    
    // Check structure of gad_proposal_personnel table
    $stmt = $conn->query("DESCRIBE gad_proposal_personnel");
    echo "Structure of gad_proposal_personnel table:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " - " . $row['Default'] . "\n";
    }
    
    echo "\n";
    
    // Check structure of gad_proposals table
    $stmt = $conn->query("DESCRIBE gad_proposals");
    echo "Structure of gad_proposals table:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . " - " . ($row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . " - " . $row['Default'] . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
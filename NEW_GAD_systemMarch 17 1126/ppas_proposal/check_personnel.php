<?php
// Include database connection
require_once '../includes/db_connection.php';

// Set content type to text/plain for better readability
header('Content-Type: text/plain');

// Get IDs from GET parameters
$ppasId = $_GET['ppasId'] ?? null;
$proposalId = $_GET['proposalId'] ?? null;

if (!$ppasId || !$proposalId) {
    echo "Please provide both ppasId and proposalId parameters.\n";
    echo "Example: check_personnel.php?ppasId=1&proposalId=5\n";
    exit;
}

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get PPAS personnel
    $ppasStmt = $conn->prepare("SELECT * FROM ppas_personnel WHERE ppas_id = :ppasId ORDER BY role, id");
    $ppasStmt->execute([':ppasId' => $ppasId]);
    $ppasPersonnel = $ppasStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get GAD proposal personnel
    $gadStmt = $conn->prepare("SELECT * FROM gad_proposal_personnel WHERE proposal_id = :proposalId ORDER BY role, id");
    $gadStmt->execute([':proposalId' => $proposalId]);
    $gadPersonnel = $gadStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display PPAS personnel
    echo "PPAS Personnel (ppas_id = $ppasId):\n";
    echo "====================================\n";
    if (empty($ppasPersonnel)) {
        echo "No personnel found in ppas_personnel table for PPAS ID: $ppasId\n";
    } else {
        echo "ID\tPPAS ID\tPersonnel ID\tRole\tName\n";
        foreach ($ppasPersonnel as $person) {
            echo $person['id'] . "\t" . 
                 $person['ppas_id'] . "\t" . 
                 $person['personnel_id'] . "\t" . 
                 $person['role'] . "\t" . 
                 $person['personnel_name'] . "\n";
        }
    }
    echo "\n";
    
    // Display GAD proposal personnel
    echo "GAD Proposal Personnel (proposal_id = $proposalId):\n";
    echo "==================================================\n";
    if (empty($gadPersonnel)) {
        echo "No personnel found in gad_proposal_personnel table for Proposal ID: $proposalId\n";
    } else {
        echo "ID\tProposal ID\tPersonnel ID\tRole\tCreated At\n";
        foreach ($gadPersonnel as $person) {
            echo $person['id'] . "\t" . 
                 $person['proposal_id'] . "\t" . 
                 $person['personnel_id'] . "\t" . 
                 $person['role'] . "\t" . 
                 $person['created_at'] . "\n";
        }
    }
    echo "\n";
    
    // Compare personnel lists
    echo "Comparison:\n";
    echo "===========\n";
    
    $ppasIds = array_column($ppasPersonnel, 'personnel_id');
    $gadIds = array_column($gadPersonnel, 'personnel_id');
    
    $inBothTables = array_intersect($ppasIds, $gadIds);
    $onlyInPpas = array_diff($ppasIds, $gadIds);
    $onlyInGad = array_diff($gadIds, $ppasIds);
    
    echo "Personnel in both tables: " . count($inBothTables) . "\n";
    echo "Personnel only in PPAS table: " . count($onlyInPpas) . "\n";
    echo "Personnel only in GAD table: " . count($onlyInGad) . "\n";
    
    if (!empty($onlyInPpas)) {
        echo "\nPersonnel IDs only in PPAS table: " . implode(', ', $onlyInPpas) . "\n";
    }
    
    if (!empty($onlyInGad)) {
        echo "\nPersonnel IDs only in GAD table: " . implode(', ', $onlyInGad) . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
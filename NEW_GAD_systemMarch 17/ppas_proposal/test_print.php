<?php
// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../includes/db_connection.php');

// Clear previous debug log
$debug_file = __DIR__ . '/print_debug.log';
file_put_contents($debug_file, "Print test started at " . date('Y-m-d H:i:s') . "\n");

echo '<h1>Testing Print Functionality</h1>';

try {
    // Get the most recent GAD proposal for testing
    $stmt = $conn->query("SELECT id, activity_title, ppas_id FROM gad_proposals ORDER BY id DESC LIMIT 1");
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proposal) {
        echo '<div style="color: red;">No GAD proposals found in the database. Please create a GAD proposal first.</div>';
        exit;
    }
    
    echo '<div style="margin-bottom: 20px;">';
    echo '<strong>Found GAD Proposal:</strong> ' . htmlspecialchars($proposal['activity_title']) . ' (ID: ' . $proposal['id'] . ')<br>';
    echo '<strong>PPAS ID:</strong> ' . ($proposal['ppas_id'] ? $proposal['ppas_id'] : 'None') . '<br>';
    echo '</div>';
    
    // Check if the proposal has personnel
    $personnelStmt = $conn->prepare("SELECT COUNT(*) FROM gad_proposal_personnel WHERE proposal_id = :id");
    $personnelStmt->execute([':id' => $proposal['id']]);
    $personnelCount = $personnelStmt->fetchColumn();
    
    echo '<div style="margin-bottom: 20px;">';
    echo '<strong>Personnel Count:</strong> ' . $personnelCount . '<br>';
    echo '</div>';
    
    // If there's a PPAS ID, check personnel in ppas_personnel
    if ($proposal['ppas_id']) {
        $ppasPersonnelStmt = $conn->prepare("SELECT COUNT(*) FROM ppas_personnel WHERE ppas_id = :ppasId");
        $ppasPersonnelStmt->execute([':ppasId' => $proposal['ppas_id']]);
        $ppasPersonnelCount = $ppasPersonnelStmt->fetchColumn();
        
        echo '<div style="margin-bottom: 20px;">';
        echo '<strong>PPAS Personnel Count:</strong> ' . $ppasPersonnelCount . '<br>';
        echo '</div>';
        
        // Get PPAS personnel details
        $ppasPersonnelDetailsStmt = $conn->prepare("SELECT * FROM ppas_personnel WHERE ppas_id = :ppasId");
        $ppasPersonnelDetailsStmt->execute([':ppasId' => $proposal['ppas_id']]);
        $ppasPersonnel = $ppasPersonnelDetailsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div style="margin-bottom: 20px;">';
        echo '<strong>PPAS Personnel Details:</strong><br>';
        echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Personnel ID</th><th>Role</th><th>Name</th></tr>';
        
        foreach ($ppasPersonnel as $person) {
            echo '<tr>';
            echo '<td>' . $person['id'] . '</td>';
            echo '<td>' . $person['personnel_id'] . '</td>';
            echo '<td>' . $person['role'] . '</td>';
            echo '<td>' . htmlspecialchars($person['personnel_name']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
    }
    
    // Check if personnel_list table exists and has personnel
    $stmt = $conn->query("SHOW TABLES LIKE 'personnel_list'");
    $personnelListTableExists = $stmt->rowCount() > 0;
    
    if ($personnelListTableExists) {
        $stmt = $conn->query("SELECT COUNT(*) FROM personnel_list");
        $personnelListCount = $stmt->fetchColumn();
        
        echo '<div style="margin-bottom: 20px;">';
        echo '<strong>Personnel List Table:</strong> Exists<br>';
        echo '<strong>Personnel List Count:</strong> ' . $personnelListCount . '<br>';
        echo '</div>';
    } else {
        echo '<div style="margin-bottom: 20px; color: orange;">';
        echo '<strong>Warning:</strong> The personnel_list table does not exist. This may cause issues with printing.<br>';
        echo '</div>';
    }
    
    // Test the modified print query
    $personnelSql = "SELECT gpp.id, gpp.personnel_id, gpp.role, 
                     COALESCE(pl.name, pp.personnel_name) as name, 
                     COALESCE(pl.gender, 'Unspecified') as gender,
                     ar.rank_name
                     FROM gad_proposal_personnel gpp
                     LEFT JOIN personnel_list pl ON gpp.personnel_id = pl.id
                     LEFT JOIN ppas_personnel pp ON pp.personnel_id = gpp.personnel_id AND pp.ppas_id = :ppas_id
                     LEFT JOIN academic_rank ar ON pl.academic_rank_id = ar.id
                     WHERE gpp.proposal_id = :id
                     ORDER BY gpp.role ASC, COALESCE(pl.name, pp.personnel_name) ASC";
                     
    $personnelStmt = $conn->prepare($personnelSql);
    $personnelStmt->execute([
        ':id' => $proposal['id'],
        ':ppas_id' => $proposal['ppas_id'] ?? null
    ]);
    $personnel = $personnelStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div style="margin-bottom: 20px;">';
    echo '<strong>Test Print Query Result:</strong> Found ' . count($personnel) . ' personnel<br>';
    
    if (count($personnel) > 0) {
        echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
        echo '<tr><th>ID</th><th>Personnel ID</th><th>Role</th><th>Name</th><th>Gender</th></tr>';
        
        foreach ($personnel as $person) {
            echo '<tr>';
            echo '<td>' . $person['id'] . '</td>';
            echo '<td>' . $person['personnel_id'] . '</td>';
            echo '<td>' . $person['role'] . '</td>';
            echo '<td>' . htmlspecialchars($person['name']) . '</td>';
            echo '<td>' . htmlspecialchars($person['gender']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<div style="color: orange;">';
        echo 'No personnel found with the test query. Trying direct ppas_personnel query...<br>';
        echo '</div>';
        
        if ($proposal['ppas_id']) {
            $ppasPersonnelSql = "SELECT id, personnel_id, role, personnel_name as name, 'Unspecified' as gender, NULL as rank_name
                                FROM ppas_personnel 
                                WHERE ppas_id = :ppas_id";
            $ppasPersonnelStmt = $conn->prepare($ppasPersonnelSql);
            $ppasPersonnelStmt->execute([':ppas_id' => $proposal['ppas_id']]);
            $personnel = $ppasPersonnelStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<strong>Direct PPAS Personnel Query Result:</strong> Found ' . count($personnel) . ' personnel<br>';
            
            if (count($personnel) > 0) {
                echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
                echo '<tr><th>ID</th><th>Personnel ID</th><th>Role</th><th>Name</th><th>Gender</th></tr>';
                
                foreach ($personnel as $person) {
                    echo '<tr>';
                    echo '<td>' . $person['id'] . '</td>';
                    echo '<td>' . $person['personnel_id'] . '</td>';
                    echo '<td>' . $person['role'] . '</td>';
                    echo '<td>' . htmlspecialchars($person['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($person['gender']) . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            }
        }
    }
    echo '</div>';
    
    // Add a link to print the actual proposal
    echo '<div style="margin-top: 30px;">';
    echo '<a href="print_proposal.php?id=' . $proposal['id'] . '" target="_blank" style="padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Test Print Proposal</a>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="color: red; margin-top: 20px;">';
    echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '<br>';
    echo '</div>';
    
    file_put_contents($debug_file, "Error in test script: " . $e->getMessage() . "\n", FILE_APPEND);
}
?> 
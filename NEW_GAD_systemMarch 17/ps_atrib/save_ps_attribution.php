<?php
require_once 'db_connection.php';

header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['ppaId']) || !isset($data['psAttribution'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required data'
    ]);
    exit;
}

$ppaId = intval($data['ppaId']);
$psAttribution = floatval($data['psAttribution']);

try {
    // First check if there's already a PS attribution record
    $checkStmt = $pdo->prepare("
        SELECT id 
        FROM ps_attribution 
        WHERE ppa_id = ?
    ");
    $checkStmt->execute([$ppaId]);
    $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingRecord) {
        // Update existing record
        $updateStmt = $pdo->prepare("
            UPDATE ps_attribution 
            SET ps_value = ?, 
                saved_by = ?, 
                updated_at = NOW() 
            WHERE ppa_id = ?
        ");
        
        $savedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown User';
        $updateStmt->execute([$psAttribution, $savedBy, $ppaId]);
        
        $message = 'PS Attribution updated successfully';
    } else {
        // Insert new record
        $insertStmt = $pdo->prepare("
            INSERT INTO ps_attribution 
                (ppa_id, ps_value, saved_by) 
            VALUES 
                (?, ?, ?)
        ");
        
        $savedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'Unknown User';
        $insertStmt->execute([$ppaId, $psAttribution, $savedBy]);
        
        $message = 'PS Attribution saved successfully';
    }
    
    // Also update the PS attribution field in ppa_details
    $updatePpaStmt = $pdo->prepare("
        UPDATE ppa_details 
        SET ps_attribution = ? 
        WHERE id = ?
    ");
    $updatePpaStmt->execute([$psAttribution, $ppaId]);
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} catch(PDOException $e) {
    error_log("Save PS Attribution error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 
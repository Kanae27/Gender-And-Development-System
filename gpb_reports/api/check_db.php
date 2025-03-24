<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/db_connection.php';

try {
    $conn = getConnection();
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'gpb_entries'");
    if ($tableCheck->rowCount() === 0) {
        die("Table 'gpb_entries' does not exist. Please create the table first.");
    }
    echo "Table 'gpb_entries' exists.\n";

    // Check table structure
    $columnsCheck = $conn->query("DESCRIBE gpb_entries");
    $columns = $columnsCheck->fetchAll(PDO::FETCH_COLUMN);
    echo "Table columns:\n";
    print_r($columns);

    // Check for sample data
    $dataCheck = $conn->query("SELECT COUNT(*) as count FROM gpb_entries");
    $count = $dataCheck->fetchColumn();
    echo "\nTotal records in gpb_entries: " . $count . "\n";

    // Check for specific campus and year
    $stmt = $conn->prepare("SELECT COUNT(*) FROM gpb_entries WHERE campus = ? AND year = ?");
    $stmt->execute(['Lipa', '2027']);
    $specificCount = $stmt->fetchColumn();
    echo "Records for Lipa campus and year 2027: " . $specificCount . "\n";

    // Show sample data
    $stmt = $conn->query("SELECT * FROM gpb_entries LIMIT 1");
    echo "\nSample record:\n";
    print_r($stmt->fetch(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} 
<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Determine which personnel table exists
    $personnelTable = '';
    
    // Check if personnel table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'personnel'");
    if ($stmt->rowCount() > 0) {
        $personnelTable = 'personnel';
        error_log("Using 'personnel' table");
    } else {
        // Check if personnel_list table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'personnel_list'");
        if ($stmt->rowCount() > 0) {
            $personnelTable = 'personnel_list';
            error_log("Using 'personnel_list' table");
        } else {
            throw new Exception("No personnel table found in the database");
        }
    }
    
    // Check personnel table structure to see if academic_rank_id exists
    $personnelColumns = [];
    $stmt = $conn->query("DESCRIBE {$personnelTable}");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $personnelColumns[] = $row['Field'];
    }
    error_log("Personnel columns: " . implode(", ", $personnelColumns));
    
    // Check academic_rank table structure to see which columns exist
    $academicRankColumns = [];
    $stmt = $conn->query("SHOW TABLES LIKE 'academic_rank'");
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->query("DESCRIBE academic_rank");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $academicRankColumns[] = $row['Field'];
        }
        error_log("Academic rank columns: " . implode(", ", $academicRankColumns));
    }
    
    // Build the SQL query based on available columns
    $sql = "SELECT 
        p.id, 
        p.name";
    
    // Add gender if it exists
    if (in_array('gender', $personnelColumns)) {
        $sql .= ", p.gender";
    } else {
        $sql .= ", '' as gender";
    }
    
    // Check if we can join with academic_rank table
    $hasAcademicRankId = in_array('academic_rank_id', $personnelColumns);
    $hasAcademicRankTable = count($academicRankColumns) > 0;
    
    if ($hasAcademicRankId && $hasAcademicRankTable) {
        // We can join with academic_rank table
        $sql .= ", a.rank_name as academic_rank";
        
        // Add monthly_salary and hourly_rate if they exist
        if (in_array('monthly_salary', $academicRankColumns)) {
            $sql .= ", a.monthly_salary";
        } else {
            $sql .= ", '0.00' as monthly_salary";
        }
        
        if (in_array('hourly_rate', $academicRankColumns)) {
            $sql .= ", a.hourly_rate";
        } else {
            $sql .= ", '0.00' as hourly_rate";
        }
        
        $sql .= " FROM {$personnelTable} p
        LEFT JOIN academic_rank a ON p.academic_rank_id = a.id";
    } else {
        // We can't join, so just return personnel data with default values
        $sql .= ", 'No Rank' as academic_rank, 
                  '0.00' as monthly_salary, 
                  '0.00' as hourly_rate 
                  FROM {$personnelTable} p";
    }
    
    $sql .= " ORDER BY p.name ASC";
    
    error_log("Executing query: " . $sql);
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($personnel);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
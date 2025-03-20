<?php
// For debugging, enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gad_db";

// Function to check if a table exists
function tableExists($conn, $tableName) {
    try {
        $result = $conn->query("SHOW TABLES LIKE '{$tableName}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error checking if table exists: ' . $e->getMessage()]);
        return false;
    }
}

// Function to check if a column exists in a table
function columnExists($conn, $tableName, $columnName) {
    try {
        $result = $conn->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
        return $result->rowCount() > 0;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error checking if column exists: ' . $e->getMessage()]);
        return false;
    }
}

try {
    // Connect to database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $messages = [];
    
    // Check if ppas_forms table exists
    if (!tableExists($conn, 'ppas_forms')) {
        // Create ppas_forms table if it doesn't exist
        $sql = "
            CREATE TABLE ppas_forms (
                id INT(11) NOT NULL AUTO_INCREMENT,
                year VARCHAR(4) NOT NULL,
                quarter VARCHAR(2) NOT NULL,
                gender_issue TEXT NULL,
                type VARCHAR(20) NOT NULL,
                title VARCHAR(255) NOT NULL,
                location VARCHAR(255) NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                has_lunch_break TINYINT(1) DEFAULT 0,
                has_am_break TINYINT(1) DEFAULT 0,
                has_pm_break TINYINT(1) DEFAULT 0,
                total_duration DECIMAL(10,2) NOT NULL,
                approved_budget DECIMAL(12,2) NOT NULL,
                source_of_budget VARCHAR(50) NOT NULL,
                ps_attribution DECIMAL(12,2) DEFAULT 0.00,
                created_by VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->exec($sql);
        $messages[] = "Created ppas_forms table";
    } else {
        $messages[] = "Table ppas_forms already exists";
        
        // Check and add required columns if they don't exist
        if (!columnExists($conn, 'ppas_forms', 'gender_issue')) {
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN gender_issue TEXT NULL AFTER quarter");
            $messages[] = "Added gender_issue column to ppas_forms";
        } else {
            $messages[] = "Column gender_issue already exists";
        }
        
        if (!columnExists($conn, 'ppas_forms', 'type')) {
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN type VARCHAR(20) NOT NULL AFTER gender_issue");
            $messages[] = "Added type column to ppas_forms";
        } else {
            $messages[] = "Column type already exists";
        }
        
        if (!columnExists($conn, 'ppas_forms', 'start_date')) {
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN start_date DATE NOT NULL AFTER location");
            $messages[] = "Added start_date column to ppas_forms";
        } else {
            $messages[] = "Column start_date already exists";
        }
        
        if (!columnExists($conn, 'ppas_forms', 'end_date')) {
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN end_date DATE NOT NULL AFTER start_date");
            $messages[] = "Added end_date column to ppas_forms";
        } else {
            $messages[] = "Column end_date already exists";
        }
        
        if (!columnExists($conn, 'ppas_forms', 'ps_attribution')) {
            $conn->exec("ALTER TABLE ppas_forms ADD COLUMN ps_attribution DECIMAL(12,2) DEFAULT 0.00 AFTER source_of_budget");
            $messages[] = "Added ps_attribution column to ppas_forms";
        } else {
            $messages[] = "Column ps_attribution already exists";
        }
    }
    
    // Check if ppas_personnel table exists
    if (!tableExists($conn, 'ppas_personnel')) {
        // Create ppas_personnel table if it doesn't exist
        $sql = "
            CREATE TABLE ppas_personnel (
                id INT(11) NOT NULL AUTO_INCREMENT,
                ppas_id INT(11) NOT NULL,
                personnel_id INT(11) NOT NULL,
                personnel_name VARCHAR(255) NOT NULL,
                role VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY ppas_id (ppas_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->exec($sql);
        $messages[] = "Created ppas_personnel table";
    } else {
        $messages[] = "Table ppas_personnel already exists";
    }
    
    // Check if ppas_beneficiaries table exists
    if (!tableExists($conn, 'ppas_beneficiaries')) {
        // Create ppas_beneficiaries table if it doesn't exist
        $sql = "
            CREATE TABLE ppas_beneficiaries (
                id INT(11) NOT NULL AUTO_INCREMENT,
                ppas_id INT(11) NOT NULL,
                type VARCHAR(50) NOT NULL,
                male_count INT(11) NOT NULL DEFAULT 0,
                female_count INT(11) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY ppas_id (ppas_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->exec($sql);
        $messages[] = "Created ppas_beneficiaries table";
    } else {
        $messages[] = "Table ppas_beneficiaries already exists";
    }
    
    // Check if ppas_sdgs table exists
    if (!tableExists($conn, 'ppas_sdgs')) {
        // Create ppas_sdgs table if it doesn't exist
        $sql = "
            CREATE TABLE ppas_sdgs (
                id INT(11) NOT NULL AUTO_INCREMENT,
                ppas_id INT(11) NOT NULL,
                sdg_number INT(11) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY ppas_id (ppas_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        $conn->exec($sql);
        $messages[] = "Created ppas_sdgs table";
    } else {
        $messages[] = "Table ppas_sdgs already exists";
    }
    
    // Return success message
    echo json_encode([
        'success' => true,
        'message' => 'Database structure updated successfully.',
        'details' => $messages
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 
<?php
require_once '../config.php';

// Set appropriate headers
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'data' => [],
    'error' => null,
    'debug' => []
];

try {
    // Check if PDO connection exists
    if (!isset($pdo) || !$pdo) {
        throw new Exception("Database connection not established");
    }
    
    // Get search term
    $term = isset($_GET['term']) ? trim($_GET['term']) : '';
    $response['debug']['term'] = $term;
    
    // Check if gpb_entries table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'gpb_entries'");
    $table_exists = $table_check->rowCount() > 0;
    $response['debug']['table_exists'] = $table_exists;
    
    if (!$table_exists) {
        // Create the table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS gpb_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category VARCHAR(255),
            gender_issue TEXT,
            cause_of_issue TEXT,
            gad_objective TEXT,
            relevant_agency VARCHAR(255),
            generic_activity TEXT,
            specific_activities TEXT,
            male_participants INT,
            female_participants INT,
            total_participants INT,
            gad_budget DECIMAL(15,2),
            source_of_budget VARCHAR(255),
            responsible_unit VARCHAR(255),
            campus VARCHAR(255),
            year VARCHAR(10),
            total_gaa DECIMAL(15,2),
            total_gad_fund DECIMAL(15,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($create_table_sql);
        $response['debug']['table_created'] = true;
        
        // Insert sample data
        $sample_data = [
            ['Client-Focused', 'Limited access to educational resources for female students'],
            ['Organization-Focused', 'Underrepresentation of women in leadership positions'],
            ['Gender Issue', 'Gender-based discrimination in academic opportunities'],
            ['Client-Focused', 'Limited participation of women in STEM programs'],
            ['Organization-Focused', 'Lack of gender-sensitive facilities and infrastructure'],
            ['Gender Issue', 'Gender stereotyping in course selection and career guidance'],
            ['Client-Focused', 'Harassment and bullying based on gender'],
            ['Organization-Focused', 'Unequal pay for equal work'],
            ['Gender Issue', 'Limited access to reproductive health services'],
            ['Client-Focused', 'Gender bias in teaching materials and curriculum']
        ];
        
        $insert_stmt = $pdo->prepare("INSERT INTO gpb_entries (category, gender_issue) VALUES (?, ?)");
        foreach ($sample_data as $data) {
            try {
                $insert_stmt->execute($data);
            } catch (PDOException $e) {
                // Log error but continue with other insertions
                error_log("Error inserting sample data: " . $e->getMessage());
            }
        }
        $response['debug']['sample_data_added'] = true;
    }
    
    // Base query to get distinct gender issues
    $query = "SELECT DISTINCT gender_issue FROM gpb_entries WHERE gender_issue IS NOT NULL AND gender_issue != ''";
    $params = [];
    
    // Add search condition if term is provided
    if ($term !== '') {
        $query .= " AND gender_issue LIKE ?";
        $params[] = "%$term%";
    }
    
    $query .= " ORDER BY gender_issue ASC";
    $response['debug']['query'] = $query;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $issues = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['gender_issue'])) {
            $issues[] = $row['gender_issue'];
        }
    }
    
    $response['success'] = true;
    $response['data'] = $issues;
    $response['debug']['count'] = count($issues);

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    error_log("Error in search_gender_issues.php: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
exit; 
<?php
// Start output buffering
ob_start();

// Include database connection
require_once '../includes/db_connection.php';

try {
    // Create ppas_forms table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS ppas_forms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year INT NOT NULL,
            quarter VARCHAR(2) NOT NULL,
            title VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            has_lunch_break TINYINT(1) DEFAULT 0,
            has_am_break TINYINT(1) DEFAULT 0,
            has_pm_break TINYINT(1) DEFAULT 0,
            total_duration DECIMAL(5,2) NOT NULL,
            approved_budget DECIMAL(10,2) NOT NULL,
            source_of_budget VARCHAR(50) NOT NULL,
            created_by VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Create ppas_personnel table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS ppas_personnel (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ppas_id INT NOT NULL,
            personnel_id VARCHAR(50) NOT NULL,
            personnel_name VARCHAR(255) NOT NULL,
            role ENUM('project_leader', 'asst_project_leader', 'project_staff', 'other_participant') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ppas_id) REFERENCES ppas_forms(id) ON DELETE CASCADE
        )
    ");

    // Create ppas_beneficiaries table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS ppas_beneficiaries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ppas_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            male_count INT NOT NULL DEFAULT 0,
            female_count INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ppas_id) REFERENCES ppas_forms(id) ON DELETE CASCADE
        )
    ");

    // Create ppas_sdgs table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS ppas_sdgs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ppas_id INT NOT NULL,
            sdg_number INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ppas_id) REFERENCES ppas_forms(id) ON DELETE CASCADE
        )
    ");

    // Clear any buffered output
    ob_end_clean();

    // Return success message
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Database tables created successfully']);

} catch (PDOException $e) {
    // Clear any buffered output
    ob_end_clean();

    // Log the error
    error_log("Error creating tables: " . $e->getMessage());

    // Return error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error creating database tables. Please check the error log.']);
} 
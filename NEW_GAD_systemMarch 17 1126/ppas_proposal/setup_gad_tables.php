<?php
// Start output buffering
ob_start();

// Include database connection
require_once '../includes/db_connection.php';

try {
    // Create gad_proposals table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS gad_proposals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            year INT NOT NULL,
            quarter VARCHAR(2) NOT NULL,
            proposal_type ENUM('program', 'project', 'activity') NOT NULL,
            title VARCHAR(255) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            venue VARCHAR(255) NOT NULL,
            delivery_mode ENUM('online', 'face-to-face', 'hybrid') NOT NULL,
            rationale TEXT,
            specific_objectives TEXT,
            strategies TEXT,
            sustainability_plan TEXT,
            partner_offices TEXT,
            male_beneficiaries INT DEFAULT 0,
            female_beneficiaries INT DEFAULT 0,
            total_beneficiaries INT DEFAULT 0,
            budget_source VARCHAR(50),
            total_budget DECIMAL(10,2),
            budget_breakdown TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Create gad_project_team table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS gad_project_team (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposal_id INT NOT NULL,
            role ENUM('project_leader', 'asst_project_leader', 'project_staff') NOT NULL,
            personnel_name TEXT NOT NULL,
            responsibilities TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
        )
    ");

    // Create gad_activities table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS gad_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposal_id INT NOT NULL,
            activity_title VARCHAR(255) NOT NULL,
            activity_details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
        )
    ");

    // Create gad_work_plan table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS gad_work_plan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposal_id INT NOT NULL,
            activity VARCHAR(255) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
        )
    ");

    // Create gad_monitoring table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS gad_monitoring (
            id INT AUTO_INCREMENT PRIMARY KEY,
            proposal_id INT NOT NULL,
            objectives TEXT NOT NULL,
            performance_indicators TEXT,
            baseline_data TEXT,
            performance_target TEXT,
            data_source TEXT,
            collection_method TEXT,
            collection_frequency TEXT,
            responsible_office TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (proposal_id) REFERENCES gad_proposals(id) ON DELETE CASCADE
        )
    ");

    // Clear any buffered output
    ob_end_clean();

    // Return success message
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'GAD Proposal tables created successfully']);

} catch (PDOException $e) {
    // Clear any buffered output
    ob_end_clean();

    // Log the error
    error_log("Error creating GAD tables: " . $e->getMessage());

    // Return error message
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error creating GAD database tables. Please check the error log.']);
} 
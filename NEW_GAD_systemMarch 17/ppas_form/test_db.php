<?php
require_once('../config.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Test database connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Database connection successful<br>";

    // Create programs table
    $sql_programs = "CREATE TABLE IF NOT EXISTS programs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        program_name VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";

    if ($conn->query($sql_programs) === TRUE) {
        echo "Programs table created successfully or already exists<br>";
    } else {
        throw new Exception("Error creating programs table: " . $conn->error);
    }

    // Create projects table
    $sql_projects = "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_name VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;";

    if ($conn->query($sql_projects) === TRUE) {
        echo "Projects table created successfully or already exists<br>";
    } else {
        throw new Exception("Error creating projects table: " . $conn->error);
    }

    // Test insert into programs
    $test_program = "Test Program " . date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO programs (program_name) VALUES (?)");
    $stmt->bind_param("s", $test_program);
    
    if ($stmt->execute()) {
        echo "Test program insert successful<br>";
    } else {
        throw new Exception("Error inserting test program: " . $stmt->error);
    }

    // Test insert into projects
    $test_project = "Test Project " . date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO projects (project_name) VALUES (?)");
    $stmt->bind_param("s", $test_project);
    
    if ($stmt->execute()) {
        echo "Test project insert successful<br>";
    } else {
        throw new Exception("Error inserting test project: " . $stmt->error);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 
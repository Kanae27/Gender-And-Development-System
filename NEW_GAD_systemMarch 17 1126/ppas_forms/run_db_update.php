<!DOCTYPE html>
<html>
<head>
    <title>PPAS Table Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>PPAS Table Update</h2>
        <p>This script will update the ppas_forms table to replace the 'date' column with 'start_date' and 'end_date' columns.</p>
        
        <div class="result-container">
            <?php
            ob_start(); // Start output buffering
            include 'update_ppas_table.php';
            $result = ob_get_clean(); // Get the output and clear the buffer
            
            if (strpos($result, 'Successfully') !== false) {
                echo '<div class="success">' . $result . '</div>';
            } elseif (strpos($result, 'already updated') !== false) {
                echo '<div class="info">' . $result . '</div>';
            } else {
                echo '<div class="error">' . $result . '</div>';
            }
            ?>
        </div>
        
        <div class="mt-4">
            <a href="ppas.php" class="btn btn-primary">Return to PPAS Form</a>
        </div>
    </div>
</body>
</html> 
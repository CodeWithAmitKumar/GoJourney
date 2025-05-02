<?php
// Initialize database with required tables
require_once '../connection/db_connect.php';

// Read SQL file
$sqlFilePath = '../sql/create_bookings_table.sql';

if (file_exists($sqlFilePath)) {
    $sql = file_get_contents($sqlFilePath);
    
    // Split SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    $success = true;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!mysqli_query($conn, $statement)) {
                echo "Error executing statement: " . mysqli_error($conn) . "<br>";
                echo "Statement: " . $statement . "<br><br>";
                $success = false;
            }
        }
    }
    
    if ($success) {
        echo "Database tables initialized successfully!";
    } else {
        echo "There were errors initializing the database tables. Please check the logs.";
    }
} else {
    echo "SQL file not found at: " . $sqlFilePath;
} 
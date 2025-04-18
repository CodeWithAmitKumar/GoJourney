<?php
// for authentication 

// Initial connection without database selection
$server = "localhost";
$username = "root";
$password = "";
$database = "go_journey";

// First connect without selecting the database
$conn = mysqli_connect($server, $username, $password);
if ($conn === false) {
    die("ERROR: Could not connect to MySQL server. " . mysqli_connect_error());
}

// Check if database exists, if not create it
$db_check = mysqli_query($conn, "SHOW DATABASES LIKE '$database'");
if (mysqli_num_rows($db_check) == 0) {
    $create_db = "CREATE DATABASE IF NOT EXISTS $database";
    if (!mysqli_query($conn, $create_db)) {
        die("ERROR: Could not create database. " . mysqli_error($conn));
    }
    // Log for debugging
    error_log("Database '$database' created successfully");
}

// Close initial connection
mysqli_close($conn);

// Connect to the specific database
$conn = mysqli_connect($server, $username, $password, $database);
if ($conn === false) {
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Log successful connection
error_log("Connected to database '$database' successfully");

?>
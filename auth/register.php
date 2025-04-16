<?php
session_start();
require_once '../connection/db_connect.php';

// Table exists, check if users table exists
$check_user_table = "SHOW TABLES LIKE 'users'";
$user_table_exists = mysqli_query($conn, $check_user_table);

// If users table doesn't exist, create it
if (mysqli_num_rows($user_table_exists) == 0) {
    // Create users table
    $create_users = "CREATE TABLE users (
        user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($conn, $create_users)) {
        $_SESSION['error'] = "Error creating users table: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../index.php");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $check_email);
    
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Email already exists";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Error checking email: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $insert_query = "INSERT INTO users (full_name, user_email, password_hash, created_at) VALUES ('$name', '$email', '$hashed_password', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        // Registration successful, auto-login the user
        $user_id = mysqli_insert_id($conn);
        
        // Create session
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        
        // Redirect to dashboard
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        // Registration failed
        $_SESSION['error'] = "Registration failed: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
} else {
    // Not a POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
}
?>

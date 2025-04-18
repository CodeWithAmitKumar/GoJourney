<?php
session_start();
require_once '../connection/db_connect.php';

// Add error logging
error_log("Login process started");

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Login form submitted");
    
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        error_log("Empty email or password");
        $_SESSION['error'] = "Email and password are required";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if users table exists
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($check_table) == 0) {
        error_log("Users table does not exist");
        $_SESSION['error'] = "Authentication system is not set up properly. Please contact administrator.";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if email exists in database
    $sql = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        error_log("Database query error: " . mysqli_error($conn));
        $_SESSION['error'] = "Database error. Please try again later.";
        header("Location: ../index.php");
        exit();
    }
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $row['password_hash'])) {
            error_log("User logged in successfully: $email");
            
            // Clear any existing session data
            $_SESSION = array();
            
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Password is correct, create session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['user_email'];
            
            // Store password hash version to detect changes
            $_SESSION['password_hash_version'] = md5($row['password_hash']);
            
            // Set last login time
            $_SESSION['last_activity'] = time();
            
            // Update last login time in database if you have such a field
            // mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE user_id = ".$row['user_id']);
            
            // Set success message
            $_SESSION['success'] = "Login successful! Welcome back, " . $row['full_name'] . "!";
            
            // Redirect to dashboard
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            // Password is incorrect
            error_log("Invalid password for user: $email");
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit();
        }
    } else {
        // Email not found
        error_log("Email not found: $email");
        $_SESSION['error'] = "Invalid email or password";
        header("Location: ../index.php");
        exit();
    }
} else {
    // Not a POST request, redirect to homepage
    error_log("Direct access to login.php without POST");
    header("Location: ../index.php");
    exit();
}
?>

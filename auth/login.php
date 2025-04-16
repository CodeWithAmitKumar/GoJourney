<?php
session_start();
require_once '../connection/db_connect.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if email exists in database
    $sql = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $row['password_hash'])) {
            // Password is correct, create session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['user_email'];
            
            // Redirect to dashboard or home page
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit();
        }
    } else {
        // Email not found or query error
        $_SESSION['error'] = "Invalid email or password";
        header("Location: ../index.php");
        exit();
    }
} else {
    // Not a POST request, redirect to homepage
    header("Location: ../index.php");
    exit();
}
?>

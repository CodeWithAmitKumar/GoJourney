<?php
session_start();
require_once '../connection/db_connect.php';

// Add error logging
error_log("Registration process started");

// Check if users table exists
$check_user_table = "SHOW TABLES LIKE 'users'";
$user_table_exists = mysqli_query($conn, $check_user_table);

// If users table doesn't exist, create it
if (mysqli_num_rows($user_table_exists) == 0) {
    error_log("Users table does not exist, creating it");
    
    // Create users table
    $create_users = "CREATE TABLE users (
        user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        user_email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($conn, $create_users)) {
        error_log("Error creating users table: " . mysqli_error($conn));
        $_SESSION['error'] = "Error creating users table: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
    
    error_log("Users table created successfully");
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Form submitted, processing user data");
    
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        error_log("Empty fields detected");
        $_SESSION['error'] = "All fields are required";
        header("Location: ../index.php");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: $email");
        $_SESSION['error'] = "Invalid email format";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        error_log("Passwords do not match");
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../index.php");
        exit();
    }
    
    // Check password strength
    if (strlen($password) < 6) {
        error_log("Password too short");
        $_SESSION['error'] = "Password must be at least 6 characters long";
        header("Location: ../index.php");
        exit();
    }
    
    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE user_email = '$email'";
    $result = mysqli_query($conn, $check_email);
    
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            error_log("Email already exists: $email");
            $_SESSION['error'] = "Email already exists";
            header("Location: ../index.php");
            exit();
        }
    } else {
        error_log("Error checking email: " . mysqli_error($conn));
        $_SESSION['error'] = "Error checking email: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $insert_query = "INSERT INTO users (full_name, user_email, password_hash) VALUES ('$name', '$email', '$hashed_password')";
    
    if (mysqli_query($conn, $insert_query)) {
        error_log("User registered successfully: $email");
        
        // Registration successful, auto-login the user
        $user_id = mysqli_insert_id($conn);
        
        // Create session
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        
        // Check if dashboard directory exists, if not create a basic one
        if (!file_exists('../dashboard')) {
            error_log("Dashboard directory does not exist, creating it");
            mkdir('../dashboard', 0755, true);
            
            // Create a basic dashboard index file
            $dashboard_content = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoJourney Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 20px;
        }
        .welcome-banner {
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.2);
        }
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .logout-btn {
            display: inline-block;
            background: #ff5e62;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../index.php" class="logo">GoJourney</a>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="../index.php#about">About</a>
            <a href="../index.php#services">Services</a>
            <a href="../index.php#need-help">Need Help</a>
            <a href="../index.php#contact">Contact Us</a>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="welcome-banner">
            <h1>Welcome, <?php echo isset($_SESSION["name"]) ? $_SESSION["name"] : "User"; ?>!</h1>
            <p>You have successfully logged into your GoJourney account.</p>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="dashboard-card">
            <h2>Available Trips</h2>
            <p>Your personalized journey recommendations will appear here soon.</p>
        </div>
        
        <div class="dashboard-card">
            <h2>Your Bookings</h2>
            <p>You have no active bookings at the moment.</p>
        </div>
    </div>
    
    <script>
        // Add scrolled class to navbar when scrolling
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });
    </script>
</body>
</html>';
            
            file_put_contents('../dashboard/index.php', $dashboard_content);
            error_log("Dashboard index file created");
        }
        
        // Set success message
        $_SESSION['success'] = "Registration successful! Welcome to GoJourney.";
        
        // Redirect to dashboard
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        // Registration failed
        error_log("Registration failed: " . mysqli_error($conn));
        $_SESSION['error'] = "Registration failed: " . mysqli_error($conn);
        header("Location: ../index.php");
        exit();
    }
} else {
    // Not a POST request, redirect to homepage
    error_log("Access to register.php without POST request");
    header("Location: ../index.php");
    exit();
}
?>

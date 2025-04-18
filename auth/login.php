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
            
            // Password is correct, create session
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['email'] = $row['user_email'];
            
            // Check if dashboard directory exists, if not create a basic one
            if (!file_exists('../dashboard')) {
                error_log("Dashboard directory does not exist, creating it");
                mkdir('../dashboard', 0755, true);
                
                // Create a basic dashboard index file (same as in register.php)
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

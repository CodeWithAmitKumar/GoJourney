<?php
session_start();
require_once '../connection/db_connect.php';

// If already logged in as admin, redirect to admin dashboard
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Process login form submission
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Check for admin credentials
    if ($email === "gojourneyamitk@admin.com" && $password === "Akpatra#@1234") {
        // Set admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_id'] = 1; // Default admin ID
        $_SESSION['admin_name'] = "Admin"; // Default admin name
        
        // Redirect to admin dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoJourney Admin - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #42275a, #734b6d);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            width: 400px;
            padding: 40px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .admin-badge {
            background-color: #FF5722;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 15px;
            color: #666;
        }
        
        .input-field {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .input-field:focus {
            border-color: #4a5ddb;
            outline: none;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(to right, #4776E6, #8E54E9);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            background: linear-gradient(to right, #3a61c9, #7d48d6);
            box-shadow: 0 5px 15px rgba(66, 103, 230, 0.4);
        }
        
        .error-message {
            color: #d9534f;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .back-to-site {
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .back-to-site:hover {
            color: #4a5ddb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <span class="admin-badge">ADMIN PORTAL</span>
            <h1>GoJourney Admin</h1>
            <p>Enter your credentials to access the admin dashboard</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" class="input-field" placeholder="Admin Email" required>
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="input-field" placeholder="Password" required>
            </div>
            
            <button type="submit" class="login-btn">Login to Dashboard</button>
        </form>
        
        <a href="../index.php" class="back-to-site">
            <i class="fas fa-arrow-left"></i> Back to GoJourney
        </a>
    </div>
</body>
</html> 
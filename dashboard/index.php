<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GoJourney</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .welcome-message {
            font-size: 1.5rem;
            color: #333;
        }
        
        .logout-btn {
            padding: 8px 16px;
            background: linear-gradient(90deg, #ff5f6d, #ffc371);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 95, 109, 0.2);
        }
        
        .dashboard-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="../index.php" class="logo">GoJourney</a>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="#">About</a>
            <a href="#">Services</a>
            <a href="#">ContactUs</a>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</div>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
        
        <div class="dashboard-content">
            <h2>Your Dashboard</h2>
            <p>This is your personal dashboard. You can manage your journeys and settings here.</p>
            
            <div style="margin-top: 20px;">
                <p><strong>Account Information:</strong></p>
                <p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <p>User ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
</body>
</html> 
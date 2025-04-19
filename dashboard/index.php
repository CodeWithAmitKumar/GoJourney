<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Enhanced security check - verify password hasn't changed since login
if (isset($_SESSION['password_hash_version']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT password_hash FROM users WHERE user_id = $user_id");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        $current_hash_version = md5($user['password_hash']);
        
        // If password hash has changed since login, force logout
        if ($_SESSION['password_hash_version'] !== $current_hash_version) {
            // Password has been changed, destroy session
            session_unset();
            session_destroy();
            
            // Start new session for message
            session_start();
            $_SESSION['error'] = "Your session has expired. Please login again.";
            header("Location: ../index.php");
            exit();
        }
    }
}

// Check for session timeout (optional, 30 minutes)
$session_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    
    // Start new session for message
    session_start();
    $_SESSION['error'] = "Your session has expired due to inactivity. Please login again.";
    header("Location: ../index.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GoJourney</title>
    <link rel="icon" type="image/png" href="../images/logo&svg/favicon.svg">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Modern UI styling -->
    <style>
        body {
            background-image: url('../images/background/dashboard.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-attachment: fixed !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
        
        /* Modern frosted glass UI */
        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }
        
        /* Revert navbar to original profile style */
        .navbar {
            background-color: rgba(255, 255, 255, 0.1) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            backdrop-filter: blur(10px) !important;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 1.2rem 5% !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            position: fixed !important;
            width: 100% !important;
            top: 0 !important;
            z-index: 1000 !important;
            transition: all 0.4s ease !important;
        }
        
        /* Main site logo style */
        .navbar .logo {
            font-size: 2rem !important;
            font-weight: bold !important;
            color: #333 !important;
            text-decoration: none !important;
            background: linear-gradient(90deg, #007bff, #00c6ff) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.05) !important;
            letter-spacing: 1px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            transition: transform 0.3s ease !important;
        }
        
        .navbar .logo:hover {
            transform: scale(1.05) !important;
        }
        
        /* Nav links styling like main site */
        .navbar .nav-links {
            display: flex !important;
            gap: 2.5rem !important;
        }
        
        .navbar .nav-links a {
            text-decoration: none !important;
            color: #333 !important;
            font-weight: 600 !important;
            font-size: 1.05rem !important;
            position: relative !important;
            transition: all 0.3s ease !important;
            padding: 0.5rem 0 !important;
        }
        
        .navbar .nav-links a::after {
            content: '' !important;
            position: absolute !important;
            width: 0 !important;
            height: 2px !important;
            bottom: 0 !important;
            left: 0 !important;
            background: linear-gradient(90deg, #007bff, #00c6ff) !important;
            transition: width 0.3s ease !important;
        }
        
        .navbar .nav-links a:hover {
            color: #007bff !important;
        }
        
        .navbar .nav-links a:hover::after {
            width: 100% !important;
        }
        
        /* Adjust profile icon and theme toggle */
        .navbar .theme-toggle-btn,
        .navbar .profile-icon {
            background: none !important;
            border: none !important;
            cursor: pointer !important;
            color: #222 !important;
            transition: all 0.3s ease !important;
            font-size: 1.2rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 40px !important;
            width: 40px !important;
            border-radius: 50% !important;
        }
        
        .navbar .theme-toggle-btn:hover,
        .navbar .profile-icon:hover {
            color: #007bff !important;
            background-color: rgba(255, 255, 255, 0.2) !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Make all icons in navbar darker and bigger */
        .navbar .nav-links i,
        .navbar .profile-icon i,
        .navbar .theme-toggle-btn i {
            font-size: 1.4rem !important;
            color: #000 !important; /* Pure black for maximum visibility */
            text-shadow: 0 1px 3px rgba(255, 255, 255, 0.4) !important;
            font-weight: 900 !important; /* Make icons bolder */
        }
        
        /* Special styling for dropdown */
        .dropdown-content {
            position: absolute !important;
            right: 0 !important;
            top: 50px !important;
            background-color: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            overflow: hidden !important;
            display: none !important;
            z-index: 1001 !important;
            min-width: 200px !important;
            animation: fadeIn 0.3s ease !important;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .profile-dropdown:hover .dropdown-content {
            display: block !important;
        }
        
        .dropdown-content a {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 14px 20px !important;
            color: #000 !important; /* Pure black text */
            font-weight: 500 !important;
            font-size: 1rem !important;
            border-left: 3px solid transparent !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
        }
        
        .dropdown-content a:not(:last-child) {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }
        
        .dropdown-content a:hover {
            background-color: rgba(0, 123, 255, 0.1) !important;
            color: #007bff !important;
            border-left: 3px solid #007bff !important;
        }
        
        /* Add icons to dropdown menu items */
        .dropdown-content a::before {
            font-family: 'Font Awesome 6 Free' !important;
            font-weight: 900 !important;
            font-size: 1rem !important;
            width: 20px !important;
            text-align: center !important;
            display: inline-block !important;
        }
        
        .dropdown-content a[href="profile.php"]::before {
            content: "\f007" !important; /* User icon */
            color: #007bff !important;
        }
        
        .dropdown-content a[href="settings.php"]::before {
            content: "\f013" !important; /* Gear icon */
            color: #6c757d !important;
        }
        
        .dropdown-content a[href="../auth/logout.php"]::before {
            content: "\f2f5" !important; /* Sign out icon */
            color: #dc3545 !important;
        }
        
        /* Adjust dashboard container margin for fixed navbar */
        .dashboard-container {
            margin-top: 120px !important;
            background-color: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
        }
        
        .dashboard-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        }
        
        .welcome-message {
            color: #333 !important;
            font-weight: 600 !important;
            text-shadow: none !important;
        }
        
        .user-name {
            color: #007bff !important;
            font-weight: 700 !important;
            background: none !important;
            -webkit-text-fill-color: initial !important;
            text-fill-color: initial !important;
        }
        
        .welcome-subtitle {
            color: #666 !important;
            font-weight: 400 !important;
        }
        
        .dashboard-welcome h1 {
            color: #333 !important;
            font-weight: 700 !important;
            margin-bottom: 20px !important;
        }
        
        .quote {
            background-color: rgba(255, 255, 255, 0.7) !important;
            border-radius: 8px !important;
            padding: 20px !important;
            border-left: 4px solid #007bff !important;
        }
        
        .quote p {
            color: #555 !important;
            font-style: italic !important;
        }
        
        .quote-icon {
            color: #007bff !important;
        }
        
        .dashboard-sections {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 20px !important;
            margin-top: 30px !important;
        }
        
        .user-info-card, .featured-destinations-card {
            background-color: rgba(255, 255, 255, 0.7) !important;
            border-radius: 10px !important;
            padding: 25px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
            transition: transform 0.3s ease, box-shadow 0.3s ease !important;
            flex: 1 !important;
            min-width: 250px !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
        }
        
        .user-info-card:hover, .featured-destinations-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
        }
        
        .user-info-card h3, .featured-destinations-card h3 {
            color: #333 !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            margin-bottom: 20px !important;
            font-weight: 600 !important;
        }
        
        .user-info-card h3 i, .featured-destinations-card h3 i {
            color: #007bff !important;
        }
        
        .user-info-card p, .featured-destinations-card p {
            color: #666 !important;
            margin-bottom: 15px !important;
            line-height: 1.6 !important;
        }
        
        .edit-profile-btn, .view-more-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 10px 18px !important;
            background-color: #007bff !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
        }
        
        .edit-profile-btn:hover, .view-more-btn:hover {
            background-color: #0069d9 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3) !important;
        }
        
        .search-input {
            background-color: rgba(255, 255, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) !important;
        }
        
        .search-input:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25) !important;
        }
    </style>
</head>
<body>
    <!-- Background overlay only -->
    <div class="bg-overlay"></div>
    
    <nav class="navbar">
        <a href="index.php" class="logo">GoJourney</a>
        <div class="nav-links">
            <a href="index.php" title="Home"><i class="fas fa-home"></i></a>
            <a href="#" title="Wishlist"><i class="fas fa-heart"></i></a>
            <a href="#" title="Cart"><i class="fas fa-shopping-cart"></i></a>
            <button id="theme-toggle" title="Toggle Theme" class="theme-toggle-btn">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun" style="display: none;"></i>
            </button>
            <div class="profile-dropdown">
                <a href="#" class="profile-icon" title="Profile">
                    <i class="fas fa-user-circle"></i>
                </a>
                <div class="dropdown-content">
                    <a href="profile.php">My Profile</a>
                    <a href="settings.php">Settings</a>
                    <a href="../auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-message">
                <?php 
                    $hour = date('H');
                    $greeting = '';
                    
                    if ($hour >= 5 && $hour < 12) {
                        $greeting = 'Good morning';
                    } elseif ($hour >= 12 && $hour < 18) {
                        $greeting = 'Good afternoon';
                    } else {
                        $greeting = 'Good evening';
                    }
                    
                    echo "$greeting, <span class='user-name'>" . htmlspecialchars($_SESSION['name']) . "</span>!";
                ?>
                <div class="welcome-subtitle">We're excited to have you here! Your personalized travel experience awaits. Discover new destinations, create memorable journeys, and make your travel dreams come true.</div>
            </div>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search destinations...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        
        <div class="dashboard-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-welcome">
                <h1>Welcome to Your Journey Hub</h1>
                <div class="quote">
                    <i class="fas fa-quote-left quote-icon"></i>
                    <p>"Travel isn't just about seeing new places; it's about seeing the world with new eyes."</p>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="user-info-card">
                    <h3><i class="fas fa-user-circle"></i> Account Information</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                    <a href="profile.php" class="edit-profile-btn"><i class="fas fa-edit"></i> Edit Profile</a>
                </div>
                
                <div class="featured-destinations-card">
                    <h3><i class="fas fa-globe-americas"></i> Featured Destinations</h3>
                    <p>Explore our handpicked selection of destinations perfect for your next adventure!</p>
                    <a href="#" class="view-more-btn"><i class="fas fa-arrow-right"></i> Explore Destinations</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
</body>
</html> 
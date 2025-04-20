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

// Set the timezone to handle time correctly
date_default_timezone_set('Asia/Kolkata'); // Set to your local timezone
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
</head>
<body>
    <!-- Background overlay -->
    <div class="bg-overlay"></div>
    
    <!-- Fixed header -->
    <div class="header-wrapper">
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
    </div>
    
    <!-- Main content -->
    <div class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="welcome-message">
                    <?php 
                        // Get the current hour in 24-hour format
                        $hour = (int)date('H');
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
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
</body>
</html> 
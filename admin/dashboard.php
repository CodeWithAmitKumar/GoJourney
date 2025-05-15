<?php
session_start();
require_once '../connection/db_connect.php';

// Include the table checking function
require_once 'check_booking_tables.php';

// Check if the user is logged in as admin
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Not logged in as admin, redirect to login page
    header("Location: index.php");
    exit;
}

// Check and create required tables if they don't exist
$table_check_result = check_booking_tables($conn);

// Get current admin info
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$admin_email = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@example.com';

// Initialize variables
$current_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoJourney Admin - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary-color: #4a5ddb;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            background-color: #f5f7fb;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(to bottom, #2c3e50, #1a2530);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            margin-bottom: 5px;
        }
        
        .admin-label {
            background-color: #FF5722;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .menu-title {
            color: rgba(255, 255, 255, 0.5);
            padding: 10px 20px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 2px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
        }
        
        /* Header Styles */
        .header {
            background: white;
            height: var(--header-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 5;
        }
        
        .toggle-sidebar {
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 20px;
            cursor: pointer;
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .header-right .user-profile {
            display: flex;
            align-items: center;
            margin-left: 20px;
            position: relative;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 180px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px 0;
            display: none;
            z-index: 100;
        }
        
        .user-profile:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-menu a {
            display: block;
            padding: 8px 15px;
            color: var(--dark-color);
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .dropdown-menu a:hover {
            background-color: #f5f5f5;
        }
        
        .dropdown-menu .logout {
            border-top: 1px solid #eee;
            margin-top: 5px;
            color: var(--danger-color);
        }
        
        /* Content Area */
        .content {
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .dashboard-title {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .dashboard-title h2 {
            color: var(--dark-color);
            font-size: 24px;
            font-weight: 600;
        }
        
        /* Stat Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .card-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(74, 93, 219, 0.1);
            color: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-card.user-card .card-icon {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .stat-card.hotel-card .card-icon {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }
        
        .stat-card.flight-card .card-icon {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .stat-card.train-card .card-icon {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .stat-card .stat-change {
            font-size: 14px;
            color: var(--success-color);
        }
        
        .stat-card .stat-change.negative {
            color: var(--danger-color);
        }
        
        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .content-section h3 {
            color: var(--dark-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Recent Bookings Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--dark-color);
            border-bottom: 2px solid #e9ecef;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            color: var(--secondary-color);
        }
        
        table tr:hover {
            background-color: #f5f7fb;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.completed {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .status.pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .status.cancelled {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            margin-right: 5px;
            display: inline-block;
        }
        
        .view-btn {
            background-color: var(--info-color);
        }
        
        .edit-btn {
            background-color: var(--primary-color);
        }
        
        .delete-btn {
            background-color: var(--danger-color);
        }
        
        /* Responsive Media Queries */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.push {
                margin-left: var(--sidebar-width);
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>GoJourney</h3>
            <span class="admin-label">ADMIN PANEL</span>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-title">MAIN NAVIGATION</div>
            <ul>
                <li>
                    <a href="?section=dashboard" class="<?php echo $current_section === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="?section=hotel_bookings" class="<?php echo $current_section === 'hotel_bookings' ? 'active' : ''; ?>">
                        <i class="fas fa-hotel"></i> Hotel Bookings
                    </a>
                </li>
                <li>
                    <a href="?section=flight_bookings" class="<?php echo $current_section === 'flight_bookings' ? 'active' : ''; ?>">
                        <i class="fas fa-plane"></i> Flight Bookings
                    </a>
                </li>
                <li>
                    <a href="?section=train_bookings" class="<?php echo $current_section === 'train_bookings' ? 'active' : ''; ?>">
                        <i class="fas fa-train"></i> Train Bookings
                    </a>
                </li>
            </ul>
            
            <div class="menu-title">MANAGEMENT</div>
            <ul>
                <li>
                    <a href="?section=users" class="<?php echo $current_section === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="?section=admins" class="<?php echo $current_section === 'admins' ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i> Admin Management
                    </a>
                </li>
                <li>
                    <a href="?section=hotels" class="<?php echo $current_section === 'hotels' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> Hotel Management
                    </a>
                </li>
            </ul>
            
            <div class="menu-title">SETTINGS</div>
            <ul>
                <li>
                    <a href="?section=settings" class="<?php echo $current_section === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> System Settings
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <button class="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="header-right">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo substr($admin_name, 0, 1); ?>
                    </div>
                    <span><?php echo $admin_name; ?></span>
                    
                    <div class="dropdown-menu">
                        <a href="?section=profile">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="?section=settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="logout.php" class="logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content">
            <?php
            // Include the appropriate section based on the request
            switch($current_section) {
                case 'dashboard':
                    include('sections/dashboard_content.php');
                    break;
                case 'hotel_bookings':
                    include('sections/hotel_bookings.php');
                    break;
                case 'flight_bookings':
                    include('sections/flight_bookings.php');
                    break;
                case 'train_bookings':
                    include('sections/train_bookings.php');
                    break;
                case 'users':
                    include('sections/users.php');
                    break;
                case 'admins':
                    include('sections/admins.php');
                    break;
                case 'hotels':
                    include('sections/hotels.php');
                    break;
                case 'flights':
                    include('sections/flights.php');
                    break;
                case 'trains':
                    include('sections/trains.php');
                    break;
                case 'settings':
                    include('sections/settings.php');
                    break;
                case 'profile':
                    include('sections/profile.php');
                    break;
                case 'view_booking':
                    include('sections/view_booking.php');
                    break;
                default:
                    // If section doesn't exist, include dashboard
                    include('sections/dashboard_content.php');
                    break;
            }
            ?>
        </div>
    </div>
    
    <script>
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('push');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userProfile = document.querySelector('.user-profile');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            
            if (!userProfile.contains(event.target) && dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
            }
        });
    </script>
</body>
</html> 
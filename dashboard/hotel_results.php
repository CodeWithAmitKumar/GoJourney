<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Check for session timeout
$session_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['error'] = "Your session has expired due to inactivity. Please login again.";
    header("Location: ../index.php");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Set the timezone
date_default_timezone_set('Asia/Kolkata');

// Redirect if parameters missing
if (!isset($_GET['destination']) || !isset($_GET['checkin']) || !isset($_GET['checkout'])) {
    $_SESSION['error'] = "Search parameters are missing. Please try again.";
    header("Location: index.php");
    exit();
}

// Get search parameters
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$checkIn = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkOut = isset($_GET['checkout']) ? $_GET['checkout'] : '';
$adults = isset($_GET['adults']) ? $_GET['adults'] : 2;
$children = isset($_GET['children']) ? $_GET['children'] : 0;
$rooms = isset($_GET['rooms']) ? $_GET['rooms'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Search Results - GoJourney</title>
    <link rel="icon" type="image/png" href="../images/logo&svg/favicon.svg">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="dashboard-fixes.css">
    <link rel="stylesheet" href="css/travel_search.css">
    <link rel="stylesheet" href="css/search_results.css">
    <link rel="stylesheet" href="css/visibility_enhancements.css">
    <!-- Decorative styles for search results -->
    <link rel="stylesheet" href="css/result_decorations.css">
    <!-- High contrast stylesheet (disabled by default, toggled by JavaScript) -->
    <link rel="stylesheet" href="css/high_contrast.css" id="high-contrast-stylesheet" disabled>
    <!-- Footer fix to prevent unwanted characters -->
    <link rel="stylesheet" href="css/footer-fix.css">
    <!-- Add modern UI enhancements -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Enhanced UI Styles */
        :root {
            --primary-color: #4a6da7;
            --primary-rgb: 74, 109, 167;
            --secondary-color: #f87575;
            --accent-color: #ffa41b;
            --light-gray: #f5f7fa;
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            --transition: all 0.3s ease-in-out;
        }
        
        /* Adjust background visibility */
        .bg-overlay {
            opacity: 0.12 !important; /* Reduced opacity for better visibility */
        }
        
        /* Improve content area visibility */
        .main-content {
            background-color: rgba(255, 255, 255, 0.9); /* More opaque background */
            backdrop-filter: blur(5px); /* Apply blur effect */
            -webkit-backdrop-filter: blur(5px);
            border-radius: var(--border-radius);
            padding: 20px 0;
            margin-top: 20px;
        }
        
        .results-page-container {
            background-color: rgba(255, 255, 255, 0.95); /* More visible container */
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }
        
        /* Hotel header information */
        .hotel-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .hotel-location {
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.5px;
        }
        
        /* Enhance hotel thumbnail */
        .hotel-thumbnail {
            background-color: rgba(255,255,255,0.9);
            border-radius: 8px;
            overflow: hidden;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .hotel-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Make result header more visible */
        .result-header, .hotel-result-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d4875 100%);
            color: white;
            padding: 18px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Modern card design with improved visibility */
        .result-card, .hotel-result-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: none;
        }
        
        .result-card:hover, .hotel-result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        /* Filter area improvements */
        .search-filters-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .search-summary {
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--primary-rgb), 0.05) 100%);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        /* Button styling */
        .book-now-btn, .view-hotel-btn {
            background: var(--secondary-color);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: bold;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .book-now-btn:hover, .view-hotel-btn:hover {
            background: #e45e5e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(248, 117, 117, 0.3);
        }
        
        .book-now-btn::before, .view-hotel-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .book-now-btn:hover::before, .view-hotel-btn:hover::before {
            left: 100%;
        }
        
        /* Add styling for search button and form actions */
        .search-btn {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e45e5e 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(var(--secondary-color), 0.3);
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--secondary-color), 0.4);
            background: linear-gradient(135deg, #e45e5e 0%, var(--secondary-color) 100%);
        }
        
        .search-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(var(--secondary-color), 0.3);
        }
        
        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .search-btn:hover::before {
            left: 100%;
        }
        
        .form-actions {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
        }
        
        /* Modal form styling improvements */
        .search-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            padding-top: 80px;
        }
        
        .search-modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .search-modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            transform: translateY(-20px);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
            animation: modalFadeIn 0.3s forwards;
            border: 1px solid rgba(var(--primary-rgb), 0.1);
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .search-modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 18px 20px;
            position: relative;
            text-align: center;
            border-bottom: 1px solid rgba(var(--primary-rgb), 0.1);
        }
        
        .search-modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .search-modal-body {
            padding: 25px;
            background-color: #f8f9fa;
        }
        
        .search-modal-content .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background-color: #fff;
        }
        
        .search-modal-content .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
            outline: none;
        }
        
        .search-modal-content .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .search-modal-content .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .search-modal-content label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }
        
        .search-modal-content select.form-control {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        
        /* Price and availability */
        .price {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .availability, .hotel-rating {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            transition: var(--transition);
        }
        
        .available {
            background: rgba(46, 213, 115, 0.15);
            color: #2ed573;
        }
        
        .hotel-rating {
            background: rgba(255, 164, 27, 0.15);
            color: #ffa41b;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }
        
        /* Loading animation improvements */
        .results-loading {
            text-align: center;
            padding: 40px 0;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .spinner {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 3px solid rgba(var(--primary-rgb), 0.3);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Detail sections */
        .additional-details {
            background: var(--light-gray);
            padding: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: none;
        }
        
        .details-section {
            margin-bottom: 20px;
        }
        
        .details-section h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        /* Error and no results states */
        .error-message, .no-results {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--box-shadow);
        }
        
        /* Back navigation styling */
        .back-navigation {
            margin-bottom: 20px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: white;
            padding: 8px 15px;
            border-radius: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .back-btn:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* Amenities */
        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .amenity {
            background: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .amenity i {
            color: var(--accent-color);
        }
        
        /* Modal styling */
        .booking-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }
        
        .booking-modal.show {
            opacity: 1;
            visibility: visible;
        }
        
        .booking-modal-content {
            width: 90%;
            max-width: 600px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
            overflow: hidden;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .booking-modal.show .booking-modal-content {
            transform: translateY(0);
        }
        
        .booking-modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .booking-modal-body {
            padding: 20px;
        }
        
        .booking-modal-footer {
            padding: 15px 20px;
            background: var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-cancel {
            background: #f1f2f6;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .modal-confirm {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            transition: var(--transition);
        }
        
        .modal-confirm:hover {
            background: #e45e5e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(248, 117, 117, 0.3);
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            position: absolute;
            right: 15px;
            top: 12px;
            cursor: pointer;
            transition: all 0.2s;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .close-modal:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: rotate(90deg);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .result-details {
                flex-direction: column;
            }
            
            .search-summary {
                flex-direction: column;
            }
            
            .search-route {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Background overlay -->
    <div class="bg-overlay"></div>
    
    <!-- Header -->
    <div class="header-wrapper">
        <nav class="navbar">
            <a href="index.php" class="logo">GoJourney</a>
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search destinations...">
                <i class="fas fa-search search-icon"></i>
            </div>
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
        <div class="results-page-container">
            <!-- Back navigation -->
            <div class="back-navigation">
                <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <!-- Search filters section -->
            <div class="search-filters-container">
                <div class="search-summary">
                    <div class="search-route">
                        <div class="destination">
                            <div class="destination-name"><?php echo htmlspecialchars($destination); ?></div>
                        </div>
                        <div class="date-guests">
                            <div class="search-date"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($checkIn); ?> - <?php echo htmlspecialchars($checkOut); ?></div>
                            <div class="search-guests"><i class="fas fa-user"></i> <?php echo htmlspecialchars($adults); ?> Adult<?php echo $adults > 1 ? 's' : ''; ?><?php echo $children > 0 ? ', ' . htmlspecialchars($children) . ' Child' . ($children > 1 ? 'ren' : '') : ''; ?></div>
                            <div class="search-rooms"><i class="fas fa-door-open"></i> <?php echo htmlspecialchars($rooms); ?> Room<?php echo $rooms > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                    <div class="search-modify">
                        <button class="modify-search-btn"><i class="fas fa-edit"></i> Modify Search</button>
                    </div>
                </div>
                
                <div class="search-filters">
                    <div class="filter-group">
                        <label>Price Range</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" checked> All Prices
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Budget (Under ₹2,000)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Mid-range (₹2,000 - ₹5,000)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Luxury (₹5,000+)
                            </label>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Star Rating</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" checked> All Ratings
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> 5 Star
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> 4 Star
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> 3 Star
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> 2 Star & Below
                            </label>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Amenities</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox"> Free WiFi
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Swimming Pool
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Gym
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Restaurant
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Free Breakfast
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search results container -->
            <div id="results-container" class="results-container">
                <div class="results-loading">
                    <div class="spinner"></div>
                    <p>Searching for hotels in <?php echo htmlspecialchars($destination); ?>...</p>
                </div>
            </div>
            
            <!-- Modify search modal -->
            <div id="modify-search-modal" class="search-modal">
                <div class="search-modal-content">
                    <div class="search-modal-header">
                        <h3>Modify Your Search</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="search-modal-body">
                        <form id="modify-search-form" action="hotel_results.php" method="GET">
                            <div class="form-group">
                                <label for="destination">Destination</label>
                                <input type="text" id="destination" name="destination" class="form-control" value="<?php echo htmlspecialchars($destination); ?>" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="checkin">Check-in</label>
                                    <input type="date" id="checkin" name="checkin" class="form-control" value="<?php echo htmlspecialchars($checkIn); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="checkout">Check-out</label>
                                    <input type="date" id="checkout" name="checkout" class="form-control" value="<?php echo htmlspecialchars($checkOut); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="adults">Adults</label>
                                    <select id="adults" name="adults" class="form-control">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (int)$adults === $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> Adult<?php echo $i > 1 ? 's' : ''; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="children">Children</label>
                                    <select id="children" name="children" class="form-control">
                                        <?php for ($i = 0; $i <= 6; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (int)$children === $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> Child<?php echo $i > 1 || $i === 0 ? 'ren' : ''; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rooms">Rooms</label>
                                <select id="rooms" name="rooms" class="form-control">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (int)$rooms === $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Room<?php echo $i > 1 ? 's' : ''; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="search-btn">Update Search</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer Section -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-row">
                <div class="footer-col about-company">
                    <h4>About GoJourney</h4>
                    <p>GoJourney helps you discover and book amazing travel experiences across India. We make travel planning simple and stress-free.</p>
                    <div class="social-links">
                    <a href="https://x.com/Codewith_amit?t=wzxyQYtIqyK_JnFzeww4uQ&s=09" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/thatodiapila?igsh=MXRyeXBjZ2l2ZXduZQ==" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="https://github.com/CodeWithAmitKumar" class="social-icon"><i class="fab fa-github"></i></a>
                    <a href="https://www.linkedin.com/in/amit-web-developer/" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-col quick-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Destinations</a></li>
                        <li><a href="#">Packages</a></li>
                        <li><a href="#">Travel Guides</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-col resources">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="#">Travel Blog</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Cancellation Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="copyright-section">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> GoJourney. All Rights Reserved. Designed with <i class="fas fa-heart"></i> in India</p>
            </div>
        </div>
    </footer>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
    <script src="dashboard-fixes.js"></script>
    <script src="js/travel_search.js"></script>
    <script src="js/visibility_enhancements.js"></script>
    <!-- Result animations -->
    <script src="js/result_animations.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch hotel results
            fetchHotelResults();
            
            // Modal functionality for modifying search
            const modifySearchBtn = document.querySelector('.modify-search-btn');
            const modifySearchModal = document.getElementById('modify-search-modal');
            const closeModalBtn = modifySearchModal.querySelector('.close-modal');
            
            modifySearchBtn.addEventListener('click', function() {
                modifySearchModal.classList.add('show');
            });
            
            closeModalBtn.addEventListener('click', function() {
                modifySearchModal.classList.remove('show');
            });
            
            window.addEventListener('click', function(e) {
                if (e.target === modifySearchModal) {
                    modifySearchModal.classList.remove('show');
                }
            });
            
            // Set minimum date for date inputs
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').setAttribute('min', today);
            document.getElementById('checkout').setAttribute('min', today);
            
            // Filter functionality
            const filterCheckboxes = document.querySelectorAll('.filter-checkbox input');
            filterCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    console.log('Filter changed:', this.parentElement.textContent.trim());
                    filterResults();
                });
            });
        });
        
        function filterResults() {
            const resultCards = document.querySelectorAll('.hotel-result-card');
            // Implement actual filtering logic here
            
            // For demonstration, add a filtered animation
            resultCards.forEach(card => {
                card.classList.add('filtered-animation');
                setTimeout(() => {
                    card.classList.remove('filtered-animation');
                }, 1000);
            });
        }
        
        function fetchHotelResults() {
            // Get search parameters from URL
            const urlParams = new URLSearchParams(window.location.search);
            const destination = urlParams.get('destination');
            const checkIn = urlParams.get('checkin');
            const checkOut = urlParams.get('checkout');
            const adults = urlParams.get('adults');
            const children = urlParams.get('children');
            const rooms = urlParams.get('rooms');
            
            // Create form data for the API request
            const formData = new FormData();
            formData.append('destination', destination);
            formData.append('checkin', checkIn);
            formData.append('checkout', checkOut);
            if (adults) formData.append('adults', adults);
            if (children) formData.append('children', children);
            if (rooms) formData.append('rooms', rooms);
            
            // Show enhanced loading animation
            document.querySelector('.results-loading').innerHTML = `
                <div class="spinner"></div>
                <p class="animate__animated animate__fadeIn">Searching for the best hotels in ${destination}...</p>
                <div class="loading-tips animate__animated animate__fadeIn animate__delay-1s">
                    <p><i class="fas fa-lightbulb"></i> Tip: Hotels with higher ratings typically offer better service</p>
                </div>
            `;
            
            // Make API request
            fetch('api/hotel_search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Hide loading indicator
                document.querySelector('.results-loading').style.display = 'none';
                
                // Display results
                displayHotelResults(data);
            })
            .catch(error => {
                console.error('Error fetching hotel results:', error);
                document.querySelector('.results-loading').style.display = 'none';
                document.getElementById('results-container').innerHTML = `
                    <div class="error-message animate__animated animate__fadeIn">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Sorry, we couldn't fetch the hotel results</h3>
                        <p>There seems to be a connection issue with our hotel search service.</p>
                        <button class="retry-btn" onclick="fetchHotelResults()">
                            <i class="fas fa-redo"></i> Retry Search
                        </button>
                    </div>
                `;
            });
        }
        
        function displayHotelResults(data) {
            const resultsContainer = document.getElementById('results-container');
            
            if (!data.hotels || data.hotels.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="no-results">
                        <i class="fas fa-hotel"></i>
                        <h3>No hotels found for this search</h3>
                        <p>Please try different dates or destination.</p>
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="results-header">
                    <h3><i class="fas fa-hotel"></i> Available Hotels</h3>
                    <div class="results-count">${data.count} hotels found</div>
                </div>
                <div class="results-list hotel-results-list">
            `;
            
            data.hotels.forEach(hotel => {
                // Calculate nights
                const checkIn = new Date(hotel.checkin_date);
                const checkOut = new Date(hotel.checkout_date);
                const nights = Math.round((checkOut - checkIn) / (1000 * 60 * 60 * 24));
                
                // Format star rating
                const starRating = parseInt(hotel.star_rating);
                let stars = '';
                for (let i = 0; i < starRating; i++) {
                    stars += '<i class="fas fa-star"></i>';
                }
                
                html += `
                    <div class="hotel-result-card">
                        <div class="hotel-image">
                            <img src="${hotel.image}" alt="${hotel.name}" onerror="this.src='../images/hotels/default-hotel.jpg'">
                            ${hotel.discount ? `<div class="discount-badge">${hotel.discount}% OFF</div>` : ''}
                        </div>
                        <div class="hotel-info">
                            <div class="hotel-header">
                                <h3 class="hotel-name">${hotel.name}</h3>
                                <div class="hotel-rating">
                                    <div class="stars">${stars}</div>
                                    <div class="review-score">${hotel.review_score}/10</div>
                                </div>
                            </div>
                            <div class="hotel-location">
                                <i class="fas fa-map-marker-alt"></i> ${hotel.location}
                                ${hotel.distance_from_center ? `<span class="distance">(${hotel.distance_from_center} from center)</span>` : ''}
                            </div>
                            <div class="hotel-amenities">
                                ${hotel.amenities.map(amenity => `<span class="amenity"><i class="fas fa-check"></i> ${amenity}</span>`).join('')}
                            </div>
                            <div class="hotel-description">
                                ${hotel.description}
                            </div>
                        </div>
                        <div class="hotel-price-booking">
                            <div class="price-info">
                                <div class="price">₹${hotel.price_per_night}</div>
                                <div class="price-details">per night</div>
                                <div class="total-price">₹${hotel.price_per_night * nights} total for ${nights} night${nights > 1 ? 's' : ''}</div>
                                ${hotel.free_cancellation ? '<div class="free-cancellation"><i class="fas fa-check-circle"></i> Free cancellation</div>' : ''}
                            </div>
                            <button class="book-now-btn" data-hotel-id="${hotel.id}">Book Now</button>
                            <button class="view-details-btn" data-toggle="hotel-details-${hotel.id}">View Details</button>
                        </div>
                        <div class="additional-details" id="hotel-details-${hotel.id}">
                            <div class="details-section">
                                <h4>Room Options</h4>
                                <div class="room-options">
                                    ${hotel.room_options.map(room => `
                                        <div class="room-option">
                                            <div class="room-name">${room.name}</div>
                                            <div class="room-details">
                                                <span class="room-size"><i class="fas fa-expand-arrows-alt"></i> ${room.size}</span>
                                                <span class="room-capacity"><i class="fas fa-user"></i> ${room.capacity}</span>
                                                ${room.breakfast_included ? '<span class="breakfast-included"><i class="fas fa-coffee"></i> Breakfast included</span>' : ''}
                                            </div>
                                            <div class="room-price">₹${room.price} per night</div>
                                            <button class="select-room-btn" data-room-id="${room.id}">Select</button>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            <div class="details-section">
                                <h4>Hotel Policies</h4>
                                <div class="hotel-policies">
                                    <div class="policy-item">
                                        <span class="policy-label">Check-in:</span>
                                        <span class="policy-value">${hotel.checkin_time || '2:00 PM'}</span>
                                    </div>
                                    <div class="policy-item">
                                        <span class="policy-label">Check-out:</span>
                                        <span class="policy-value">${hotel.checkout_time || '12:00 PM'}</span>
                                    </div>
                                    <div class="policy-item">
                                        <span class="policy-label">Cancellation:</span>
                                        <span class="policy-value">${hotel.cancellation_policy || 'Free cancellation up to 24 hours before check-in'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            resultsContainer.innerHTML = html;
            
            // Add event listeners to details buttons
            const detailButtons = resultsContainer.querySelectorAll('.view-details-btn');
            detailButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const detailsId = this.getAttribute('data-toggle');
                    const detailsSection = document.getElementById(detailsId);
                    
                    if (detailsSection.style.display === 'block') {
                        detailsSection.style.display = 'none';
                        this.classList.remove('active');
                    } else {
                        detailsSection.style.display = 'block';
                        this.classList.add('active');
                    }
                });
            });
            
            // Add event listeners to book buttons
            const bookButtons = resultsContainer.querySelectorAll('.book-now-btn, .select-room-btn');
            bookButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const hotelId = this.getAttribute('data-hotel-id');
                    const roomId = this.getAttribute('data-room-id');
                    
                    showBookingModal('hotel', hotelId, roomId);
                });
            });
        }
        
        // Helper function to show booking modal
        function showBookingModal(type, hotelId, roomId) {
            // Create modal if it doesn't exist
            let modal = document.querySelector('.booking-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'booking-modal';
                document.body.appendChild(modal);
            }
            
            const urlParams = new URLSearchParams(window.location.search);
            const checkIn = urlParams.get('checkin');
            const checkOut = urlParams.get('checkout');
            const adults = urlParams.get('adults');
            const children = urlParams.get('children');
            const rooms = urlParams.get('rooms');
            
            let modalContent = `
                <div class="booking-modal-content">
                    <div class="booking-modal-header">
                        <h3>Confirm Your Hotel Booking</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="booking-modal-body">
                        <p>You are about to book a hotel:</p>
                        <div class="booking-details">
                            <p><strong>Hotel ID:</strong> <span>${hotelId}</span></p>
                            ${roomId ? `<p><strong>Room ID:</strong> <span>${roomId}</span></p>` : ''}
                            <p><strong>Check-in:</strong> <span>${checkIn}</span></p>
                            <p><strong>Check-out:</strong> <span>${checkOut}</span></p>
                            <p><strong>Guests:</strong> <span>${adults} Adult${adults > 1 ? 's' : ''}${children > 0 ? ', ' + children + ' Child' + (children > 1 ? 'ren' : '') : ''}</span></p>
                            <p><strong>Rooms:</strong> <span>${rooms}</span></p>
                        </div>
                        <p>This booking feature will be fully implemented in the next phase.</p>
                    </div>
                    <div class="booking-modal-footer">
                        <button class="modal-cancel">Cancel</button>
                        <button class="modal-confirm">Proceed to Booking</button>
                    </div>
                </div>
            `;
            
            modal.innerHTML = modalContent;
            
            // Show the modal
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            
            // Add event listeners to buttons
            modal.querySelector('.close-modal').addEventListener('click', () => {
                closeModal(modal);
            });
            
            modal.querySelector('.modal-cancel').addEventListener('click', () => {
                closeModal(modal);
            });
            
            modal.querySelector('.modal-confirm').addEventListener('click', () => {
                alert('Thank you for your booking request! This functionality will be implemented in the next phase.');
                closeModal(modal);
            });
            
            // Close when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeModal(modal);
                }
            });
        }
        
        // Helper function to close modal
        function closeModal(modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                if (modal && modal.parentNode) {
                    document.body.removeChild(modal);
                }
            }, 300);
        }
    </script>
    <!-- Add cleanup script for any unwanted text nodes -->
    <script>
        // Function to clean up unwanted text nodes or elements
        function cleanupUnwantedNodes() {
            // Clean up any direct text children of body
            Array.from(document.body.childNodes).forEach(node => {
                if (node.nodeType === 3 && node.textContent.trim() !== '') {
                    node.textContent = '';
                }
            });
            
            // Clean up any elements that come after the main content
            const mainContent = document.querySelector('.results-page-container');
            if (mainContent) {
                let currentNode = mainContent.nextSibling;
                while (currentNode) {
                    const nextNode = currentNode.nextSibling;
                    if (currentNode.nodeType === 3 || (currentNode.nodeType === 1 && 
                        !currentNode.classList.contains('toast-container') && 
                        currentNode.tagName !== 'SCRIPT')) {
                        if (currentNode.parentNode) {
                            currentNode.parentNode.removeChild(currentNode);
                        }
                    }
                    currentNode = nextNode;
                }
            }
        }
        
        // Run cleanup when DOM is loaded and after a delay
        document.addEventListener('DOMContentLoaded', function() {
            cleanupUnwantedNodes();
            setTimeout(cleanupUnwantedNodes, 500);
        });
        
        // Run once more when everything is loaded
        window.addEventListener('load', function() {
            cleanupUnwantedNodes();
            setTimeout(cleanupUnwantedNodes, 1000);
        });
    </script>
</body>
</html> 
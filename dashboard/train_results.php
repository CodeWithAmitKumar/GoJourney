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
date_default_timezone_set('Asia/Kolkata');

// Redirect if no search parameters are provided
if (!isset($_GET['from']) || !isset($_GET['to']) || !isset($_GET['date'])) {
    $_SESSION['error'] = "Search parameters are missing. Please try again.";
    header("Location: index.php");
    exit();
}

// Get search parameters from URL
$fromStation = isset($_GET['from']) ? $_GET['from'] : '';
$toStation = isset($_GET['to']) ? $_GET['to'] : '';
$travelDate = isset($_GET['date']) ? $_GET['date'] : '';
$travelClass = isset($_GET['class']) ? $_GET['class'] : '';
$passengers = isset($_GET['passengers']) ? $_GET['passengers'] : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Search Results - GoJourney</title>
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
        
        /* Train header information */
        .train-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        
        .train-number {
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
        
        /* Enhance train icon */
        .train-icon {
            background-color: rgba(255,255,255,0.9);
            border-radius: 8px;
            padding: 12px;
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .train-icon i {
            font-size: 30px;
            color: var(--primary-color);
        }
        
        /* Make result header more visible */
        .result-header, .train-result-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2d4875 100%);
            color: white;
            padding: 18px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Modern card design with improved visibility */
        .result-card, .train-result-card {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: 1.5rem;
            overflow: hidden;
            border: none;
        }
        
        .result-card:hover, .train-result-card:hover {
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

        /* Journey timeline */
        .journey-times {
            position: relative;
            margin: 20px 0;
            display: flex;
            align-items: center;
        }
        
        .duration-line {
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            position: relative;
            flex-grow: 1;
            margin: 0 15px;
        }
        
        .duration-line::before, .duration-line::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--primary-color);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .duration-line::before {
            left: 0;
        }
        
        .duration-line::after {
            right: 0;
            background: var(--accent-color);
        }
        
        /* Button styling */
        .book-now-btn, .view-seats-btn {
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
        
        .book-now-btn:hover, .view-seats-btn:hover {
            background: #e45e5e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(248, 117, 117, 0.3);
        }
        
        .book-now-btn::before, .view-seats-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .book-now-btn:hover::before, .view-seats-btn:hover::before {
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
        
        .availability, .train-class {
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
        
        .train-class {
            background: rgba(74, 109, 167, 0.15);
            color: var(--primary-color);
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
            
            .journey-times {
                flex-direction: column;
                gap: 15px;
            }
            
            .duration-line {
                width: 3px;
                height: 50px;
                margin: 10px 0;
            }
            
            .duration-line::before, .duration-line::after {
                left: 50%;
                transform: translate(-50%, 0);
            }
            
            .duration-line::before {
                top: 0;
            }
            
            .duration-line::after {
                top: auto;
                bottom: 0;
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
    
    <!-- Fixed header -->
    <div class="header-wrapper">
        <nav class="navbar">
            <a href="index.php" class="logo">GoJourney</a>
            
            <!-- Search container moved to navbar -->
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
                        <div class="from-to">
                            <div class="station"><?php echo htmlspecialchars($fromStation); ?></div>
                            <div class="journey-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                            <div class="station"><?php echo htmlspecialchars($toStation); ?></div>
                        </div>
                        <div class="date-passengers">
                            <div class="search-date"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($travelDate); ?></div>
                            <div class="search-passengers"><i class="fas fa-user"></i> <?php echo htmlspecialchars($passengers); ?> passenger<?php echo $passengers > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                    <div class="search-modify">
                        <button class="modify-search-btn"><i class="fas fa-edit"></i> Modify Search</button>
                    </div>
                </div>
                
                <div class="search-filters">
                    <div class="filter-group">
                        <label>Train Type</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" checked> All
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Express
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> SuperFast
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Passenger
                            </label>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Departure Time</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" checked> All
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Morning (6AM - 12PM)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Afternoon (12PM - 6PM)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Evening (6PM - 12AM)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Night (12AM - 6AM)
                            </label>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label>Class</label>
                        <div class="filter-options">
                            <label class="filter-checkbox">
                                <input type="checkbox" checked> All
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> Sleeper (SL)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> AC 3 Tier (3A)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> AC 2 Tier (2A)
                            </label>
                            <label class="filter-checkbox">
                                <input type="checkbox"> AC 1st Class (1A)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search results container -->
            <div id="results-container" class="results-container">
                <div id="loading-spinner" class="results-loading">
                    <div class="spinner"></div>
                    <p>Searching for trains from <?php echo htmlspecialchars($fromStation); ?> to <?php echo htmlspecialchars($toStation); ?>...</p>
                </div>
            </div>
            
            <!-- Modify search modal (hidden by default) -->
            <div id="modify-search-modal" class="search-modal">
                <div class="search-modal-content">
                    <div class="search-modal-header">
                        <h3>Modify Your Search</h3>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="search-modal-body">
                        <form id="modify-search-form" action="train_results.php" method="GET">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="from">From</label>
                                    <input type="text" id="from" name="from" class="form-control" value="<?php echo htmlspecialchars($fromStation); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="to">To</label>
                                    <input type="text" id="to" name="to" class="form-control" value="<?php echo htmlspecialchars($toStation); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date">Travel Date</label>
                                    <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($travelDate); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="class">Class</label>
                                    <select id="class" name="class" class="form-control">
                                        <option value="SL" <?php echo $travelClass === 'SL' ? 'selected' : ''; ?>>Sleeper</option>
                                        <option value="3A" <?php echo $travelClass === '3A' ? 'selected' : ''; ?>>AC 3 Tier</option>
                                        <option value="2A" <?php echo $travelClass === '2A' ? 'selected' : ''; ?>>AC 2 Tier</option>
                                        <option value="1A" <?php echo $travelClass === '1A' ? 'selected' : ''; ?>>AC First Class</option>
                                        <option value="CC" <?php echo $travelClass === 'CC' ? 'selected' : ''; ?>>Chair Car</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="passengers">Passengers</label>
                                <select id="passengers" name="passengers" class="form-control">
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo (int)$passengers === $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?>
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
            // Extract search parameters from URL
            const urlParams = new URLSearchParams(window.location.search);
            const from = urlParams.get('from');
            const to = urlParams.get('to');
            const depart = urlParams.get('date');
            const trainClass = urlParams.get('class') || '';
            const adults = urlParams.get('passengers') || 1;
            
            console.log('Search parameters:', { from, to, depart, trainClass, adults });
            
            // Check if loading-spinner element exists
            const loadingSpinner = document.getElementById('loading-spinner');
            if (!loadingSpinner) {
                console.error('Loading spinner element not found!');
            } else {
                // Show the spinner while loading
                loadingSpinner.style.display = 'flex';
            }
            
            // Make AJAX call to get real-time train data
            const formData = new FormData();
            formData.append('from', from);
            formData.append('to', to);
            formData.append('depart', depart);
            formData.append('date', depart); // Add date parameter for compatibility
            formData.append('class', trainClass);
            formData.append('passengers', adults);
            
            console.log('Sending search request with parameters:', {
                from, to, depart, trainClass, adults
            });
            
            // Show debugging message in UI for transparency
            const resultsContainer = document.getElementById('results-container');
            resultsContainer.innerHTML = `
                <div class="loading-results">
                    <div class="spinner"></div>
                    <p>Searching for trains from ${from} to ${to} on ${depart}...</p>
                    <p><small>If results don't appear, please check console for errors</small></p>
                </div>
            `;
            
            // For direct debugging, make a test call to see if the API endpoint is reachable
            fetch('../dashboard/api/train_search.php', {
                method: 'HEAD'
            })
            .then(response => {
                console.log('API endpoint reachable:', response.ok, 'Status:', response.status);
            })
            .catch(error => {
                console.error('API endpoint not reachable:', error);
            });
            
            // Main API call
            fetch('../dashboard/api/train_search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                
                // Get the raw response text for debugging
                return response.text().then(text => {
                    console.log('Raw response:', text.substring(0, 500) + '...');
                    try {
                        // Try to parse as JSON
                        const data = JSON.parse(text);
                        return data;
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        console.error('Response was not valid JSON:', text);
                        throw new Error('Failed to parse JSON response');
                    }
                });
            })
            .then(data => {
                console.log('Received data:', data);
                
                // Hide the spinner after data is loaded
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                
                if (data.error) {
                    console.error('Error fetching train data:', data.error);
                    document.getElementById('results-container').innerHTML = `
                        <div class="alert alert-danger">
                            <p>Sorry, we couldn't find any trains for your search. Please try different dates or locations.</p>
                            <p>Error: ${data.error}</p>
                        </div>
                    `;
                    return;
                }
                
                if (!data.trains || data.count === 0) {
                    console.log('No trains found');
                    document.getElementById('results-container').innerHTML = `
                        <div class="alert alert-info">
                            <p>No trains found for this route on the selected date. Please try different dates or locations.</p>
                        </div>
                    `;
                    return;
                }
                
                // Generate HTML for each train
                const trainsHTML = data.trains.map(train => {
                    console.log('Processing train:', train);
                    
                    // Format prices for different classes
                    const prices = train.price;
                    
                    // Determine status and corresponding class
                    let statusClass = 'text-success';
                    if (train.status && train.status.toLowerCase().includes('delay')) {
                        statusClass = 'text-warning';
                    } else if (train.status && train.status.toLowerCase().includes('cancel')) {
                        statusClass = 'text-danger';
                    }
                    
                    // Create HTML for each available class
                    const classesHTML = train.class_types.map(classType => {
                        const price = prices[classType];
                        const seatsAvailable = train.seats_available[classType];
                        
                        let availabilityClass = 'text-success';
                        let availabilityText = 'Available';
                        
                        if (seatsAvailable <= 0) {
                            availabilityClass = 'text-danger';
                            availabilityText = 'Sold Out';
                        } else if (seatsAvailable <= 10) {
                            availabilityClass = 'text-warning';
                            availabilityText = 'Few Seats Left';
                        }
                        
                        const formattedPrice = new Intl.NumberFormat('en-IN', {
                            style: 'currency',
                            currency: 'INR',
                            maximumFractionDigits: 0
                        }).format(price);
                        
                        return `
                        <div class="col-md-3 mb-2">
                            <div class="card class-card">
                                <div class="card-body">
                                    <h5 class="card-title">${classType}</h5>
                                    <p class="card-text price">${formattedPrice}</p>
                                    <p class="card-text ${availabilityClass}">
                                        ${availabilityText} (${seatsAvailable})
                                    </p>
                                    <button class="btn btn-primary btn-sm book-btn" 
                                        data-train-id="${train.train_number}" 
                                        data-class="${classType}" 
                                        ${seatsAvailable <= 0 ? 'disabled' : ''}>
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    }).join('');
                    
                    return `
                    <div class="card mb-4 train-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">${train.train_name}</h5>
                                <small class="text-muted">${train.train_number} • ${train.train_type}</small>
                            </div>
                            <span class="badge ${statusClass.includes('success') ? 'bg-success' : 
                                              statusClass.includes('warning') ? 'bg-warning' : 'bg-danger'}">
                                ${train.status}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="departure">
                                        <div class="time fw-bold fs-4">${train.departure_time}</div>
                                        <div class="station">${train.from_station} (${train.from_station_code})</div>
                                        <div class="date">${train.date}</div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="duration text-muted mb-2">
                                        <i class="fas fa-clock me-1"></i> ${train.duration}
                                    </div>
                                    <div class="journey-line">
                                        <hr>
                                        <i class="fas fa-train"></i>
                                    </div>
                                    <div class="distance text-muted mt-2">
                                        <i class="fas fa-route me-1"></i> ${train.distance}
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="arrival">
                                        <div class="time fw-bold fs-4">${train.arrival_time}</div>
                                        <div class="station">${train.to_station} (${train.to_station_code})</div>
                                        <div class="date">${train.date}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="runs-on mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i> Runs on: ${train.days_of_run}
                                </small>
                            </div>
                            <hr>
                            <h6 class="mb-3">Available Classes & Pricing</h6>
                            <div class="row">
                                ${classesHTML}
                            </div>
                        </div>
                    </div>`;
                }).join('');
                
                console.log('Generated HTML for', data.trains.length, 'trains');
                
                // Update the results container
                document.getElementById('results-container').innerHTML = trainsHTML;
                console.log('Updated results container with trains HTML');
                
                // Add event listeners to all book buttons
                document.querySelectorAll('.book-btn').forEach(button => {
                    button.addEventListener('click', function(e) {
                        const trainId = this.getAttribute('data-train-id');
                        const trainClass = this.getAttribute('data-class');
                        // Redirect to booking page with train details
                        window.location.href = `booking.php?type=train&id=${trainId}&from=${from}&to=${to}&date=${depart}&class=${trainClass}&passengers=${adults}`;
                    });
                });
            })
            .catch(error => {
                console.error('Error:', error);
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
                document.getElementById('results-container').innerHTML = `
                    <div class="alert alert-danger">
                        <p>Sorry, there was an error processing your request. Please try again later.</p>
                        <p>${error.message}</p>
                    </div>
                `;
            });
            
            // Update search form with current parameters
            if (document.getElementById('from')) {
                document.getElementById('from').value = from;
            }
            if (document.getElementById('to')) {
                document.getElementById('to').value = to;
            }
            if (document.getElementById('date')) {
                document.getElementById('date').value = depart;
            }
            if (document.getElementById('class')) {
                document.getElementById('class').value = trainClass;
            }
            if (document.getElementById('passengers')) {
                document.getElementById('passengers').value = adults;
            }
            
            // Initialize the modify search modal
            const searchModal = document.getElementById('modify-search-modal');
            const modifySearchBtn = document.querySelector('.modify-search-btn');
            
            if (modifySearchBtn && searchModal) {
                modifySearchBtn.addEventListener('click', function() {
                    searchModal.classList.add('show');
                });
                
                // Close modal when clicking on close button
                const closeBtn = searchModal.querySelector('.close-modal');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        searchModal.classList.remove('show');
                    });
                }
                
                // Close modal when clicking outside content
                searchModal.addEventListener('click', function(e) {
                    if (e.target === searchModal) {
                        searchModal.classList.remove('show');
                    }
                });
            } else {
                console.error('Modal elements not found:', {
                    searchModal: Boolean(searchModal),
                    modifySearchBtn: Boolean(modifySearchBtn)
                });
            }
        });
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
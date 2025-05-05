<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Include BookingManager class
require_once '../includes/BookingManager.php';

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

// Get selected tab/filter
$activeFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Initialize BookingManager
$bookingManager = new BookingManager($conn);

// Get user's bookings from database
$userId = $_SESSION['user_id'];
$bookings = $bookingManager->getUserBookings($userId, $activeFilter);

// If no bookings found, try to get them from session (for compatibility during transition)
if (empty($bookings) && isset($_SESSION['booking_confirmed'])) {
    $confirmedBooking = $_SESSION['booking_confirmed'];
    $newBooking = [
        'booking_id' => $confirmedBooking['booking_id'] ?? null,
        'booking_reference' => $confirmedBooking['reference'],
        'booking_type' => $confirmedBooking['booking_details']['type'],
        'from_location' => $confirmedBooking['booking_details']['from'],
        'to_location' => $confirmedBooking['booking_details']['to'],
        'journey_date' => $confirmedBooking['booking_details']['date'],
        'booking_date' => $confirmedBooking['payment_date'],
        'pnr' => $confirmedBooking['pnr'],
        'total_passengers' => count($confirmedBooking['passenger_details']),
        'booking_status' => 'confirmed',
        'amount' => $confirmedBooking['amount']
    ];
    
    $bookings[] = $newBooking;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - GoJourney</title>
    <link rel="icon" type="image/png" href="../images/logo&svg/favicon.svg">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="css/visibility_enhancements.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .bookings-container {
            max-width: 1100px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .bookings-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 20px 30px;
        }
        
        .bookings-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .bookings-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .bookings-body {
            padding: 30px;
        }
        
        .booking-tabs {
            display: flex;
            border-bottom: 1px solid #eee;
            margin-bottom: 25px;
        }
        
        .booking-tab {
            padding: 12px 25px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            position: relative;
            transition: var(--transition);
        }
        
        .booking-tab.active {
            color: var(--primary-color);
        }
        
        .booking-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--primary-color);
            border-radius: 3px 3px 0 0;
        }
        
        .booking-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .booking-table th {
            text-align: left;
            padding: 15px;
            background-color: var(--light-gray);
            color: #555;
            font-weight: 500;
        }
        
        .booking-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .booking-table tr:last-child td {
            border-bottom: none;
        }
        
        .booking-table tr:hover {
            background-color: rgba(var(--primary-rgb), 0.05);
        }
        
        .booking-type {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .booking-type-icon {
            width: 30px;
            height: 30px;
            background-color: var(--light-gray);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 0.85rem;
        }
        
        .status-badge {
            display: inline-flex;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-confirmed {
            background-color: rgba(76, 175, 80, 0.15);
            color: #3d8b40;
        }
        
        .status-waiting {
            background-color: rgba(255, 152, 0, 0.15);
            color: #f57c00;
        }
        
        .status-cancelled {
            background-color: rgba(244, 67, 54, 0.15);
            color: #d32f2f;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-btn {
            background-color: var(--light-gray);
            color: var(--primary-color);
        }
        
        .view-btn:hover {
            background-color: #e9ecf2;
        }
        
        .no-bookings {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }
        
        .no-bookings i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .no-bookings h3 {
            margin: 0 0 10px;
            color: #555;
        }
        
        .no-bookings p {
            margin: 0;
        }
        
        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }
        
        .search-box {
            position: relative;
            max-width: 300px;
            width: 100%;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: border-color 0.3s;
        }
        
        .search-box input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: white;
            color: #555;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .filter-select:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        @media (max-width: 992px) {
            .booking-table {
                display: block;
                overflow-x: auto;
            }
            
            .search-filter {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-box {
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .booking-tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
            
            .booking-tab {
                padding: 12px 15px;
            }
            
            .filter-options {
                overflow-x: auto;
                padding-bottom: 5px;
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
            
            <div class="nav-links">
                <a href="index.php" title="Home"><i class="fas fa-home"></i></a>
                <a href="my_bookings.php" title="Booking History"><i class="fas fa-history"></i></a>
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
        <div class="bookings-container">
            <div class="bookings-header">
                <h1>My Bookings</h1>
                <p>Manage your bookings, download e-tickets and keep track of your upcoming journeys</p>
            </div>
            
            <div class="bookings-body">
                <div class="booking-tabs">
                    <a href="my_bookings.php?filter=all" class="booking-tab <?php echo $activeFilter === 'all' ? 'active' : ''; ?>">All Bookings</a>
                    <a href="my_bookings.php?filter=upcoming" class="booking-tab <?php echo $activeFilter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                    <a href="my_bookings.php?filter=completed" class="booking-tab <?php echo $activeFilter === 'completed' ? 'active' : ''; ?>">Completed</a>
                    <a href="my_bookings.php?filter=cancelled" class="booking-tab <?php echo $activeFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>
                
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search by PNR, destination..." id="booking-search">
                    </div>
                    
                    <div class="filter-options">
                        <select class="filter-select" id="type-filter">
                            <option value="all">All Types</option>
                            <option value="train">Train</option>
                            <option value="flight">Flight</option>
                        </select>
                        
                        <select class="filter-select" id="date-filter">
                            <option value="all">All Dates</option>
                            <option value="upcoming">Next 7 Days</option>
                            <option value="month">This Month</option>
                            <option value="past">Past Bookings</option>
                        </select>
                    </div>
                </div>
                
                <?php if (count($bookings) > 0): ?>
                <div class="booking-list">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Type</th>
                                <th>Journey</th>
                                <th>Date & Time</th>
                                <th>PNR</th>
                                <th>Passengers</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo $booking['booking_reference']; ?></td>
                                <td>
                                    <div class="booking-type">
                                        <div class="booking-type-icon">
                                            <i class="fas <?php echo $booking['booking_type'] === 'train' ? 'fa-train' : 'fa-plane'; ?>"></i>
                                        </div>
                                        <span><?php echo ucfirst($booking['booking_type']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $booking['from_location'] . ' to ' . $booking['to_location']; ?></td>
                                <td>
                                    <?php 
                                        echo date('d M Y', strtotime($booking['journey_date']));
                                        echo '<br><span style="font-size: 0.9em; color: #666;">' . date('h:i A', strtotime($booking['booking_date'])) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo $booking['pnr']; ?></td>
                                <td><?php echo $booking['total_passengers']; ?></td>
                                <td>₹<?php echo number_format($booking['amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($booking['booking_status']); ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_booking.php?id=<?php echo $booking['booking_id']; ?>" class="action-btn view-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>No bookings found</h3>
                    <p>You haven't made any bookings yet. Start exploring and book your next journey!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('booking-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.booking-table tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Filter by type
            const typeFilter = document.getElementById('type-filter');
            if (typeFilter) {
                typeFilter.addEventListener('change', function() {
                    const filterValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.booking-table tbody tr');
                    
                    if (filterValue === 'all') {
                        rows.forEach(row => row.style.display = '');
                        return;
                    }
                    
                    rows.forEach(row => {
                        const type = row.querySelector('.booking-type span').textContent.toLowerCase();
                        if (type === filterValue) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
            
            // Filter by date
            const dateFilter = document.getElementById('date-filter');
            if (dateFilter) {
                dateFilter.addEventListener('change', function() {
                    // In a real application, you would implement date filtering here
                    alert('Date filtering would be implemented in a production application.');
                });
            }
            
            // Theme toggle
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.body.classList.toggle('dark-theme');
                    const moonIcon = this.querySelector('.fa-moon');
                    const sunIcon = this.querySelector('.fa-sun');
                    
                    if (moonIcon.style.display === 'none') {
                        moonIcon.style.display = 'inline-block';
                        sunIcon.style.display = 'none';
                    } else {
                        moonIcon.style.display = 'none';
                        sunIcon.style.display = 'inline-block';
                    }
                });
            }
        });
    </script>
</body>
</html> 
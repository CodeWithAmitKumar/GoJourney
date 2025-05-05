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

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No booking ID provided.";
    header("Location: my_bookings.php");
    exit();
}

$bookingId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Initialize BookingManager and get booking details
$bookingManager = new BookingManager($conn);
$bookingDetails = $bookingManager->getBookingById($bookingId, $userId);

// If booking not found or doesn't belong to the current user
if (!$bookingDetails) {
    $_SESSION['error'] = "Booking not found or you don't have permission to view it.";
    header("Location: my_bookings.php");
    exit();
}

// Format dates
$formattedJourneyDate = date('D, d M Y', strtotime($bookingDetails['journey_date']));
$formattedBookingDate = date('d M Y, h:i A', strtotime($bookingDetails['booking_date']));

// Generate ticket ID if not already stored
$ticketId = $bookingDetails['ticket_path'] ?? strtoupper(substr(md5($bookingDetails['pnr'] . time()), 0, 6));

// Calculate arrival time based on journey date (since we don't store it explicitly)
// This is a simplified approach - in a real app you would store departure and arrival times
$departureTime = date('h:i A', strtotime($bookingDetails['booking_date']));
$durationHours = rand(1, 5); // Random duration for demo purposes
$durationMinutes = rand(0, 55);
$arrivalTime = date('h:i A', strtotime("+{$durationHours} hours +{$durationMinutes} minutes", strtotime($bookingDetails['booking_date'])));
$duration = "{$durationHours}h " . ($durationMinutes > 0 ? "{$durationMinutes}m" : "");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - GoJourney</title>
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
        
        .booking-container {
            max-width: 900px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .booking-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 20px 30px;
            position: relative;
        }
        
        .booking-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .booking-type-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .booking-body {
            padding: 30px;
        }
        
        .booking-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background-color: var(--light-gray);
            color: var(--primary-color);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .action-button i {
            margin-right: 8px;
        }
        
        .action-button:hover {
            background-color: #e9ecf2;
        }
        
        .primary-button {
            background-color: var(--primary-color);
            color: white;
        }
        
        .primary-button:hover {
            background-color: #3a5b8c;
        }
        
        .journey-card {
            border-radius: var(--border-radius);
            border: 1px solid #eee;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .journey-header {
            background-color: var(--light-gray);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .journey-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .journey-header-left h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }
        
        .journey-id {
            font-size: 0.9rem;
            color: #666;
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
        
        .journey-body {
            padding: 20px;
        }
        
        .journey-route {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .station {
            flex: 1;
        }
        
        .station h3 {
            font-size: 1.3rem;
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .station p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .journey-center {
            flex: 1;
            text-align: center;
            padding: 0 20px;
        }
        
        .journey-line {
            height: 2px;
            background-color: #ddd;
            position: relative;
            margin: 10px 0;
        }
        
        .journey-line::before,
        .journey-line::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #ddd;
            border-radius: 50%;
            top: -4px;
        }
        
        .journey-line::before {
            left: 0;
        }
        
        .journey-line::after {
            right: 0;
        }
        
        .duration {
            color: #666;
            font-size: 0.9rem;
        }
        
        .journey-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #ddd;
        }
        
        .detail-item {
            flex: 1;
            min-width: 150px;
        }
        
        .detail-item h4 {
            margin: 0 0 5px 0;
            color: #888;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .detail-item p {
            margin: 0;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .passenger-details h3 {
            font-size: 1.1rem;
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .passenger-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .passenger-table th {
            background-color: var(--light-gray);
            padding: 10px;
            text-align: left;
            font-weight: 500;
            color: #555;
        }
        
        .passenger-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .passenger-table tr:last-child td {
            border-bottom: none;
        }
        
        .payment-details {
            background-color: var(--light-gray);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .payment-details h3 {
            font-size: 1.1rem;
            margin: 0 0 15px 0;
            color: #333;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-amount {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .booking-actions {
                flex-direction: column;
            }
            
            .journey-route {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .journey-center {
                padding: 20px 0;
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
        <div class="booking-container">
            <div class="booking-header">
                <h1>
                    <div class="booking-type-icon">
                        <i class="fas <?php echo $bookingDetails['booking_type'] === 'train' ? 'fa-train' : 'fa-plane'; ?>"></i>
                    </div>
                    <?php echo ucfirst($bookingDetails['booking_type']); ?> Booking Details
                </h1>
            </div>
            
            <div class="booking-body">
                <div class="booking-actions">
                    <button class="action-button primary-button" onclick="window.print()">
                        <i class="fas fa-download"></i> Download Ticket
                    </button>
                    <button class="action-button" id="emailTicketBtn">
                        <i class="fas fa-envelope"></i> Email Ticket
                    </button>
                    <button class="action-button" onclick="window.location.href='my_bookings.php'">
                        <i class="fas fa-arrow-left"></i> Back to My Bookings
                    </button>
                    <?php if ($bookingDetails['booking_status'] !== 'cancelled'): ?>
                    <button class="action-button" id="cancelBookingBtn" data-id="<?php echo $bookingDetails['booking_id']; ?>">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="journey-card">
                    <div class="journey-header">
                        <div class="journey-header-left">
                            <h3><?php echo $bookingDetails['from_location'] . ' to ' . $bookingDetails['to_location']; ?></h3>
                            <div class="journey-id">PNR: <?php echo $bookingDetails['pnr']; ?></div>
                        </div>
                        <span class="status-badge status-<?php echo strtolower($bookingDetails['booking_status']); ?>">
                            <?php echo ucfirst($bookingDetails['booking_status']); ?>
                        </span>
                    </div>
                    
                    <div class="journey-body">
                        <div class="journey-route">
                            <div class="station">
                                <h3><?php echo $bookingDetails['from_location']; ?></h3>
                                <p><?php echo $departureTime; ?></p>
                                <p><?php echo $formattedJourneyDate; ?></p>
                            </div>
                            
                            <div class="journey-center">
                                <div class="duration"><?php echo $duration; ?></div>
                                <div class="journey-line">
                                    <i class="fas <?php echo $bookingDetails['booking_type'] === 'train' ? 'fa-train' : 'fa-plane'; ?>" style="position: absolute; top: -8px; left: 50%; transform: translateX(-50%); background: white; padding: 0 10px;"></i>
                                </div>
                            </div>
                            
                            <div class="station">
                                <h3><?php echo $bookingDetails['to_location']; ?></h3>
                                <p><?php echo $arrivalTime; ?></p>
                                <p><?php echo $formattedJourneyDate; ?></p>
                            </div>
                        </div>
                        
                        <div class="journey-details">
                            <div class="detail-item">
                                <h4>Booking Reference</h4>
                                <p><?php echo $bookingDetails['booking_reference']; ?></p>
                            </div>
                            
                            <div class="detail-item">
                                <h4>Booking Date</h4>
                                <p><?php echo $formattedBookingDate; ?></p>
                            </div>
                            
                            <div class="detail-item">
                                <h4>Travel Class</h4>
                                <p><?php echo $bookingDetails['travel_class']; ?></p>
                            </div>
                            
                            <div class="detail-item">
                                <h4><?php echo $bookingDetails['booking_type'] === 'train' ? 'Train' : 'Flight'; ?> Number</h4>
                                <p><?php echo $bookingDetails['transport_id']; ?></p>
                            </div>
                            
                            <div class="detail-item">
                                <h4>Ticket Number</h4>
                                <p><?php echo $ticketId; ?></p>
                            </div>
                        </div>
                        
                        <div class="passenger-details">
                            <h3>Passenger Details</h3>
                            <table class="passenger-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Seat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookingDetails['passengers'] as $index => $passenger): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $passenger['name']; ?></td>
                                        <td><?php echo $passenger['age']; ?></td>
                                        <td><?php echo $passenger['gender']; ?></td>
                                        <td><?php echo $passenger['seat_number'] ?? strtoupper(substr(md5($bookingDetails['pnr'] . $index), 0, 2)) . ($index + 1); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="payment-details">
                            <h3>Payment Details</h3>
                            <?php 
                                $baseFare = $bookingDetails['amount'] * 0.85;
                                $taxes = $bookingDetails['amount'] * 0.12;
                                $convenienceFee = $bookingDetails['amount'] * 0.03;
                            ?>
                            <div class="payment-row">
                                <span>Base Fare (<?php echo count($bookingDetails['passengers']); ?> passengers)</span>
                                <span>₹<?php echo number_format($baseFare, 2); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Taxes & Fees</span>
                                <span>₹<?php echo number_format($taxes, 2); ?></span>
                            </div>
                            <div class="payment-row">
                                <span>Convenience Fee</span>
                                <span>₹<?php echo number_format($convenienceFee, 2); ?></span>
                            </div>
                            <div class="payment-row total-amount">
                                <span>Total Amount</span>
                                <span>₹<?php echo number_format($bookingDetails['amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Email ticket functionality
            const emailTicketBtn = document.getElementById('emailTicketBtn');
            if (emailTicketBtn) {
                emailTicketBtn.addEventListener('click', function() {
                    alert('E-ticket has been sent to your registered email address.');
                });
            }
            
            // Cancel booking functionality
            const cancelBookingBtn = document.getElementById('cancelBookingBtn');
            if (cancelBookingBtn) {
                cancelBookingBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                        const bookingId = this.getAttribute('data-id');
                        window.location.href = 'cancel_booking.php?id=' + bookingId;
                    }
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
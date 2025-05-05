<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Check if booking confirmation exists in session
if (!isset($_SESSION['booking_confirmed'])) {
    $_SESSION['error'] = "No booking confirmation found. Please try again.";
    header("Location: index.php");
    exit();
}

// Retrieve booking details from session
$booking = $_SESSION['booking_confirmed'];
$bookingDetails = $booking['booking_details'];
$passengerDetails = $booking['passenger_details'];
$contactDetails = $booking['contact_details'];
$bookingType = $bookingDetails['type'];
$bookingDate = $booking['payment_date'];
$formattedDate = date('d M Y, h:i A', strtotime($bookingDate));

// Get journey details
$fromLocation = $bookingDetails['from'];
$toLocation = $bookingDetails['to'];
$journeyDate = $bookingDetails['date'];
$travelClass = $bookingDetails['class'];
$pnr = $booking['pnr'];
$bookingReference = $booking['reference'];
$bookingId = $booking['booking_id'] ?? null;

// Format journey date
$formattedJourneyDate = date('D, d M Y', strtotime($journeyDate));

// Generate ticket ID
$ticketId = strtoupper(substr(md5($pnr . time()), 0, 6));

// Save ticket path to database if booking_id exists
if ($bookingId) {
    // Create a unique ticket filename
    $ticketFilename = $pnr . '_' . $ticketId . '.pdf';
    
    // In a real-world scenario, you would generate a PDF here and save it to the server
    // For this demo, we'll just update the database with the ticket path
    
    // Update the ticket_path in the database
    $updateSql = "UPDATE bookings SET ticket_path = ? WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $updateSql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $ticketFilename, $bookingId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Get passenger details
    $passengerSql = "SELECT * FROM passengers WHERE booking_id = ?";
    $passengerStmt = mysqli_prepare($conn, $passengerSql);

    if ($passengerStmt) {
        mysqli_stmt_bind_param($passengerStmt, "i", $bookingId);
        mysqli_stmt_execute($passengerStmt);
        $passengerResult = mysqli_stmt_get_result($passengerStmt);
        
        $updatedPassengerDetails = [];
        $assignedSeats = []; // Keep track of assigned seats

        while ($row = mysqli_fetch_assoc($passengerResult)) {
            // Generate and update seat number if not already set
            if (empty($row['seat_number'])) {
                $seatNumber = '';
                $attempts = 0;
                
                // Try to generate a unique seat number (max 10 attempts to prevent infinite loop)
                do {
                    $attempts++;
                    
                    // Generate different seat formats based on booking type
                    if ($bookingType === 'train') {
                        // For trains: Format like S4102 (coach letter + number)
                        // Map travel class to coach prefix
                        $coachPrefix = 'S'; // Default (Sleeper)
                        
                        switch ($travelClass) {
                            case 'SL': $coachPrefix = 'S'; break; // Sleeper
                            case '3A': $coachPrefix = 'B'; break; // AC 3 Tier
                            case '2A': $coachPrefix = 'A'; break; // AC 2 Tier
                            case '1A': $coachPrefix = 'H'; break; // AC First Class
                            case 'CC': $coachPrefix = 'C'; break; // Chair Car
                            default:   $coachPrefix = 'S'; break;
                        }
                        
                        $coachNumber = rand(1, 9);
                        $seatNumber = $coachPrefix . $coachNumber . str_pad(rand(1, 72), 2, '0', STR_PAD_LEFT);
                    } else {
                        // For flights: Create a realistic seat number based on class
                        // Economy: Usually numbered rows with A-F seats (e.g., 14A, 26F)
                        // Business/First: Lower row numbers with A-D or A-K seats
                        
                        if ($travelClass == 'Economy' || $travelClass == 'Premium Economy') {
                            $row_num = rand(10, 40);
                            $seat_letter = chr(rand(65, 70)); // A to F
                        } else {
                            // Business or First class
                            $row_num = rand(1, 8);
                            if (rand(0, 1) == 0) {
                                // Narrow body config (A-D)
                                $seat_letter = chr(rand(65, 68)); // A to D
                            } else {
                                // Wide body config (A-K, skipping I)
                                $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
                                $seat_letter = $letters[array_rand($letters)];
                            }
                        }
                        
                        $seatNumber = $row_num . $seat_letter;
                    }
                    
                } while (in_array($seatNumber, $assignedSeats) && $attempts < 10);
                
                // Keep track of this seat as assigned
                $assignedSeats[] = $seatNumber;
                
                // Update seat number in database
                $updateSeatSql = "UPDATE passengers SET seat_number = ? WHERE passenger_id = ?";
                $seatStmt = mysqli_prepare($conn, $updateSeatSql);
                
                if ($seatStmt) {
                    mysqli_stmt_bind_param($seatStmt, "si", $seatNumber, $row['passenger_id']);
                    mysqli_stmt_execute($seatStmt);
                    mysqli_stmt_close($seatStmt);
                    
                    // Update row with new seat number
                    $row['seat_number'] = $seatNumber;
                }
            } else {
                // If seat already assigned, add to the tracking list
                $assignedSeats[] = $row['seat_number'];
            }
            
            $updatedPassengerDetails[] = $row;
        }
        
        // Use updated passenger details with seat numbers
        if (!empty($updatedPassengerDetails)) {
            $passengerDetails = $updatedPassengerDetails;
        }
        
        mysqli_stmt_close($passengerStmt);
    }
}

// Clear booking details from session after showing the ticket
// In a real application, you might want to keep this for a while
// unset($_SESSION['booking_confirmed']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - GoJourney</title>
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
        
        .ticket-container {
            max-width: 900px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 20px 30px;
            position: relative;
        }
        
        .ticket-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .booking-success {
            display: flex;
            align-items: center;
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .booking-success i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #4CAF50;
        }
        
        .ticket-progress {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .progress-step.completed .step-number {
            background-color: #4CAF50;
        }
        
        .step-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .progress-step.completed .step-label {
            color: white;
            font-weight: 600;
        }
        
        .ticket-progress::before {
            content: '';
            position: absolute;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.2);
            top: 15px;
            left: 15%;
            right: 15%;
            z-index: 0;
        }
        
        .ticket-body {
            padding: 30px;
        }
        
        .ticket-actions {
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
        
        .download-button {
            background-color: var(--primary-color);
            color: white;
        }
        
        .download-button:hover {
            background-color: #3a5b8c;
        }
        
        .e-ticket {
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .ticket-brand {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
        }
        
        .ticket-logo {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .ticket-type {
            background-color: var(--accent-color);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .ticket-details {
            padding: 20px;
        }
        
        .ticket-journey {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .journey-point {
            flex: 1;
        }
        
        .journey-point h3 {
            font-size: 1.3rem;
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .journey-point p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .journey-separator {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0 20px;
        }
        
        .journey-line {
            width: 100%;
            height: 2px;
            background-color: #ddd;
            position: relative;
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
        
        .journey-icon {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.2rem;
            color: var(--primary-color);
            background-color: white;
            padding: 0 10px;
        }
        
        .ticket-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #ddd;
        }
        
        .info-item {
            flex: 1;
            min-width: 150px;
        }
        
        .info-item h4 {
            margin: 0 0 5px 0;
            color: #888;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .info-item p {
            margin: 0;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
        }
        
        .passenger-details {
            margin-bottom: 25px;
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
        
        .barcode-section {
            border-top: 1px dashed #ddd;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .barcode {
            text-align: center;
        }
        
        .barcode img {
            max-width: 100%;
            height: 60px;
        }
        
        .barcode-text {
            font-family: monospace;
            margin-top: 5px;
            font-size: 0.8rem;
            color: #666;
        }
        
        .ticket-disclaimer {
            font-size: 0.8rem;
            color: #888;
            text-align: center;
            margin-top: 30px;
            line-height: 1.5;
        }
        
        @media print {
            .ticket-header, .ticket-actions, .navbar, .header-wrapper, .bg-overlay {
                display: none;
            }
            
            .ticket-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            
            .e-ticket {
                border: 1px solid #000;
            }
            
            .ticket-brand {
                background-color: #fff !important;
                color: #000 !important;
                border-bottom: 1px solid #000;
            }
            
            .ticket-type {
                border: 1px solid #000;
                background-color: #fff !important;
                color: #000 !important;
            }
            
            body {
                background-color: white;
            }
        }
        
        @media (max-width: 768px) {
            .ticket-journey {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .journey-separator {
                width: 80%;
                padding: 20px 0;
            }
            
            .ticket-actions {
                flex-direction: column;
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
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>Booking Confirmed</h1>
                <div class="booking-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Your <?php echo $bookingType === 'train' ? 'train' : 'flight'; ?> ticket has been booked successfully!</span>
                </div>
                <div class="ticket-progress">
                    <div class="progress-step completed">
                        <div class="step-number">1</div>
                        <div class="step-label">Search</div>
                    </div>
                    <div class="progress-step completed">
                        <div class="step-number">2</div>
                        <div class="step-label">Passenger Details</div>
                    </div>
                    <div class="progress-step completed">
                        <div class="step-number">3</div>
                        <div class="step-label">Payment</div>
                    </div>
                    <div class="progress-step completed">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
            </div>
            
            <div class="ticket-body">
                <div class="ticket-actions">
                    <button class="download-button action-button" onclick="window.print()">
                        <i class="fas fa-download"></i> Download E-Ticket
                    </button>
                    <button class="action-button" onclick="window.location.href='index.php'">
                        <i class="fas fa-home"></i> Back to Home
                    </button>
                    <button class="action-button" onclick="window.location.href='my_bookings.php'">
                        <i class="fas fa-list"></i> My Bookings
                    </button>
                    <button class="action-button" id="emailTicketBtn">
                        <i class="fas fa-envelope"></i> Email Ticket
                    </button>
                </div>
                
                <div class="e-ticket" id="printable-ticket">
                    <div class="ticket-brand">
                        <div class="ticket-logo">GoJourney</div>
                        <div class="ticket-type"><?php echo $bookingType === 'train' ? 'TRAIN TICKET' : 'FLIGHT TICKET'; ?></div>
                    </div>
                    
                    <div class="ticket-details">
                        <div class="ticket-journey">
                            <div class="journey-point">
                                <h3><?php echo $fromLocation; ?></h3>
                                <p><?php echo $formattedJourneyDate; ?></p>
                            </div>
                            
                            <div class="journey-separator">
                                <div class="journey-line">
                                    <div class="journey-icon">
                                        <i class="fas <?php echo $bookingType === 'train' ? 'fa-train' : 'fa-plane'; ?>"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="journey-point">
                                <h3><?php echo $toLocation; ?></h3>
                                <p><?php echo $formattedJourneyDate; ?></p>
                            </div>
                        </div>
                        
                        <div class="ticket-info">
                            <div class="info-item">
                                <h4>Booking Reference</h4>
                                <p><?php echo $bookingReference; ?></p>
                            </div>
                            
                            <div class="info-item">
                                <h4>PNR Number</h4>
                                <p><?php echo $pnr; ?></p>
                            </div>
                            
                            <div class="info-item">
                                <h4>Ticket Number</h4>
                                <p><?php echo $ticketId; ?></p>
                            </div>
                            
                            <div class="info-item">
                                <h4>Class</h4>
                                <p><?php echo $travelClass; ?></p>
                            </div>
                            
                            <div class="info-item">
                                <h4>Booking Date</h4>
                                <p><?php echo $formattedDate; ?></p>
                            </div>
                            
                            <div class="info-item">
                                <h4>Status</h4>
                                <p>Confirmed</p>
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
                                    <?php foreach ($passengerDetails as $index => $passenger): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $passenger['name']; ?></td>
                                        <td><?php echo $passenger['age']; ?></td>
                                        <td><?php echo $passenger['gender']; ?></td>
                                        <td><?php echo $passenger['seat_number'] ? $passenger['seat_number'] : 'Not Assigned'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="barcode-section">
                            <div class="ticket-details">
                                <div class="info-item">
                                    <h4><?php echo $bookingType === 'train' ? 'Train' : 'Flight'; ?> Number</h4>
                                    <p><?php echo $bookingDetails['id']; ?></p>
                                </div>
                                <?php if ($bookingType === 'train'): ?>
                                <div class="info-item">
                                    <h4>Platform</h4>
                                    <p><?php echo rand(1, 10); ?></p>
                                </div>
                                <?php else: ?>
                                <div class="info-item">
                                    <h4>Gate</h4>
                                    <p><?php echo chr(rand(65, 90)) . rand(1, 20); ?></p>
                                </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <h4>Contact</h4>
                                    <p><?php echo $contactDetails['phone'] ?? 'N/A'; ?></p>
                                </div>
                            </div>
                            
                            <div class="barcode">
                                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo $pnr; ?>&code=Code128&multiplebarcodes=false&translate-esc=false&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=&qunit=Mm&quiet=0" alt="Barcode">
                                <div class="barcode-text"><?php echo $pnr; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="ticket-disclaimer">
                    <p>This e-ticket is valid when presented with a valid government-issued photo ID. Please keep this ticket for your records.</p>
                    <p>For any assistance or to report any issues regarding your booking, please contact our customer support at support@gojourney.com.</p>
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
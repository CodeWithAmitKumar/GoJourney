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

// Get booking details from URL
$bookingType = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to = isset($_GET['to']) ? $_GET['to'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;

// Validate required parameters
if (empty($bookingType) || empty($id) || empty($from) || empty($to) || empty($date)) {
    $_SESSION['error'] = "Booking parameters are missing. Please try again.";
    header("Location: index.php");
    exit();
}

// Fetch user information
$userId = $_SESSION['user_id'];
$userQuery = "SELECT * FROM users WHERE user_id = $userId";
$userResult = mysqli_query($conn, $userQuery);
$userData = mysqli_fetch_assoc($userResult);

// Initialize booking details array
$bookingDetails = [
    'type' => $bookingType,
    'id' => $id,
    'from' => $from,
    'to' => $to,
    'date' => $date,
    'class' => $class,
    'passengers' => $passengers
];

// Store booking details in session for later use
$_SESSION['booking_details'] = $bookingDetails;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate passenger details
    $error = false;
    $errorMessage = '';
    
    // Check if all required fields are filled
    for ($i = 1; $i <= $passengers; $i++) {
        if (empty($_POST["name_$i"]) || 
            empty($_POST["age_$i"]) || 
            empty($_POST["gender_$i"])) {
            $error = true;
            $errorMessage = "Please fill all passenger details.";
            break;
        }
    }
    
    if (!$error) {
        // Store passenger details in session
        $passengerDetails = [];
        for ($i = 1; $i <= $passengers; $i++) {
            $passengerDetails[] = [
                'name' => $_POST["name_$i"],
                'age' => $_POST["age_$i"],
                'gender' => $_POST["gender_$i"],
                'seat_preference' => $_POST["seat_preference_$i"] ?? '',
                'meal_preference' => $_POST["meal_preference_$i"] ?? ''
            ];
        }
        
        // Store contact details
        $contactDetails = [
            'email' => $_POST['contact_email'],
            'phone' => $_POST['contact_phone']
        ];
        
        // Store all details in session
        $_SESSION['passenger_details'] = $passengerDetails;
        $_SESSION['contact_details'] = $contactDetails;
        
        // Redirect to payment page
        header("Location: payment.php");
        exit();
    }
}

// Set page title based on booking type
$pageTitle = ($bookingType === 'train') ? 'Train Booking' : 'Flight Booking';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - GoJourney</title>
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
        }
        
        .booking-progress {
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
        
        .progress-step.active .step-number {
            background-color: var(--accent-color);
        }
        
        .progress-step.completed .step-number {
            background-color: #4CAF50;
        }
        
        .step-label {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .progress-step.active .step-label {
            color: white;
            font-weight: 600;
        }
        
        .booking-progress::before {
            content: '';
            position: absolute;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.2);
            top: 15px;
            left: 15%;
            right: 15%;
            z-index: 0;
        }
        
        .booking-body {
            padding: 30px;
        }
        
        .booking-section {
            margin-bottom: 30px;
        }
        
        .booking-section h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .journey-summary {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .journey-route {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .station {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .journey-arrow {
            margin: 0 15px;
            color: var(--primary-color);
        }
        
        .journey-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .journey-detail {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .journey-detail i {
            color: var(--primary-color);
        }
        
        .passenger-form {
            margin-top: 20px;
        }
        
        .passenger-card {
            background-color: white;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .passenger-card h3 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.2);
            outline: none;
        }
        
        .contact-details {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
        }
        
        .booking-footer {
            display: flex;
            justify-content: space-between;
            padding: 20px 30px;
            background-color: var(--light-gray);
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 1rem;
        }
        
        .btn-back {
            background-color: #f1f2f6;
            color: #333;
        }
        
        .btn-back:hover {
            background-color: #e4e6eb;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #e45e5e 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(var(--secondary-color), 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--secondary-color), 0.4);
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        
        .required-field::after {
            content: '*';
            color: #c62828;
            margin-left: 4px;
        }
        
        @media (max-width: 768px) {
            .booking-container {
                margin: 15px;
            }
            
            .booking-header h1 {
                font-size: 1.5rem;
            }
            
            .step-label {
                display: none;
            }
            
            .booking-body {
                padding: 20px;
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
            
            <div class="nav-links">
                <a href="index.php" title="Home"><i class="fas fa-home"></i></a>
                <a href="#" title="Wishlist"><i class="fas fa-heart"></i></a>
                <a href="#" title="Cart"><i class="fas fa-shopping-cart"></i></a>
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
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="booking-container">
            <div class="booking-header">
                <h1><?php echo ($bookingType === 'train') ? 'Book Train Ticket' : 'Book Flight Ticket'; ?></h1>
                
                <div class="booking-progress">
                    <div class="progress-step active">
                        <div class="step-number">1</div>
                        <div class="step-label">Passenger Details</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-number">2</div>
                        <div class="step-label">Payment</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-number">3</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
            </div>
            
            <div class="booking-body">
                <?php if (isset($error) && $error): ?>
                <div class="error-message">
                    <?php echo $errorMessage; ?>
                </div>
                <?php endif; ?>
                
                <div class="booking-section">
                    <h2>Journey Summary</h2>
                    <div class="journey-summary">
                        <div class="journey-route">
                            <div class="station"><?php echo htmlspecialchars($from); ?></div>
                            <div class="journey-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                            <div class="station"><?php echo htmlspecialchars($to); ?></div>
                        </div>
                        
                        <div class="journey-details">
                            <div class="journey-detail">
                                <i class="far fa-calendar-alt"></i>
                                <span><?php echo htmlspecialchars($date); ?></span>
                            </div>
                            
                            <div class="journey-detail">
                                <i class="fas fa-ticket-alt"></i>
                                <span><?php echo ($bookingType === 'train') ? 'Train #' : 'Flight #'; ?><?php echo htmlspecialchars($id); ?></span>
                            </div>
                            
                            <div class="journey-detail">
                                <i class="fas fa-couch"></i>
                                <span>Class: <?php echo htmlspecialchars($class); ?></span>
                            </div>
                            
                            <div class="journey-detail">
                                <i class="fas fa-users"></i>
                                <span><?php echo htmlspecialchars($passengers); ?> Passenger<?php echo $passengers > 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="">
                    <div class="booking-section">
                        <h2>Passenger Details</h2>
                        
                        <?php for ($i = 1; $i <= $passengers; $i++): ?>
                        <div class="passenger-card">
                            <h3>Passenger <?php echo $i; ?></h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name_<?php echo $i; ?>" class="required-field">Full Name</label>
                                    <input type="text" id="name_<?php echo $i; ?>" name="name_<?php echo $i; ?>" class="form-control" required placeholder="As per ID card">
                                </div>
                                
                                <div class="form-group">
                                    <label for="age_<?php echo $i; ?>" class="required-field">Age</label>
                                    <input type="number" id="age_<?php echo $i; ?>" name="age_<?php echo $i; ?>" class="form-control" required min="1" max="120">
                                </div>
                                
                                <div class="form-group">
                                    <label for="gender_<?php echo $i; ?>" class="required-field">Gender</label>
                                    <select id="gender_<?php echo $i; ?>" name="gender_<?php echo $i; ?>" class="form-control" required>
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <?php if ($bookingType === 'train'): ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="seat_preference_<?php echo $i; ?>">Seat Preference</label>
                                    <select id="seat_preference_<?php echo $i; ?>" name="seat_preference_<?php echo $i; ?>" class="form-control">
                                        <option value="">No Preference</option>
                                        <option value="Lower">Lower Berth</option>
                                        <option value="Middle">Middle Berth</option>
                                        <option value="Upper">Upper Berth</option>
                                        <option value="Side Lower">Side Lower</option>
                                        <option value="Side Upper">Side Upper</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($bookingType === 'flight'): ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="seat_preference_<?php echo $i; ?>">Seat Preference</label>
                                    <select id="seat_preference_<?php echo $i; ?>" name="seat_preference_<?php echo $i; ?>" class="form-control">
                                        <option value="">No Preference</option>
                                        <option value="Window">Window</option>
                                        <option value="Aisle">Aisle</option>
                                        <option value="Middle">Middle</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="meal_preference_<?php echo $i; ?>">Meal Preference</label>
                                    <select id="meal_preference_<?php echo $i; ?>" name="meal_preference_<?php echo $i; ?>" class="form-control">
                                        <option value="Regular">Regular</option>
                                        <option value="Vegetarian">Vegetarian</option>
                                        <option value="Vegan">Vegan</option>
                                        <option value="Diabetic">Diabetic</option>
                                        <option value="Gluten-free">Gluten-free</option>
                                        <option value="None">No meal</option>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="booking-section">
                        <h2>Contact Details</h2>
                        <div class="contact-details">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_email" class="required-field">Email Address</label>
                                    <input type="email" id="contact_email" name="contact_email" class="form-control" required value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
                                    <small>Ticket and updates will be sent to this email</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_phone" class="required-field">Mobile Number</label>
                                    <input type="tel" id="contact_phone" name="contact_phone" class="form-control" required value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                                    <small>SMS alerts will be sent to this number</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-footer">
                        <a href="<?php echo $bookingType === 'train' ? 'train_results.php' : 'flight_results.php'; ?>?from=<?php echo urlencode($from); ?>&to=<?php echo urlencode($to); ?>&date=<?php echo urlencode($date); ?>&class=<?php echo urlencode($class); ?>&passengers=<?php echo urlencode($passengers); ?>" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Results
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Continue to Payment <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="copyright-section">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> GoJourney. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="../script.js"></script>
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = document.querySelectorAll('[required]');
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill all required fields');
                }
            });
        });
    </script>
</body>
</html> 
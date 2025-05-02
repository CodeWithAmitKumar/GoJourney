<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Include the BookingManager class
require_once '../includes/BookingManager.php';

// Check if booking details exist in session
if (!isset($_SESSION['booking_details']) || !isset($_SESSION['passenger_details'])) {
    $_SESSION['error'] = "Booking information is missing. Please start again.";
    header("Location: index.php");
    exit();
}

// Retrieve booking and passenger details from session
$bookingDetails = $_SESSION['booking_details'];
$passengerDetails = $_SESSION['passenger_details'];
$contactDetails = $_SESSION['contact_details'] ?? [];

// Calculate total price based on booking type, class and number of passengers
$basePrice = 0;

if ($bookingDetails['type'] === 'train') {
    // Train pricing logic (simplified example)
    switch ($bookingDetails['class']) {
        case 'SL': $basePrice = 500; break;
        case '3A': $basePrice = 1200; break;
        case '2A': $basePrice = 2000; break;
        case '1A': $basePrice = 3500; break;
        case 'CC': $basePrice = 800; break;
        default: $basePrice = 500;
    }
} else {
    // Flight pricing logic (simplified example)
    switch ($bookingDetails['class']) {
        case 'Economy': $basePrice = 3000; break;
        case 'Premium Economy': $basePrice = 5000; break;
        case 'Business': $basePrice = 12000; break;
        case 'First': $basePrice = 25000; break;
        default: $basePrice = 3000;
    }
}

// Calculate total price
$totalPassengers = count($passengerDetails);
$subtotal = $basePrice * $totalPassengers;
$taxes = $subtotal * 0.18; // 18% tax
$convenienceFee = 200;
$totalAmount = $subtotal + $taxes + $convenienceFee;

// Handle payment form submission
$paymentSuccess = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment form
    if (empty($_POST['card_number']) ||
        empty($_POST['card_holder']) ||
        empty($_POST['expiry_date']) ||
        empty($_POST['cvv'])) {
        $errorMessage = "Please fill all payment details.";
    } else {
        // Payment processing logic would go here in a real application
        
        // Generate booking reference
        $bookingReference = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        // Generate PNR (Passenger Name Record)
        $pnr = strtoupper(substr(md5(time() . rand()), 0, 10));
        
        // Initialize BookingManager
        $bookingManager = new BookingManager($conn);
        
        // Prepare booking data
        $bookingData = [
            'user_id' => $_SESSION['user_id'],
            'booking_reference' => $bookingReference,
            'pnr' => $pnr,
            'booking_type' => $bookingDetails['type'],
            'transport_id' => $bookingDetails['id'],
            'from_location' => $bookingDetails['from'],
            'to_location' => $bookingDetails['to'],
            'journey_date' => $bookingDetails['date'],
            'travel_class' => $bookingDetails['class'],
            'total_passengers' => $totalPassengers,
            'booking_date' => date('Y-m-d H:i:s'),
            'payment_status' => 'completed',
            'booking_status' => 'confirmed',
            'amount' => $totalAmount
        ];
        
        // Prepare payment data
        $paymentData = [
            'payment_method' => 'Credit Card',
            'amount' => $totalAmount,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_status' => 'completed'
        ];
        
        // Save booking to database
        $bookingId = $bookingManager->saveBooking($bookingData, $passengerDetails, $paymentData);
        
        if ($bookingId) {
            // Store booking in session for ticket generation
            $_SESSION['booking_confirmed'] = [
                'booking_id' => $bookingId,
                'reference' => $bookingReference,
                'pnr' => $pnr,
                'booking_details' => $bookingDetails,
                'passenger_details' => $passengerDetails,
                'contact_details' => $contactDetails,
                'amount' => $totalAmount,
                'payment_date' => date('Y-m-d H:i:s')
            ];
            
            // Redirect to ticket page
            header("Location: ticket.php");
            exit();
        } else {
            $errorMessage = "Unable to save booking. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - GoJourney</title>
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
        
        .payment-container {
            max-width: 900px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
            color: white;
            padding: 20px 30px;
            position: relative;
        }
        
        .payment-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .payment-progress {
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
        
        .payment-progress::before {
            content: '';
            position: absolute;
            height: 3px;
            background-color: rgba(255, 255, 255, 0.2);
            top: 15px;
            left: 15%;
            right: 15%;
            z-index: 0;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .payment-section {
            margin-bottom: 30px;
        }
        
        .payment-section h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .payment-summary {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .amount-row {
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
        
        .payment-methods {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .payment-method.active {
            border-color: var(--primary-color);
            background-color: rgba(var(--primary-rgb), 0.05);
        }
        
        .payment-method img {
            height: 40px;
            margin-bottom: 10px;
        }
        
        .card-form {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #eee;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .card-icons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .card-icon {
            width: 50px;
            height: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .pay-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        
        .pay-button:hover {
            background-color: #3a5b8c;
        }
        
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .secure-badge i {
            color: #4CAF50;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .payment-methods {
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
        <div class="payment-container">
            <div class="payment-header">
                <h1><?php echo $bookingDetails['type'] === 'train' ? 'Train' : 'Flight'; ?> Booking Payment</h1>
                <div class="payment-progress">
                    <div class="progress-step completed">
                        <div class="step-number">1</div>
                        <div class="step-label">Search</div>
                    </div>
                    <div class="progress-step completed">
                        <div class="step-number">2</div>
                        <div class="step-label">Passenger Details</div>
                    </div>
                    <div class="progress-step active">
                        <div class="step-number">3</div>
                        <div class="step-label">Payment</div>
                    </div>
                    <div class="progress-step">
                        <div class="step-number">4</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
            </div>
            
            <div class="payment-body">
                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-error">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>
                
                <div class="payment-section">
                    <h2>Payment Summary</h2>
                    <div class="payment-summary">
                        <div class="amount-row">
                            <span>Base Fare (<?php echo $totalPassengers; ?> <?php echo $totalPassengers > 1 ? 'passengers' : 'passenger'; ?>)</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="amount-row">
                            <span>Taxes & Fees</span>
                            <span>₹<?php echo number_format($taxes, 2); ?></span>
                        </div>
                        <div class="amount-row">
                            <span>Convenience Fee</span>
                            <span>₹<?php echo number_format($convenienceFee, 2); ?></span>
                        </div>
                        <div class="amount-row total-amount">
                            <span>Total Amount</span>
                            <span>₹<?php echo number_format($totalAmount, 2); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-section">
                    <h2>Select Payment Method</h2>
                    <div class="payment-methods">
                        <div class="payment-method active">
                            <img src="../images/payment/card-icon.png" alt="Credit/Debit Card">
                            <h3>Credit/Debit Card</h3>
                        </div>
                        <div class="payment-method">
                            <img src="../images/payment/upi-icon.png" alt="UPI">
                            <h3>UPI</h3>
                        </div>
                        <div class="payment-method">
                            <img src="../images/payment/netbanking-icon.png" alt="Net Banking">
                            <h3>Net Banking</h3>
                        </div>
                    </div>
                </div>
                
                <form method="post" class="card-form">
                    <div class="card-icons">
                        <div class="card-icon"><img src="../images/payment/visa.png" alt="Visa"></div>
                        <div class="card-icon"><img src="../images/payment/mastercard.png" alt="Mastercard"></div>
                        <div class="card-icon"><img src="../images/payment/amex.png" alt="American Express"></div>
                        <div class="card-icon"><img src="../images/payment/rupay.png" alt="RuPay"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    
                    <div class="form-group">
                        <label for="card_holder">Cardholder Name</label>
                        <input type="text" id="card_holder" name="card_holder" placeholder="John Doe">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="password" id="cvv" name="cvv" placeholder="123" maxlength="4">
                        </div>
                    </div>
                    
                    <button type="submit" class="pay-button">Pay ₹<?php echo number_format($totalAmount, 2); ?></button>
                    
                    <div class="secure-badge">
                        <i class="fas fa-lock"></i> Secure payment powered by GoJourney Payment Gateway
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Card number formatting
            const cardNumberInput = document.getElementById('card_number');
            cardNumberInput.addEventListener('input', function(e) {
                // Remove non-digits
                let value = this.value.replace(/\D/g, '');
                // Add spaces after every 4 digits
                value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                // Update the input value
                this.value = value;
            });
            
            // Expiry date formatting
            const expiryDateInput = document.getElementById('expiry_date');
            expiryDateInput.addEventListener('input', function(e) {
                // Remove non-digits
                let value = this.value.replace(/\D/g, '');
                
                // Add slash after month
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                
                // Update the input value
                this.value = value;
            });
            
            // Payment method selection
            const paymentMethods = document.querySelectorAll('.payment-method');
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    // Remove active class from all methods
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    // Add active class to clicked method
                    this.classList.add('active');
                });
            });
            
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
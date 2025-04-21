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
                
                <!-- Destination Image Slider -->
                <div class="destination-slider-container">
                    <div class="destination-slider">
                        <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
                        <div class="slider-wrapper">
                            <div class="slide">
                                <img src="../images/destinations/chilika.jpg" alt="Chilika Lake">
                                <div class="slide-info">
                                    <h3>Chilika Lake</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/konark.jpg" alt="Konark Sun Temple">
                                <div class="slide-info">
                                    <h3>Konark Sun Temple</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/puri.jpg" alt="Puri Beach">
                                <div class="slide-info">
                                    <h3>Puri</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/lingaraj.jpg" alt="Lingaraj Temple">
                                <div class="slide-info">
                                    <h3>Lingaraj Temple</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/barheipani.png" alt="Barehipani Waterfall">
                                <div class="slide-info">
                                    <h3>Barehipani Waterfall</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/chandrabhaga.jpg" alt="Chandrabhaga Beach">
                                <div class="slide-info">
                                    <h3>Chandrabhaga Beach</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/tarinitemple.jpg" alt="Tarini Temple">
                                <div class="slide-info">
                                    <h3>Tarini Temple</h3>
                                </div>
                            </div>
                            <div class="slide">
                                <img src="../images/destinations/tunki.jpg" alt="Tunki Waterfall">
                                <div class="slide-info">
                                    <h3>Tunki Waterfall</h3>
                                </div>
                            </div>
                        </div>
                        <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
                        <div class="slide-indicators"></div>
                    </div>
                </div>
                
                <!-- Booking Cards Section -->
                <div class="booking-section-title">
                    <h2>Search Hotel, Flight and Train to Reach, Stay and Enjoy Your Holiday with Your Loved Ones </h2><big>❤️</big> 
                    <div class="title-underline"></div>
                </div>
                <div class="booking-cards-container">
                    <div class="booking-card hotel-card">
                        <div class="booking-card-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h3>Book Hotel</h3>
                        <p>Find the perfect stay from our curated selection of hotels, resorts, and homestays.</p>
                        <div class="booking-card-form">
                            <div class="form-group">
                                <label for="hotel-destination">Destination</label>
                                <input type="text" id="hotel-destination" class="form-control" placeholder="Where are you going?">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hotel-check-in">Check-in</label>
                                    <input type="date" id="hotel-check-in" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="hotel-check-out">Check-out</label>
                                    <input type="date" id="hotel-check-out" class="form-control">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hotel-guests">Adults</label>
                                    <div class="number-input">
                                        <button type="button" class="minus-btn" onclick="this.parentNode.querySelector('input').stepDown()"><i class="fas fa-minus"></i></button>
                                        <input type="number" id="hotel-adults" class="form-control" min="1" max="10" value="2">
                                        <button type="button" class="plus-btn" onclick="this.parentNode.querySelector('input').stepUp()"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="hotel-children">Children</label>
                                    <div class="number-input">
                                        <button type="button" class="minus-btn" onclick="this.parentNode.querySelector('input').stepDown()"><i class="fas fa-minus"></i></button>
                                        <input type="number" id="hotel-children" class="form-control" min="0" max="6" value="0">
                                        <button type="button" class="plus-btn" onclick="this.parentNode.querySelector('input').stepUp()"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hotel-rooms">Rooms</label>
                                    <select id="hotel-rooms" class="form-control">
                                        <option value="1">1 Room</option>
                                        <option value="2">2 Rooms</option>
                                        <option value="3">3 Rooms</option>
                                        <option value="4+">4+ Rooms</option>
                                    </select>
                                </div>
                            </div>
                            <button class="booking-search-btn">Search Hotels <i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="booking-card train-card">
                        <div class="booking-card-icon">
                            <i class="fas fa-train"></i>
                        </div>
                        <h3>Book Train</h3>
                        <p>Book train tickets with convenience and get the best deals on your journey.</p>
                        <div class="booking-card-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="train-from">From</label>
                                    <input type="text" id="train-from" class="form-control" placeholder="Departure station">
                                </div>
                                <div class="form-group">
                                    <label for="train-to">To</label>
                                    <input type="text" id="train-to" class="form-control" placeholder="Arrival station">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="train-date">Travel Date</label>
                                    <input type="date" id="train-date" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="train-class">Class</label>
                                    <select id="train-class" class="form-control">
                                        <option value="SL">Sleeper</option>
                                        <option value="3A">AC 3 Tier</option>
                                        <option value="2A">AC 2 Tier</option>
                                        <option value="1A">AC First Class</option>
                                        <option value="CC">Chair Car</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="train-passengers">Passengers</label>
                                <select id="train-passengers" class="form-control">
                                    <option value="1">1 Passenger</option>
                                    <option value="2">2 Passengers</option>
                                    <option value="3">3 Passengers</option>
                                    <option value="4">4 Passengers</option>
                                    <option value="5+">5+ Passengers</option>
                                </select>
                            </div>
                            <button class="booking-search-btn">Search Trains <i class="fas fa-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="booking-card flight-card">
                        <div class="booking-card-icon">
                            <i class="fas fa-plane"></i>
                        </div>
                        <h3>Book Flight</h3>
                        <p>Search for flights, compare prices, and book your tickets for domestic and international destinations.</p>
                        <div class="booking-card-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="flight-from">From</label>
                                    <input type="text" id="flight-from" class="form-control" placeholder="Departure city">
                                </div>
                                <div class="form-group">
                                    <label for="flight-to">To</label>
                                    <input type="text" id="flight-to" class="form-control" placeholder="Arrival city">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="flight-depart">Departure</label>
                                    <input type="date" id="flight-depart" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="flight-return">Return</label>
                                    <input type="date" id="flight-return" class="form-control">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="flight-passengers">Passengers</label>
                                    <select id="flight-passengers" class="form-control">
                                        <option value="1">1 Passenger</option>
                                        <option value="2">2 Passengers</option>
                                        <option value="3">3 Passengers</option>
                                        <option value="4">4 Passengers</option>
                                        <option value="5+">5+ Passengers</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="flight-class">Class</label>
                                    <select id="flight-class" class="form-control">
                                        <option value="Economy">Economy</option>
                                        <option value="Premium">Premium Economy</option>
                                        <option value="Business">Business</option>
                                        <option value="First">First Class</option>
                                    </select>
                                </div>
                            </div>
                            <button class="booking-search-btn">Search Flights <i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Why Book With Us Section -->
                <div class="why-book-container">
                    <h2><i class="fas fa-award"></i> Why Book With Us</h2>
                    <div class="testimonials-slider">
                        <button class="slider-btn prev-btn testimonial-prev"><i class="fas fa-chevron-left"></i></button>
                        <div class="testimonials-wrapper">
                            <div class="testimonial-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <h3>Safe & Secure</h3>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <p>Your security is our priority. We use industry-leading encryption to protect your personal information and transactions.</p>
                                </div>
                            </div>
                            <div class="testimonial-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h3>Best Price Guarantee</h3>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <p>Find a lower price? We'll match it and give you an additional 10% off on your booking.</p>
                                </div>
                            </div>
                            <div class="testimonial-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon">
                                        <i class="fas fa-headset"></i>
                                    </div>
                                    <h3>24/7 Customer Support</h3>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <p>Our dedicated support team is available round the clock to assist you with any questions or concerns.</p>
                                </div>
                            </div>
                            <div class="testimonial-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon">
                                        <i class="fas fa-plane-departure"></i>
                                    </div>
                                    <h3>Flexible Bookings</h3>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <p>Plans change, we understand. Enjoy free cancellation and easy rescheduling on most bookings.</p>
                                </div>
                            </div>
                            <div class="testimonial-slide">
                                <div class="testimonial-card">
                                    <div class="testimonial-icon">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <h3>Rewards Program</h3>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <p>Earn points with every booking and redeem them for discounts on future travels and exclusive perks.</p>
                                </div>
                            </div>
                        </div>
                        <button class="slider-btn next-btn testimonial-next"><i class="fas fa-chevron-right"></i></button>
                        <div class="testimonial-indicators"></div>
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
                
                <div class="footer-col contact-info">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i>016 Travel Road, Baripada, Mayurbhanj, Odisha,India</p>
                    <p><i class="fas fa-phone-alt"></i> +91 8984972877</p>
                    <p><i class="fas fa-envelope"></i> info@gojourney.com</p>
                    <div class="payment-methods">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                    </div>
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
</body>
</html> 
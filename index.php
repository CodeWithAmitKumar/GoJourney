<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoJourney</title>
    <link rel="icon" type="image/png" href="images/logo&svg/svg2.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Note: The hero background uses an image from images/background/home.jpg -->
</head>
<body>
    <!-- Toast container -->
    <div id="toast-container"></div>
    
    <?php
    session_start();
    // Display error messages if any
    if (isset($_SESSION['error'])) {
        echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    
    // Display success messages if any
    if (isset($_SESSION['success'])) {
        echo "<div class='success-message'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
    }
    ?>

    <nav class="navbar">
        <a href="index.php" class="logo">GoJourney</a>
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#services">Services</a>
            <a href="#contact">ContactUs</a>
        </div>
    </nav>
    
    <section id="home" class="hero-section">
        <div class="hero-content">
            <h1>Start your journey by one click,explore beautiful world! </h1>
            <p>Travel with love in your heart and adventure in your soul - the world responds to both with beauty and wonder.</p>
            <button class="cta-button" id="signupBtn">SignUp</button>
            <button class="cta-button" id="loginBtn">Login</button>
        </div>
        <div class="hero-image">
            <img src="images/background/model1.png" alt="Travel Model">
        </div>
    </section>
    
    <section id="popular-destinations" class="destinations-section">
        <div class="section-header">
            <h2>Popular Destinations</h2>
            <p>Explore our handpicked selection of breathtaking destinations that travelers love worldwide</p>
        </div>
        
        <div class="destination-cards">
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/puri.jpg" alt="Jagannath Dham">
                    <div class="destination-tag">FEATURED</div>
                </div>
                <div class="destination-info">
                    <h3>Puri , Odisha</h3>
                    <p class="destination-description">Shree Jagannath Dham </p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>5.0</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.1100.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/shreeRam.jpg" alt="Shree Ram Temple">
                </div>
                <div class="destination-info">
                    <h3>Bhubaneswer , Odisha</h3>
                    <p class="destination-description">Shree Ram Temple</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.8</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.799.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/chandrabhaga.jpg" alt="Chandrabhaga Sea Beach">
                </div>
                <div class="destination-info">
                    <h3>Chandrabhaga , Odisha</h3>
                    <p class="destination-description">Beautiful Sea Beach</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.7</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.1,099.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/konark.jpg" alt="Konark Temple">
                </div>
                <div class="destination-info">
                    <h3>Konark , Odisha</h3>
                    <p class="destination-description">Sun Temple</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.5</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.1245.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/tunki.jpg" alt="Tunki Waterfall">
                </div>
                <div class="destination-info">
                    <h3>Mayurbhanj , Odisha</h3>
                    <p class="destination-description">Tunki Waterfall</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.0</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.599.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/chilika.jpg" alt="Chilika lake">
                </div>
                <div class="destination-info">
                    <h3>Ganjam , Odisha</h3>
                    <p class="destination-description">Chilika - The largest brackish water lagoon in Asia</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.7</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.999.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/itr.webp" alt="Itr">
                </div>
                <div class="destination-info">
                    <h3>Itr , Balasore </h3>
                    <p class="destination-description">The Missile city </p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.0</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.1,299.00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/barheipani.png" alt="Barheipani">
                </div>
                <div class="destination-info">
                    <h3>Mayurbhanj , Odisha</h3>
                    <p class="destination-description"> Barheipani - Two-tiered waterfall</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>3.8</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">Rs.700.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="explore-more-container">
            <a href="#" id="exploreMoreBtn" class="explore-more-btn" onclick="exploreMoreClicked(event)">
                Explore More
                <span class="arrow-icon">→</span>
            </a>
        </div>
    </section>
    
    <section id="about" class="about-section">
        <div class="about-content">
            <h2>About GoJourney</h2>
           <p> Welcome to GoJourney — where wanderlust meets wonder.</p>

            <p>We're not just a travel website. We're a spark. A gentle nudge that whispers, "Go. See what's out there." Whether it's the call of misty mountains, the hum of a bustling street market, or the calm of a sunset over the ocean — we're here to guide you toward the moments that make you feel truly alive.</p>

            <p>At GoJourney, we believe every journey begins with a dream. That dream deserves more than just a map — it needs heart, inspiration, and a touch of magic. We handpick stories, hidden gems, and travel experiences that stir the soul and awaken curiosity.</p>

            <p>Pack your bags — or just your imagination — and let GoJourney be your window to the world. The adventure of a lifetime might be just one click away. </p>


        </div>
        <div class="about-image">
            <img src="images/background/model2.png" alt="model-2">
        </div>
    </section>
    
    <section id="services" class="reviews-section">
        <h2>Meet Clients Reviews</h2>
        <div class="reviews-container">
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client1.png" alt="Client 1">
                </div>
                <div class="review-content">
                    <h3>Harekrushna Pradhan</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p>"GoJourney made planning my trip to Shree Jagannath Dham ,Puri absolutely seamless! Their personalized recommendations helped me discover hidden gems I would have never found on my own. The memories I made will last a lifetime."</p>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client2.jpg" alt="Client 2">
                </div>
                <div class="review-content">
                    <h3>Amit Kumar Patra</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p>"As a solo traveler, I was nervous about exploring Tunki Waterfall alone. GoJourney connected me with local guides who showed me the authentic side of the mountain and river . It transformed what could have been an ordinary vacation into an extraordinary adventure!"</p>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client3.png" alt="Client 3">
                </div>
                <div class="review-content">
                    <h3>Susree Jena</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star half">★</span>
                    </div>
                    <p>"I used GoJourney for Picnic Party in Chandrabhaga Sea Beach. The itinerary they crafted was perfect - a beautiful balance of relaxation and adventure. Their attention to detail made my  trip truly magical."</p>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client4.png" alt="Client 4">
                </div>
                <div class="review-content">
                    <h3>Snehasis Behera</h3>
                    <div class="stars">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                    </div>
                    <p>"As a family of five, finding suitable travel plans can be challenging. GoJourney understood our needs perfectly and created a family-friendly tour of Lingaraj Temple , Bhubaneswer that kept everyone from our 5-year-old to my wife's parents engaged and happy!"</p>
                </div>
            </div>
        </div>
    </section>
    
    <section id="contact" class="contact-section" style="background-image: url('images/background/contact.jpg'); background-position: center; background-size: cover; background-repeat: no-repeat; background-color: rgba(255, 255, 255, 0.9); background-blend-mode: overlay;">
        <div class="gradient-overlay"></div>
        <div class="contact-content">
            <div class="contact-info">
                <h2>Contact Us</h2>
                <h3>We'd love to hear from you!</h3>
                <p>Have questions, suggestions, or feedback about your experience with GoJourney? Our team is dedicated to making your travel experience exceptional.</p>
                
                <div class="contact-details">
                    <div class="contact-detail-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-detail-text">support@gojourney.com</div>
                    </div>
                    
                    <div class="contact-detail-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-detail-text">+91 8984972877</div>
                    </div>
                    
                    <div class="contact-detail-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-detail-text">016 Travel Road, Baripada, Mayurbhanj, Odisha</div>
                    </div>
                </div>
                
                <button class="feedback-btn" id="feedbackBtn">Share Your Feedback</button>
            </div>
            
            <div class="contact-visual">
                <div class="contact-visual-content">
                    <h3>Let's Make Your Journey Memorable</h3>
                    <p>Your next adventure is just a conversation away.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <!-- Your page content will go here -->
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Login</h2>
            <h3 id="login-message">Welcome back! Please enter your details to login.</h3>
            <form id="loginForm" action="auth/login.php" method="post">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" class="submit-btn">Login</button>
                <p class="form-switch">Don't have an account? <a href="#" id="showSignup">Sign up</a></p>
            </form>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal" id="signupModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Create Account</h2>
            <h3>Create an account to start your journey with GoJourney.</h3>
            <form id="signupForm" action="auth/register.php" method="post">
                <div class="form-group">
                    <label for="signup-name">Full Name</label>
                    <input type="text" id="signup-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signup-password">Password</label>
                    <input type="password" id="signup-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="signup-confirm">Confirm Password</label>
                    <input type="password" id="signup-confirm" name="confirm_password" required>
                </div>
                <button type="submit" class="submit-btn">Sign Up</button>
                <p class="form-switch">Already have an account? <a href="#" id="showLogin">Login</a></p>
            </form>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal" id="feedbackModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Your Feedback</h2>
            <h3>We value your opinion to improve our services</h3>
            <form id="feedbackForm" action="submit_feedback.php" method="post">
                <div class="form-group">
                    <label for="feedback-name">Your Name</label>
                    <input type="text" id="feedback-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="feedback-email">Email</label>
                    <input type="email" id="feedback-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="feedback-subject">Subject</label>
                    <input type="text" id="feedback-subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="feedback-message">Your Message</label>
                    <textarea id="feedback-message" name="message" rows="5" required></textarea>
                </div>
                <input type="hidden" name="submission_date" value="<?php echo date('Y-m-d H:i:s'); ?>">
                <button type="submit" class="submit-btn">Submit Feedback</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    <script>
        // Verify DOM loading
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document loaded from inline script');
            
            // Direct handlers for login and signup buttons
            const loginBtn = document.getElementById('loginBtn');
            const signupBtn = document.getElementById('signupBtn');
            const loginModal = document.getElementById('loginModal');
            const signupModal = document.getElementById('signupModal');
            const feedbackModal = document.getElementById('feedbackModal');
            
            console.log('Login button:', loginBtn);
            console.log('Signup button:', signupBtn);
            
            // Modal show function with animation
            function showModal(modal) {
                modal.style.display = 'flex';
                // Allow display:flex to take effect before adding show class
                setTimeout(() => {
                    modal.classList.add('show');
                }, 10);
            }
            
            // Modal hide function with animation
            function hideModal(modal) {
                modal.classList.remove('show');
                // Wait for the opacity transition to complete before hiding
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
            
            // Login button click handler
            loginBtn.addEventListener('click', function() {
                console.log('Login button clicked');
                showModal(loginModal);
            });
            
            // Signup button click handler
            signupBtn.addEventListener('click', function() {
                console.log('Signup button clicked');
                showModal(signupModal);
            });
            
            // Switch between forms
            document.getElementById('showLogin').addEventListener('click', function(e) {
                e.preventDefault();
                hideModal(signupModal);
                setTimeout(() => {
                    showModal(loginModal);
                }, 300);
            });
            
            document.getElementById('showSignup').addEventListener('click', function(e) {
                e.preventDefault();
                hideModal(loginModal);
                setTimeout(() => {
                    showModal(signupModal);
                }, 300);
            });
            
            // Close when clicking outside of modal
            window.addEventListener('click', function(e) {
                if (e.target === loginModal) {
                    hideModal(loginModal);
                }
                if (e.target === signupModal) {
                    hideModal(signupModal);
                }
                if (e.target === feedbackModal) {
                    hideModal(feedbackModal);
                }
            });
            
            // Close on X button click
            document.querySelectorAll('.close-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        hideModal(modal);
                    }
                });
            });
        });

        // Direct implementation for Explore More button
        function exploreMoreClicked(event) {
            event.preventDefault();
            console.log('Explore More clicked via direct handler');
            
            // Show toast
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            toast.textContent = 'Login to explore more places';
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            // Redirect after 2 seconds
            setTimeout(() => {
                const loginModal = document.getElementById('loginModal');
                if (loginModal) {
                    const showModal = (modal) => {
                        modal.style.display = 'flex';
                        setTimeout(() => {
                            modal.classList.add('show');
                        }, 10);
                    };
                    showModal(loginModal);
                }
                
                // Remove toast
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 400);
            }, 2000);
            
            return false;
        }

        // Feedback button functionality
        document.getElementById('feedbackBtn').addEventListener('click', function() {
            const feedbackModal = document.getElementById('feedbackModal');
            feedbackModal.style.display = 'flex';
            setTimeout(() => {
                feedbackModal.classList.add('show');
            }, 10);
        });
    </script>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <h3>GoJourney</h3>
                <p>Explore the world with confidence. We provide unforgettable travel experiences with personalized itineraries and exceptional customer service.</p>
                <div class="social-icons">
                    <a href="https://x.com/Codewith_amit?t=wzxyQYtIqyK_JnFzeww4uQ&s=09" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/thatodiapila?igsh=MXRyeXBjZ2l2ZXduZQ==" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="https://github.com/CodeWithAmitKumar" class="social-icon"><i class="fab fa-github"></i></a>
                    <a href="https://www.linkedin.com/in/amit-web-developer/" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#popular-destinations">Destinations</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contact</h3>
                <ul class="footer-links">
                    <li><a href="tel:+918984972877">+91 8984972877</a></li>
                    <li><a href="mailto:support@gojourney.com">support@gojourney.com</a></li>
                    <li>016 Travel Road, Baripada, Mayurbhanj, Odisha</li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for travel tips and exclusive offers.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your Email Address" required>
                    <button type="submit">Join</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> GoJourney. All Rights Reserved. Made with <i class="fas fa-heart" style="color: #ff6b6b;"></i> in India</p>
        </div>
    </footer>
</body>
</html>

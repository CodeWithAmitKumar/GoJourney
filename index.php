<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoJourney</title>
    <link rel="stylesheet" href="style.css">
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
    ?>

    <nav class="navbar">
        <a href="index.php" class="logo">GoJourney</a>
        <div class="nav-links">
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#services">Services</a>

            <a href="#">ContactUs</a>
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
                    <img src="images/destinations/paris.jpg" alt="Paris">
                    <div class="destination-tag">FEATURED</div>
                </div>
                <div class="destination-info">
                    <h3>Paris, France</h3>
                    <p class="destination-description">City of lights and romance</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.8</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$899</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/bali.jpg" alt="Bali">
                </div>
                <div class="destination-info">
                    <h3>Bali, Indonesia</h3>
                    <p class="destination-description">Paradise island getaway</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.9</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$799</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/kyoto.jpg" alt="Kyoto">
                </div>
                <div class="destination-info">
                    <h3>Kyoto, Japan</h3>
                    <p class="destination-description">Ancient temples and gardens</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.7</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$1,099</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/santorini.jpg" alt="Santorini">
                </div>
                <div class="destination-info">
                    <h3>Santorini, Greece</h3>
                    <p class="destination-description">Stunning white and blue views</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.9</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$949</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/newyork.jpg" alt="New York">
                </div>
                <div class="destination-info">
                    <h3>New York, USA</h3>
                    <p class="destination-description">The city that never sleeps</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.6</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$849</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/capetown.jpg" alt="Cape Town">
                </div>
                <div class="destination-info">
                    <h3>Cape Town, South Africa</h3>
                    <p class="destination-description">Where mountains meet the sea</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.7</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$999</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/maldives.jpg" alt="Maldives">
                </div>
                <div class="destination-info">
                    <h3>Maldives Islands</h3>
                    <p class="destination-description">Crystal clear waters and luxury</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.9</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$1,299</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="destination-card">
                <div class="destination-image">
                    <img src="images/destinations/dubai.jpg" alt="Dubai">
                </div>
                <div class="destination-info">
                    <h3>Dubai, UAE</h3>
                    <p class="destination-description">Modern luxury in the desert</p>
                    <div class="destination-meta">
                        <div class="destination-rating">
                            <span class="star">★</span>
                            <span>4.8</span>
                        </div>
                        <div class="destination-price">
                            <span>From</span>
                            <span class="price">$1,199</span>
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

            <p>Pack your bags — or just your imagination — and let GoJourney be your window to the world. The adventure of a lifetime might be just one click away.</p>


        </div>
        <div class="about-image">
            <img src="images/background/model2.png" alt="model-2">
        </div>
    </section>
    
    <section id="reviews" class="reviews-section">
        <h2>Meet Clients Reviews</h2>
        <div class="reviews-container">
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client1.jpg" alt="Client 1">
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
                    <img src="images/reviews/client3.jpg" alt="Client 3">
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
                    <p>"My husband and I used GoJourney for Picnic Party in Chandrabhaga Sea Beach. The itinerary they crafted was perfect - a beautiful balance of relaxation and adventure. Their attention to detail made our Picnic trip truly magical."</p>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-image">
                    <img src="images/reviews/client4.jpg" alt="Client 4">
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

    <script src="script.js"></script>
    <script>
        // Verify DOM loading
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document loaded from inline script');
            
            // Add a visible debug button
            const debugBtn = document.createElement('button');
            debugBtn.textContent = 'Test Toast';
            debugBtn.style.position = 'fixed';
            debugBtn.style.bottom = '20px';
            debugBtn.style.right = '20px';
            debugBtn.style.zIndex = '9999';
            debugBtn.style.padding = '10px 20px';
            debugBtn.style.backgroundColor = '#007bff';
            debugBtn.style.color = 'white';
            debugBtn.style.border = 'none';
            debugBtn.style.borderRadius = '5px';
            debugBtn.style.cursor = 'pointer';
            
            debugBtn.addEventListener('click', function() {
                console.log('Debug button clicked');
                // Direct toast implementation for debugging
                const toastContainer = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'toast-notification';
                toast.textContent = 'Direct toast test';
                toastContainer.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);
                
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 400);
                }, 3000);
            });
            
            document.body.appendChild(debugBtn);
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
                    loginModal.style.display = 'flex';
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
    </script>
</body>
</html>

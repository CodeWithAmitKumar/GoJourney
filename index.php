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
            <a href="#">About</a>
            <a href="#">Services</a>
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
    
    <div class="content">
        <!-- Your page content will go here -->
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Login</h2>
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
</body>
</html>

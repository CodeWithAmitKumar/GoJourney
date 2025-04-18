<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

$user_id = $_SESSION['user_id'];

// Enhanced security check - verify password hasn't changed since login
if (isset($_SESSION['password_hash_version']) && isset($_SESSION['user_id'])) {
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

// Check if the settings table exists, if not create it
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'user_settings'");
if (mysqli_num_rows($table_check) == 0) {
    $create_table = "CREATE TABLE user_settings (
        setting_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        email_notifications BOOLEAN DEFAULT 1,
        dark_mode BOOLEAN DEFAULT 0,
        language VARCHAR(10) DEFAULT 'english',
        currency VARCHAR(5) DEFAULT 'USD',
        date_format VARCHAR(20) DEFAULT 'MM/DD/YYYY',
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        $_SESSION['error'] = "Could not create settings table: " . mysqli_error($conn);
    }
}

// Check if user has settings, if not create default
$settings_check = mysqli_query($conn, "SELECT * FROM user_settings WHERE user_id = $user_id");
if (mysqli_num_rows($settings_check) == 0) {
    // Get dark mode preference from localStorage (if using the theme toggle)
    $dark_mode = 0; // Default light mode
    
    mysqli_query($conn, "INSERT INTO user_settings (user_id, email_notifications, dark_mode, language, currency, date_format) 
                         VALUES ($user_id, 1, $dark_mode, 'english', 'USD', 'MM/DD/YYYY')");
    
    $settings_check = mysqli_query($conn, "SELECT * FROM user_settings WHERE user_id = $user_id");
}

$settings = mysqli_fetch_assoc($settings_check);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process email notifications toggle
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    // Process dark mode toggle
    $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
    
    // Process language selection
    $language = mysqli_real_escape_string($conn, $_POST['language']);
    
    // Process currency selection
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    
    // Process date format selection
    $date_format = mysqli_real_escape_string($conn, $_POST['date_format']);
    
    // Update settings in database
    $update_query = "UPDATE user_settings SET 
                    email_notifications = $email_notifications,
                    dark_mode = $dark_mode,
                    language = '$language',
                    currency = '$currency',
                    date_format = '$date_format'
                    WHERE user_id = $user_id";
    
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = "Settings updated successfully!";
        
        // Refresh the page to get updated settings
        header("Location: settings.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating settings: " . mysqli_error($conn);
    }
}

// Process password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All password fields are required";
    } else if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
    } else if (strlen($new_password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long";
    } else {
        // Verify current password
        $user_query = mysqli_query($conn, "SELECT password_hash FROM users WHERE user_id = $user_id");
        
        if (!$user_query) {
            $_SESSION['error'] = "Database error: " . mysqli_error($conn);
            header("Location: settings.php");
            exit();
        }
        
        $user = mysqli_fetch_assoc($user_query);
        
        if (password_verify($current_password, $user['password_hash'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            error_log("Changing password for user ID: $user_id");
            
            // Update password with more detailed error reporting
            $update_sql = "UPDATE users SET password_hash = '$hashed_password' WHERE user_id = $user_id";
            $update_result = mysqli_query($conn, $update_sql);
            
            if ($update_result) {
                // Check if any rows were actually affected
                if (mysqli_affected_rows($conn) > 0) {
                    error_log("Password updated in database successfully");
                    
                    // Force expire the current session cookie
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 42000, '/');
                    }
                    
                    // Clear all session data
                    $_SESSION = array();
                    
                    // Destroy the session
                    session_destroy();
                    error_log("Session destroyed after password change");
                    
                    // Start new session for message
                    session_start();
                    session_regenerate_id(true);
                    error_log("New session started for success message");
                    
                    $_SESSION['success'] = "Password changed successfully! Please login with your new password.";
                    error_log("Redirecting to login page");
                    
                    // Redirect to login page
                    header("Location: ../index.php");
                    exit();
                } else {
                    // The query ran but no rows were updated
                    error_log("Query executed but no rows updated. Password might be the same.");
                    $_SESSION['success'] = "Query executed but no rows updated. Your password might already be set to this value.";
                    header("Location: settings.php");
                    exit();
                }
            } else {
                error_log("Error updating password: " . mysqli_error($conn));
                $_SESSION['error'] = "Error updating password: " . mysqli_error($conn) . " (SQL: $update_sql)";
                header("Location: settings.php");
                exit();
            }
        } else {
            error_log("Current password verification failed");
            $_SESSION['error'] = "Current password is incorrect";
        }
    }
}

// Delete account request
if (isset($_POST['delete_account'])) {
    // Set up a confirmation page or modal instead of immediately deleting
    $_SESSION['confirm_delete'] = true;
}

// Confirm account deletion
if (isset($_POST['confirm_delete'])) {
    $password = $_POST['confirm_password'];
    
    // Verify password
    $user_query = mysqli_query($conn, "SELECT password_hash FROM users WHERE user_id = $user_id");
    $user = mysqli_fetch_assoc($user_query);
    
    if (password_verify($password, $user['password_hash'])) {
        // Delete user data
        mysqli_query($conn, "DELETE FROM user_settings WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
        
        // Log out user
        session_destroy();
        
        // Start new session for message
        session_start();
        $_SESSION['success'] = "Your account has been deleted successfully.";
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = "Password is incorrect. Account not deleted.";
        $_SESSION['confirm_delete'] = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GoJourney</title>
    <link rel="icon" type="image/png" href="../images/logo&svg/svg2.png">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .settings-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .settings-header h1 {
            font-size: 2.2rem;
            color: #333;
            margin-bottom: 10px;
        }
        
        .settings-header p {
            color: #666;
        }
        
        .settings-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .settings-section h2 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .settings-section h2 i {
            color: #007bff;
        }
        
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .form-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .setting-label {
            display: flex;
            flex-direction: column;
        }
        
        .setting-label strong {
            margin-bottom: 5px;
            color: #444;
        }
        
        .setting-label span {
            color: #777;
            font-size: 0.9rem;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 30px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #007bff;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .select-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .select-wrapper select {
            appearance: none;
            padding: 10px 35px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-size: 1rem;
            cursor: pointer;
            min-width: 180px;
        }
        
        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            top: 10px;
            right: 15px;
            color: #777;
            pointer-events: none;
        }
        
        .danger-zone {
            background-color: #fff8f8;
            border-left: 4px solid #dc3545;
        }
        
        .danger-zone h2 {
            color: #dc3545;
        }
        
        .danger-zone h2 i {
            color: #dc3545;
        }
        
        .danger-zone .form-row {
            border-color: #ffe0e0;
        }
        
        .danger-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .danger-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .password-form {
            margin-top: 20px;
        }
        
        .password-form .form-group {
            margin-bottom: 15px;
        }
        
        .password-form label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        .password-form input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .password-form button {
            padding: 10px 20px;
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .confirm-delete {
            background-color: #fff0f0;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #ffcccc;
        }
        
        .confirm-delete h3 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        
        .confirm-delete p {
            margin-bottom: 15px;
            color: #555;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .submit-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        
        /* Dark theme adjustments */
        body.dark-theme .settings-section {
            background-color: #2a2a2a;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-theme .settings-header h1,
        body.dark-theme .settings-section h2 {
            color: #f5f5f5;
        }
        
        body.dark-theme .settings-header p,
        body.dark-theme .setting-label span {
            color: #aaa;
        }
        
        body.dark-theme .setting-label strong {
            color: #ddd;
        }
        
        body.dark-theme .settings-section h2,
        body.dark-theme .form-row {
            border-color: #444;
        }
        
        body.dark-theme .select-wrapper select {
            background-color: #333;
            border-color: #555;
            color: #f5f5f5;
        }
        
        body.dark-theme .danger-zone {
            background-color: #3a2a2a;
        }
        
        body.dark-theme .danger-zone .form-row {
            border-color: #553333;
        }
        
        body.dark-theme .confirm-delete {
            background-color: #3a2a2a;
            border-color: #663333;
        }
        
        body.dark-theme .confirm-delete p {
            color: #ddd;
        }
        
        body.dark-theme .password-form label {
            color: #ddd;
        }
        
        body.dark-theme .password-form input {
            background-color: #333;
            border-color: #555;
            color: #f5f5f5;
        }
    </style>
</head>
<body>
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
    
    <div class="dashboard-container">
        <div class="settings-container">
            <div class="settings-header">
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p>Customize your GoJourney experience</p>
            </div>
            
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
            
            <form method="post" action="settings.php">
                <!-- Preferences Section -->
                <div class="settings-section">
                    <h2><i class="fas fa-sliders-h"></i> Preferences</h2>
                    
                    <div class="form-row">
                        <div class="setting-label">
                            <strong>Email Notifications</strong>
                            <span>Receive emails about deals, trip updates, and account activity</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <div class="setting-label">
                            <strong>Dark Mode</strong>
                            <span>Toggle between light and dark themes</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="dark_mode" id="dark_mode_toggle" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <div class="setting-label">
                            <strong>Language</strong>
                            <span>Select your preferred language</span>
                        </div>
                        <div class="select-wrapper">
                            <select name="language">
                                <option value="english" <?php echo $settings['language'] == 'english' ? 'selected' : ''; ?>>English</option>
                                <option value="spanish" <?php echo $settings['language'] == 'spanish' ? 'selected' : ''; ?>>Spanish</option>
                                <option value="french" <?php echo $settings['language'] == 'french' ? 'selected' : ''; ?>>French</option>
                                <option value="german" <?php echo $settings['language'] == 'german' ? 'selected' : ''; ?>>German</option>
                                <option value="japanese" <?php echo $settings['language'] == 'japanese' ? 'selected' : ''; ?>>Japanese</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="setting-label">
                            <strong>Currency</strong>
                            <span>Set your preferred currency for prices</span>
                        </div>
                        <div class="select-wrapper">
                            <select name="currency">
                                <option value="USD" <?php echo $settings['currency'] == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                <option value="EUR" <?php echo $settings['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                                <option value="GBP" <?php echo $settings['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                                <option value="INR" <?php echo $settings['currency'] == 'INR' ? 'selected' : ''; ?>>INR (₹)</option>
                                <option value="JPY" <?php echo $settings['currency'] == 'JPY' ? 'selected' : ''; ?>>JPY (¥)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="setting-label">
                            <strong>Date Format</strong>
                            <span>Choose how dates are displayed</span>
                        </div>
                        <div class="select-wrapper">
                            <select name="date_format">
                                <option value="MM/DD/YYYY" <?php echo $settings['date_format'] == 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" <?php echo $settings['date_format'] == 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD" <?php echo $settings['date_format'] == 'YYYY-MM-DD' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">Save Preferences</button>
                </div>
            </form>
            
            <!-- Security Section -->
            <div class="settings-section">
                <h2><i class="fas fa-shield-alt"></i> Security</h2>
                
                <div class="form-row">
                    <div class="setting-label">
                        <strong>Change Password</strong>
                        <span>Update your password to keep your account secure</span>
                    </div>
                    <button type="button" id="show-password-form" class="submit-btn">Change Password</button>
                </div>
                
                <div id="password-form" class="password-form" style="display: none;">
                    <form method="post" action="settings.php">
                        <div class="form-group">
                            <label for="current-password">Current Password</label>
                            <input type="password" id="current-password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new-password">New Password</label>
                            <input type="password" id="new-password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm New Password</label>
                            <input type="password" id="confirm-password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="submit-btn">Update Password</button>
                    </form>
                </div>
            </div>
            
            <!-- Danger Zone Section -->
            <div class="settings-section danger-zone">
                <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
                
                <div class="form-row">
                    <div class="setting-label">
                        <strong>Delete Account</strong>
                        <span>Permanently delete your account and all associated data</span>
                    </div>
                    <form method="post" action="settings.php">
                        <button type="submit" name="delete_account" class="danger-btn">Delete Account</button>
                    </form>
                </div>
                
                <?php if (isset($_SESSION['confirm_delete']) && $_SESSION['confirm_delete']): ?>
                    <div class="confirm-delete">
                        <h3>Are you sure?</h3>
                        <p>This action cannot be undone. All your data, including your profile, settings, and travel history will be permanently deleted.</p>
                        <form method="post" action="settings.php">
                            <div class="form-group">
                                <label for="confirm-password-delete">Enter your password to confirm:</label>
                                <input type="password" id="confirm-password-delete" name="confirm_password" required>
                            </div>
                            <div class="btn-group">
                                <button type="submit" name="cancel_delete" class="cancel-btn">Cancel</button>
                                <button type="submit" name="confirm_delete" class="danger-btn">Yes, Delete My Account</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password form toggle
            const showPasswordFormBtn = document.getElementById('show-password-form');
            const passwordForm = document.getElementById('password-form');
            
            if (showPasswordFormBtn && passwordForm) {
                showPasswordFormBtn.addEventListener('click', function() {
                    if (passwordForm.style.display === 'none') {
                        passwordForm.style.display = 'block';
                        showPasswordFormBtn.textContent = 'Hide Form';
                    } else {
                        passwordForm.style.display = 'none';
                        showPasswordFormBtn.textContent = 'Change Password';
                    }
                });
            }
            
            // Dark mode toggle that also updates localStorage
            const darkModeToggle = document.getElementById('dark_mode_toggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', function() {
                    // This will be saved in the database when the form is submitted
                    // But we also need to update localStorage for the immediate visual effect
                    if (this.checked) {
                        document.body.classList.add('dark-theme');
                        localStorage.setItem('theme', 'dark');
                        
                        // Update theme toggle icons
                        const moonIcon = document.querySelector('.fa-moon');
                        const sunIcon = document.querySelector('.fa-sun');
                        if (moonIcon && sunIcon) {
                            moonIcon.style.display = 'none';
                            sunIcon.style.display = 'block';
                        }
                    } else {
                        document.body.classList.remove('dark-theme');
                        localStorage.setItem('theme', 'light');
                        
                        // Update theme toggle icons
                        const moonIcon = document.querySelector('.fa-moon');
                        const sunIcon = document.querySelector('.fa-sun');
                        if (moonIcon && sunIcon) {
                            moonIcon.style.display = 'block';
                            sunIcon.style.display = 'none';
                        }
                    }
                });
            }
            
            // Cancel delete account
            const cancelDeleteBtn = document.querySelector('button[name="cancel_delete"]');
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelector('.confirm-delete').style.display = 'none';
                    <?php $_SESSION['confirm_delete'] = false; ?>
                });
            }
            
            // Auto-dismiss notifications after 3 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                alerts.forEach(alert => {
                    setTimeout(() => {
                        // Add fade-out animation
                        alert.style.transition = 'opacity 0.5s ease';
                        alert.style.opacity = '0';
                        
                        // Remove the element after animation completes
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }, 3000); // Wait 3 seconds before starting to fade out
                });
            }
        });
    </script>
</body>
</html> 
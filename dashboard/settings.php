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
    <title>Account Settings - GoJourney</title>
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
                    <span>Account Settings</span>
                    <div class="welcome-subtitle">Manage your account preferences and security settings</div>
                </div>
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search destinations...">
                    <i class="fas fa-search search-icon"></i>
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
                
                <!-- Preferences Settings Card -->
                <div class="settings-card">
                    <h3><i class="fas fa-cog"></i> Preferences</h3>
                    
                    <form action="settings.php" method="POST">
                        <!-- Email Notifications -->
                        <div class="form-check">
                            <input type="checkbox" id="email_notifications" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            <label for="email_notifications">Receive email notifications</label>
                        </div>
                        
                        <!-- Dark Mode -->
                        <div class="form-check">
                            <input type="checkbox" id="dark_mode" name="dark_mode" <?php echo $settings['dark_mode'] ? 'checked' : ''; ?>>
                            <label for="dark_mode">Enable dark mode</label>
                        </div>
                        
                        <!-- Language -->
                        <div class="form-group">
                            <label for="language">Language</label>
                            <select id="language" name="language" class="form-control">
                                <option value="english" <?php echo $settings['language'] == 'english' ? 'selected' : ''; ?>>English</option>
                                <option value="spanish" <?php echo $settings['language'] == 'spanish' ? 'selected' : ''; ?>>Spanish</option>
                                <option value="french" <?php echo $settings['language'] == 'french' ? 'selected' : ''; ?>>French</option>
                                <option value="german" <?php echo $settings['language'] == 'german' ? 'selected' : ''; ?>>German</option>
                                <option value="italian" <?php echo $settings['language'] == 'italian' ? 'selected' : ''; ?>>Italian</option>
                            </select>
                        </div>
                        
                        <!-- Currency -->
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <select id="currency" name="currency" class="form-control">
                                <option value="USD" <?php echo $settings['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar ($)</option>
                                <option value="EUR" <?php echo $settings['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (€)</option>
                                <option value="GBP" <?php echo $settings['currency'] == 'GBP' ? 'selected' : ''; ?>>British Pound (£)</option>
                                <option value="JPY" <?php echo $settings['currency'] == 'JPY' ? 'selected' : ''; ?>>Japanese Yen (¥)</option>
                                <option value="AUD" <?php echo $settings['currency'] == 'AUD' ? 'selected' : ''; ?>>Australian Dollar (A$)</option>
                            </select>
                        </div>
                        
                        <!-- Date Format -->
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format" class="form-control">
                                <option value="MM/DD/YYYY" <?php echo $settings['date_format'] == 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                <option value="DD/MM/YYYY" <?php echo $settings['date_format'] == 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                <option value="YYYY-MM-DD" <?php echo $settings['date_format'] == 'YYYY-MM-DD' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="save-settings-btn"><i class="fas fa-save"></i> Save Preferences</button>
                    </form>
                </div>
                
                <!-- Security Settings Card -->
                <div class="settings-card">
                    <h3><i class="fas fa-lock"></i> Security</h3>
                    
                    <form action="settings.php" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                            <small>Password must be at least 6 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <button type="submit" class="change-password-btn"><i class="fas fa-key"></i> Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
</body>
</html> 
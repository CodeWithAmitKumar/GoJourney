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
                         VALUES ($user_id, 1, $dark_mode, 'english', 'INR', 'DD/MM/YYYY')");
    
    $settings_check = mysqli_query($conn, "SELECT * FROM user_settings WHERE user_id = $user_id");
}

$settings = mysqli_fetch_assoc($settings_check);

// Handle form submission for settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account'])) {
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

// Handle account deletion
if (isset($_POST['delete_account'])) {
    // Verify the DELETE confirmation text
    if (isset($_POST['confirm_delete_text']) && $_POST['confirm_delete_text'] === 'DELETE') {
        // For debugging
        error_log("Account deletion initiated for user ID: $user_id");
        
        // Check if there are any other tables with foreign key relationships
        $tables_to_check = ['user_settings']; // Add any other tables with foreign keys to user
        $deletion_errors = false;
        
        // First delete from tables with foreign key constraints
        foreach ($tables_to_check as $table) {
            $delete_query = "DELETE FROM $table WHERE user_id = $user_id";
            if (!mysqli_query($conn, $delete_query)) {
                $deletion_errors = true;
                error_log("Error deleting from $table: " . mysqli_error($conn));
            }
        }
        
        if (!$deletion_errors) {
            // Now delete the user record
            $delete_user = "DELETE FROM users WHERE user_id = $user_id";
            if (mysqli_query($conn, $delete_user)) {
                // Successfully deleted
                error_log("User ID: $user_id successfully deleted");
                
                // Clear the session
                session_unset();
                session_destroy();
                
                // Create new session for success message
                session_start();
                $_SESSION['success'] = "Your account has been deleted successfully.";
                header("Location: ../index.php");
                exit();
            } else {
                error_log("Error deleting user: " . mysqli_error($conn));
                $_SESSION['error'] = "Error deleting your account: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "Could not delete account due to database constraints. Please contact support.";
        }
    } else {
        $_SESSION['error'] = "You must type DELETE (in all caps) to confirm account deletion.";
    }
    
    // If we reach here, there was an error - redirect back to settings
    header("Location: settings.php");
    exit();
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
    <style>
        .delete-account-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }
        
        .delete-account-section h4 {
            color: #d9534f;
            margin-bottom: 10px;
        }
        
        .delete-account-section p {
            color: #d9534f;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .delete-account-btn {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .delete-account-btn:hover {
            background-color: #c9302c;
        }
        
        .delete-confirmation {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
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
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="welcome-message">
                    <span>Account Settings</span>
                    <div class="welcome-subtitle">Manage your account preferences and security settings</div>
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
                                <option value="INR" <?php echo $settings['currency'] == 'INR' ? 'selected' : ''; ?>>Indian Rupee (₹)</option>
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
                
                <!-- Account Management Card -->
                <div class="settings-card">
                    <h3><i class="fas fa-user-cog"></i> Account Management</h3>
                    
                    <!-- Delete Account Section -->
                    <div class="delete-account-section">
                        <h4><i class="fas fa-exclamation-triangle"></i> Delete Account</h4>
                        <p class="delete-warning">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                        
                        <button type="button" id="show-delete-confirmation" class="delete-account-btn">Delete My Account</button>
                        
                        <div id="delete-confirmation" class="delete-confirmation" style="display: none;">
                            <form action="settings.php" method="POST">
                                <p><strong>Are you absolutely sure you want to delete your account?</strong></p>
                                <p>Please type DELETE in the field below to confirm:</p>
                                
                                <div class="form-group">
                                    <input type="text" name="confirm_delete_text" class="form-control" placeholder="Type DELETE here" required>
                                </div>
                                
                                <input type="hidden" name="delete_account" value="1">
                                <button type="submit" class="delete-account-btn">Confirm Delete Account</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
    <script>
        // Toggle delete confirmation form
        document.getElementById('show-delete-confirmation').addEventListener('click', function() {
            document.getElementById('delete-confirmation').style.display = 'block';
            this.style.display = 'none';
        });
        
        // Handle dark mode toggle checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeCheckbox = document.getElementById('dark_mode');
            
            // Apply dark mode if checkbox is checked on page load
            if (darkModeCheckbox.checked) {
                document.body.classList.add('dark-theme');
                localStorage.setItem('theme', 'dark');
                
                // Update the theme toggle button icons
                const moonIcon = document.querySelector('.fa-moon');
                const sunIcon = document.querySelector('.fa-sun');
                if (moonIcon && sunIcon) {
                    moonIcon.style.display = 'none';
                    sunIcon.style.display = 'block';
                }
            }
            
            // Toggle dark mode when checkbox is clicked
            darkModeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    // Enable dark mode
                    document.body.classList.add('dark-theme');
                    localStorage.setItem('theme', 'dark');
                    
                    // Update theme toggle button icons
                    const moonIcon = document.querySelector('.fa-moon');
                    const sunIcon = document.querySelector('.fa-sun');
                    if (moonIcon && sunIcon) {
                        moonIcon.style.display = 'none';
                        sunIcon.style.display = 'block';
                    }
                } else {
                    // Disable dark mode
                    document.body.classList.remove('dark-theme');
                    localStorage.setItem('theme', 'light');
                    
                    // Update theme toggle button icons
                    const moonIcon = document.querySelector('.fa-moon');
                    const sunIcon = document.querySelector('.fa-sun');
                    if (moonIcon && sunIcon) {
                        moonIcon.style.display = 'block';
                        sunIcon.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>
</html> 
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Set default values
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

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

// Check and create needed database columns
// First verify the connection is valid
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if users table has necessary columns, if not add them
$columns_to_check = ['gender', 'age', 'last_login', 'created_at'];
foreach ($columns_to_check as $column) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE '$column'");
    if ($result && mysqli_num_rows($result) == 0) {
        if ($column == 'gender') {
            mysqli_query($conn, "ALTER TABLE users ADD gender VARCHAR(20) DEFAULT NULL");
        } elseif ($column == 'age') {
            mysqli_query($conn, "ALTER TABLE users ADD age INT DEFAULT NULL");
        } elseif ($column == 'last_login') {
            mysqli_query($conn, "ALTER TABLE users ADD last_login DATETIME DEFAULT NULL");
        } elseif ($column == 'created_at') {
            mysqli_query($conn, "ALTER TABLE users ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
    }
}

// Get current user data with error handling
$user = [];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Failed to retrieve user data";
        header("Location: index.php");
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Database query error: " . mysqli_error($conn);
    header("Location: index.php");
    exit();
}

// Check if form was submitted (profile update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with proper security measures
    $full_name = isset($_POST['full_name']) ? mysqli_real_escape_string($conn, trim($_POST['full_name'])) : '';
    $gender = isset($_POST['gender']) && !empty($_POST['gender']) ? mysqli_real_escape_string($conn, trim($_POST['gender'])) : NULL;
    $age = isset($_POST['age']) && !empty($_POST['age']) ? (int)$_POST['age'] : NULL;
    
    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Name cannot be empty";
    }
    
    if (!empty($age) && ($age < 13 || $age > 120)) {
        $errors[] = "Age must be between 13 and 120";
    }
    
    // If no errors, update user profile using prepared statement
    if (empty($errors)) {
        $update_sql = "UPDATE users SET full_name = ?, gender = ?, age = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssii", $full_name, $gender, $age, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Update the session variable with the new name
                $_SESSION['name'] = $full_name;
                $_SESSION['success'] = "Profile updated successfully!";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update profile: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        }
    } else {
        // Set error message
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GoJourney</title>
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
                    <span>My Profile</span>
                    <div class="welcome-subtitle">Update your personal information and manage your account</div>
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
                            echo htmlspecialchars($_SESSION['success']); 
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php 
                            echo htmlspecialchars($_SESSION['error']); 
                            unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-form-card">
                    <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
                    
                    <form action="profile.php" method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email_display">Email Address</label>
                            <div class="input-with-icon">
                                <input type="email" id="email_display" name="email_display" class="form-control" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : (isset($user['user_email']) ? htmlspecialchars($user['user_email']) : ''); ?>" readonly>
                                <span class="verified-badge"><i class="fas fa-check-circle verified-icon" title="Verified Email"></i></span>
                            </div>
                            <small class="form-text">Email cannot be changed here. Contact support if you need to update it.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo (isset($user['gender']) && $user['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                <option value="prefer_not_to_say" <?php echo (isset($user['gender']) && $user['gender'] == 'prefer_not_to_say') ? 'selected' : ''; ?>>Prefer not to say</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" class="form-control" value="<?php echo isset($user['age']) ? htmlspecialchars($user['age']) : ''; ?>" min="13" max="120">
                        </div>
                        
                        <button type="submit" class="edit-profile-btn"><i class="fas fa-save"></i> Save Changes</button>
                    </form>
                </div>
                
                <div class="profile-form-card">
                    <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    <p><strong>User ID:</strong> <?php echo isset($user['user_id']) ? htmlspecialchars($user['user_id']) : 'N/A'; ?></p>
                    
                    <p><strong>Account Created:</strong> 
                    <?php 
                        if (isset($user['created_at']) && !empty($user['created_at']) && $user['created_at'] !== '0000-00-00 00:00:00') {
                            try {
                                echo date('F j, Y', strtotime($user['created_at']));
                            } catch (Exception $e) {
                                echo 'Date format error';
                            }
                        } else {
                            echo 'Not available';
                        }
                    ?>
                    </p>
                    
                    <p><strong>Last Login:</strong> 
                    <?php 
                        if (isset($user['last_login']) && !empty($user['last_login']) && $user['last_login'] !== '0000-00-00 00:00:00') {
                            try {
                                echo date('F j, Y, g:i a', strtotime($user['last_login']));
                            } catch (Exception $e) {
                                echo 'Date format error';
                            }
                        } else {
                            echo 'First login or not recorded';
                        }
                    ?>
                    </p>
                    
                    <a href="settings.php" class="edit-profile-btn"><i class="fas fa-cog"></i> Account Settings</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
</body>
</html> 
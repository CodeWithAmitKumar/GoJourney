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

// Get current user data
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    $_SESSION['error'] = "Failed to retrieve user data: " . mysqli_error($conn);
    header("Location: index.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

// Check if users table has gender and age columns, if not add them
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'gender'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD gender VARCHAR(10) DEFAULT NULL");
}

$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'age'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD age INT DEFAULT NULL");
}

// Check if form was submitted (profile update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $gender = isset($_POST['gender']) ? mysqli_real_escape_string($conn, trim($_POST['gender'])) : NULL;
    $age = isset($_POST['age']) && !empty($_POST['age']) ? (int)$_POST['age'] : NULL;
    
    // Validate input
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Name cannot be empty";
    }
    
    if (!empty($age) && ($age < 13 || $age > 120)) {
        $errors[] = "Age must be between 13 and 120";
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        $update_sql = "UPDATE users SET 
                       full_name = '$full_name', 
                       gender = " . ($gender ? "'$gender'" : "NULL") . ", 
                       age = " . ($age ? $age : "NULL") . " 
                       WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $update_sql)) {
            // Update the session variable with the new name
            $_SESSION['name'] = $full_name;
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile: " . mysqli_error($conn);
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
    <link rel="icon" type="image/png" href="../images/logo&svg/svg2.png">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }
        
        .profile-form h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .submit-btn {
            padding: 12px 24px;
            background: linear-gradient(90deg, #007bff, #00c6ff);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        
        body.dark-theme .profile-form {
            background-color: #2a2a2a;
            color: #f5f5f5;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-theme .profile-form h2,
        body.dark-theme .form-group label {
            color: #f5f5f5;
        }
        
        body.dark-theme .form-control {
            background-color: #3a3a3a;
            border-color: #444;
            color: #f5f5f5;
        }
        
        body.dark-theme .form-control:focus {
            border-color: #007bff;
            background-color: #333;
        }
        
        .profile-header h1 {
            font-size: 2rem;
            color: #333;
        }
        
        body.dark-theme .profile-header h1 {
            color: #f5f5f5;
        }
        
        .profile-header p {
            color: #666;
            margin-top: 5px;
        }
        
        body.dark-theme .profile-header p {
            color: #aaa;
        }
        
        .profile-header {
            border-bottom: 1px solid #eee;
        }
        
        body.dark-theme .profile-header {
            border-bottom: 1px solid #444;
        }
        
        /* Message styles */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        body.dark-theme .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #98c379;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        body.dark-theme .alert-error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #e06c75;
            border: 1px solid rgba(220, 53, 69, 0.3);
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
        <div class="profile-container">
            <div class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your personal information</p>
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
            
            <div class="profile-form">
                <h2>Edit Profile</h2>
                <form action="profile.php" method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['user_email']); ?>" disabled>
                        <small style="color: #666; display: block; margin-top: 5px;">Email cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" id="male" name="gender" value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'checked' : ''; ?>>
                                <label for="male">Male</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="female" name="gender" value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'checked' : ''; ?>>
                                <label for="female">Female</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" id="other" name="gender" value="other" <?php echo (isset($user['gender']) && $user['gender'] == 'other') ? 'checked' : ''; ?>>
                                <label for="other">Other</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" class="form-control" min="13" max="120" value="<?php echo isset($user['age']) ? htmlspecialchars($user['age']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../script.js"></script>
    <script src="dashboard.js"></script>
</body>
</html> 
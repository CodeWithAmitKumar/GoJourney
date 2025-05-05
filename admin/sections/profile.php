<?php
// Get current admin info from session
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 1;
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
$admin_email = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@example.com';

// Check if profile update form was submitted
$success_message = "";
$error_message = "";

if(isset($_POST['update_profile'])) {
    $new_name = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $new_email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    if(empty($new_name)) {
        $errors[] = "Full name is required";
    }
    
    if(empty($new_email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
    
    // Check if admins table exists
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
    
    // If admins table doesn't exist, create it
    if(mysqli_num_rows($check_table) == 0) {
        $create_table_sql = "CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        )";
        
        if(!mysqli_query($conn, $create_table_sql)) {
            $errors[] = "Failed to create admins table: " . mysqli_error($conn);
        } else {
            // Insert current admin details into table
            $default_password_hash = password_hash('admin123', PASSWORD_DEFAULT); // Default password
            $insert_admin_sql = "INSERT INTO admins (id, fullname, email, password_hash) 
                                VALUES (1, '$admin_name', '$admin_email', '$default_password_hash')";
            mysqli_query($conn, $insert_admin_sql);
        }
    }
    
    // If no errors, proceed with update
    if(empty($errors)) {
        // Check if changing password
        $password_update = "";
        if(!empty($new_password)) {
            // Validate password
            if(strlen($new_password) < 8) {
                $errors[] = "Password must be at least 8 characters";
            } elseif($new_password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            } else {
                // Check current password if admins table exists
                $check_admins = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
                if(mysqli_num_rows($check_admins) > 0) {
                    $admin_result = mysqli_query($conn, "SELECT * FROM admins WHERE id = $admin_id");
                    if($admin_result && mysqli_num_rows($admin_result) > 0) {
                        $admin_data = mysqli_fetch_assoc($admin_result);
                        if(!password_verify($current_password, $admin_data['password_hash'])) {
                            $errors[] = "Current password is incorrect";
                        } else {
                            // Password verified, update with new password
                            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $password_update = ", password_hash = '$new_password_hash'";
                        }
                    }
                } else {
                    // If no admins table, just update the password without verification
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $password_update = ", password_hash = '$new_password_hash'";
                }
            }
        }
        
        if(empty($errors)) {
            // Update admin in database
            $check_admins = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
            if(mysqli_num_rows($check_admins) > 0) {
                $update_sql = "UPDATE admins SET fullname = '$new_name', email = '$new_email' $password_update WHERE id = $admin_id";
                if(mysqli_query($conn, $update_sql)) {
                    // Update session variables
                    $_SESSION['admin_name'] = $new_name;
                    $_SESSION['admin_email'] = $new_email;
                    
                    $admin_name = $new_name;
                    $admin_email = $new_email;
                    
                    $success_message = "Profile updated successfully!";
                } else {
                    $error_message = "Failed to update profile: " . mysqli_error($conn);
                }
            } else {
                // Update session variables only if no database
                $_SESSION['admin_name'] = $new_name;
                $_SESSION['admin_email'] = $new_email;
                
                $admin_name = $new_name;
                $admin_email = $new_email;
                
                $success_message = "Profile updated successfully!";
            }
        } else {
            $error_message = implode("<br>", $errors);
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}

// Get admin data from database if available
$admin_data = [
    'fullname' => $admin_name,
    'email' => $admin_email,
    'created_at' => date('Y-m-d H:i:s'),
    'last_login' => date('Y-m-d H:i:s')
];

$check_admins = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
if(mysqli_num_rows($check_admins) > 0) {
    $admin_result = mysqli_query($conn, "SELECT * FROM admins WHERE id = $admin_id");
    if($admin_result && mysqli_num_rows($admin_result) > 0) {
        $admin_data = mysqli_fetch_assoc($admin_result);
    }
}
?>

<div class="dashboard-title">
    <h2>Admin Profile</h2>
</div>

<?php if(!empty($success_message)): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
<?php endif; ?>

<?php if(!empty($error_message)): ?>
    <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<div class="content-section">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($admin_data['fullname'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($admin_data['fullname']); ?></h3>
                <p><?php echo htmlspecialchars($admin_data['email']); ?></p>
                <span class="admin-badge">Administrator</span>
            </div>
        </div>
        
        <div class="profile-details">
            <div class="detail-item">
                <div class="detail-label">Admin ID</div>
                <div class="detail-value"><?php echo $admin_id; ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Account Created</div>
                <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($admin_data['created_at'])); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Last Login</div>
                <div class="detail-value">
                    <?php 
                        echo !empty($admin_data['last_login']) 
                            ? date('d M Y, h:i A', strtotime($admin_data['last_login'])) 
                            : 'Not recorded'; 
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="profile-form-container">
        <h3>Update Profile</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($admin_data['fullname']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
            </div>
            
            <div class="password-section">
                <h4>Change Password</h4>
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password">
                    <small class="form-hint">Must be at least 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn-primary">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.profile-container {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.profile-header {
    display: flex;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 500;
    margin-right: 20px;
}

.profile-info h3 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
    font-size: 1.5rem;
}

.profile-info p {
    margin: 0 0 10px 0;
    color: var(--secondary-color);
}

.admin-badge {
    background-color: #FF5722;
    color: white;
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.profile-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.detail-item {
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.detail-label {
    color: var(--secondary-color);
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.detail-value {
    font-weight: 600;
    color: var(--dark-color);
}

.profile-form-container {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
}

.profile-form-container h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: var(--dark-color);
    font-size: 1.3rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--dark-color);
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.password-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.password-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--dark-color);
    font-size: 1.1rem;
}

.form-hint {
    display: block;
    color: var(--secondary-color);
    font-size: 0.85rem;
    margin-top: 5px;
}

.form-actions {
    margin-top: 30px;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-primary:hover {
    background-color: #375990;
}
</style> 
<?php
// Check for admin add form submission
if(isset($_POST['add_admin']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    if(empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    if(empty($email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
    
    if(empty($password)) {
        $errors[] = "Password is required";
    } elseif(strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if admin table exists, if not create it
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
    if(mysqli_num_rows($check_table) == 0) {
        // Create admins table
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
        }
    }
    
    // If no errors, proceed with admin creation
    if(empty($errors)) {
        // Check if email already exists
        $check_email = mysqli_query($conn, "SELECT * FROM admins WHERE email = '$email'");
        if(mysqli_num_rows($check_email) > 0) {
            $errors[] = "Email already exists";
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin
            $insert_sql = "INSERT INTO admins (fullname, email, password_hash) VALUES ('$fullname', '$email', '$password_hash')";
            if(mysqli_query($conn, $insert_sql)) {
                $success_message = "Admin added successfully!";
            } else {
                $errors[] = "Failed to add admin: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all admins
$admins = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
if(mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT * FROM admins ORDER BY created_at DESC");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $admins[] = $row;
        }
    }
}
?>

<div class="dashboard-title">
    <h2>Admin Management</h2>
</div>

<!-- Add Admin Form -->
<div class="content-section">
    <h3>Add New Admin</h3>
    
    <?php if(isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if(isset($success_message)): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" style="max-width: 600px;">
        <div style="margin-bottom: 15px;">
            <label for="fullname" style="display: block; margin-bottom: 5px; font-weight: 500;">Full Name:</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo isset($fullname) ? $fullname : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="password" style="display: block; margin-bottom: 5px; font-weight: 500;">Password:</label>
            <input type="password" id="password" name="password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            <small style="color: #6c757d; display: block; margin-top: 5px;">Password must be at least 8 characters long.</small>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: 500;">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div>
            <button type="submit" name="add_admin" style="background: linear-gradient(to right, #4776E6, #8E54E9); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-user-plus"></i> Add Admin
            </button>
        </div>
    </form>
</div>

<!-- Admin List -->
<div class="content-section">
    <h3>Admin List</h3>
    
    <?php if(empty($admins)): ?>
        <p style="color: #6c757d; font-style: italic;">No admins found. Add a new admin using the form above.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($admins as $admin): ?>
                        <tr>
                            <td><?php echo $admin['id']; ?></td>
                            <td><?php echo $admin['fullname']; ?></td>
                            <td><?php echo $admin['email']; ?></td>
                            <td>
                                <?php if($admin['is_active'] == 1): ?>
                                    <span class="status completed">Active</span>
                                <?php else: ?>
                                    <span class="status cancelled">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                            <td>
                                <?php 
                                if($admin['last_login']) {
                                    echo date('d M Y H:i', strtotime($admin['last_login']));
                                } else {
                                    echo 'Never';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="?section=admins&action=edit&id=<?php echo $admin['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                <?php if($admin['email'] !== 'gojourneyamitk@admin.com'): ?>
                                    <a href="?section=admins&action=delete&id=<?php echo $admin['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this admin?');"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?> 
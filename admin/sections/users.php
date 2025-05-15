<?php
// First, check if the is_active column exists in the users table
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'is_active'");
if(mysqli_num_rows($check_column) == 0) {
    // Column doesn't exist, add it
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
}

// Check if phone_number column exists
$check_phone_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'phone_number'");
if(mysqli_num_rows($check_phone_column) == 0) {
    // Column doesn't exist, add it
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULL");
}

// View User Details
if(isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Fetch user details
    $user_query = "SELECT * FROM users WHERE user_id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    
    if($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        ?>
        <div class="dashboard-title">
            <h2>User Details</h2>
            <a href="?section=users" class="btn btn-primary" style="padding: 8px 15px; border-radius: 5px; text-decoration: none; background-color: var(--primary-color); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
        
        <div class="content-section">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px;">
                <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="width: 100px; height: 100px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto;">
                            <?php echo substr($user['full_name'], 0, 1); ?>
                        </div>
                        <h3 style="margin-top: 15px; margin-bottom: 5px;"><?php echo $user['full_name']; ?></h3>
                        <p style="color: #6c757d; margin-top: 5px;">User ID: <?php echo $user['user_id']; ?></p>
                        <span class="status <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'completed' : 'cancelled'; ?>" style="display: inline-block; margin-top: 10px;">
                            <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="?section=users&action=toggle_status&id=<?php echo $user['user_id']; ?>" class="btn btn-<?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'danger' : 'success'; ?>" style="display: block; width: 100%; padding: 10px; text-align: center; border-radius: 5px; text-decoration: none; background-color: <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'var(--danger-color)' : 'var(--success-color)'; ?>; color: white; margin-bottom: 10px;">
                            <i class="fas <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'fa-user-slash' : 'fa-user-check'; ?>"></i> 
                            <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'Deactivate User' : 'Activate User'; ?>
                        </a>
                        
                        <a href="?section=users&action=edit&id=<?php echo $user['user_id']; ?>" class="btn btn-primary" style="display: block; width: 100%; padding: 10px; text-align: center; border-radius: 5px; text-decoration: none; background-color: var(--primary-color); color: white;">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                    </div>
                </div>
                
                <div>
                    <div style="background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
                        <h4 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">User Information</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Full Name</p>
                                <p style="margin-top: 0; font-weight: 500;"><?php echo $user['full_name']; ?></p>
                            </div>
                            
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Email Address</p>
                                <p style="margin-top: 0; font-weight: 500;"><?php echo $user['user_email']; ?></p>
                            </div>
                            
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Phone Number</p>
                                <p style="margin-top: 0; font-weight: 500;">
                                    <?php echo !empty($user['phone_number']) ? $user['phone_number'] : '<em style="color: #999;">Not set</em>'; ?>
                                </p>
                            </div>
                            
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Joined Date</p>
                                <p style="margin-top: 0; font-weight: 500;"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Last Login</p>
                                <p style="margin-top: 0; font-weight: 500;">
                                    <?php 
                                    if(!empty($user['last_login'])) {
                                        echo date('d M Y H:i', strtotime($user['last_login']));
                                    } else {
                                        echo '<em style="color: #999;">Never</em>';
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <div>
                                <p style="margin-bottom: 5px; color: #6c757d; font-size: 14px;">Status</p>
                                <p style="margin-top: 0; font-weight: 500;">
                                    <span class="status <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'completed' : 'cancelled'; ?>">
                                        <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return; // Stop processing the rest of the file
    }
}

// User Edit Form
if(isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
        $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
        $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
        $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $update_query = "UPDATE users SET 
                        full_name = '$full_name',
                        user_email = '$user_email',
                        phone_number = '$phone_number',
                        is_active = $is_active
                    WHERE user_id = $user_id";
                    
        if(mysqli_query($conn, $update_query)) {
            $action_message = "User updated successfully.";
            $action_status = "success";
        } else {
            $action_message = "Error updating user: " . mysqli_error($conn);
            $action_status = "danger";
        }
    }
    
    // Fetch user details
    $user_query = "SELECT * FROM users WHERE user_id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    
    if($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        ?>
        <div class="dashboard-title">
            <h2>Edit User</h2>
            <a href="?section=users" class="btn btn-primary" style="padding: 8px 15px; border-radius: 5px; text-decoration: none; background-color: var(--primary-color); color: white;">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
        
        <?php if(isset($action_message)): ?>
            <div class="alert alert-<?php echo $action_status; ?>" style="background-color: <?php echo $action_status === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $action_status === 'success' ? '#155724' : '#721c24'; ?>; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $action_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-section">
            <form method="POST" action="" style="max-width: 600px; margin: 0 auto;">
                <div style="margin-bottom: 20px;">
                    <label for="full_name" style="display: block; margin-bottom: 5px; font-weight: 500;">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="user_email" style="display: block; margin-bottom: 5px; font-weight: 500;">Email Address</label>
                    <input type="email" id="user_email" name="user_email" value="<?php echo htmlspecialchars($user['user_email']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label for="phone_number" style="display: block; margin-bottom: 5px; font-weight: 500;">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo (isset($user['is_active']) && $user['is_active'] == 1) ? 'checked' : ''; ?> style="margin-right: 10px;">
                        <span>Active User</span>
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_user" style="background-color: var(--primary-color); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; flex: 1;">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="?section=users" style="background-color: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 5px; text-decoration: none; text-align: center; flex: 1;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        <?php
        return; // Stop processing the rest of the file
    }
}

// Action handlers
if(isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Fetch current status
    $result = mysqli_query($conn, "SELECT is_active FROM users WHERE user_id = $user_id");
    if($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $new_status = $row['is_active'] ? 0 : 1;
        
        // Update status
        if(mysqli_query($conn, "UPDATE users SET is_active = $new_status WHERE user_id = $user_id")) {
            $action_message = "User status updated successfully.";
            $action_status = "success";
        } else {
            $action_message = "Error updating user status: " . mysqli_error($conn);
            $action_status = "danger";
        }
    }
}

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$search_condition = '';
if(!empty($search)) {
    $search_condition = " WHERE 
                            full_name LIKE '%$search%' OR 
                            user_email LIKE '%$search%' OR 
                            phone_number LIKE '%$search%'";
}

// Fetch total users count
$total_records_query = "SELECT COUNT(*) as total FROM users" . $search_condition;
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records = 0;
if($total_records_result) {
    $total_records_row = mysqli_fetch_assoc($total_records_result);
    $total_records = $total_records_row['total'];
}
$total_pages = ceil($total_records / $records_per_page);

// Fetch users
$users = [];
$sql = "SELECT * FROM users" . $search_condition . " ORDER BY last_login DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);
if($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Fetch booking stats
$user_booking_stats = [];
if(!empty($users)) {
    $user_ids = array_column($users, 'user_id');
    $user_ids_str = implode(',', $user_ids);
    
    // Check if hotel_bookings table exists
    $check_hotel_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
    if(mysqli_num_rows($check_hotel_bookings) > 0) {
        $hotel_bookings_query = "SELECT user_id, COUNT(*) as count FROM hotel_bookings WHERE user_id IN ($user_ids_str) GROUP BY user_id";
        $hotel_bookings_result = mysqli_query($conn, $hotel_bookings_query);
        if($hotel_bookings_result) {
            while($row = mysqli_fetch_assoc($hotel_bookings_result)) {
                if(!isset($user_booking_stats[$row['user_id']])) {
                    $user_booking_stats[$row['user_id']] = ['hotel' => 0, 'flight' => 0, 'train' => 0];
                }
                $user_booking_stats[$row['user_id']]['hotel'] = $row['count'];
            }
        }
    }
    
    // Check if flight_bookings table exists
    $check_flight_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'flight_bookings'");
    if(mysqli_num_rows($check_flight_bookings) > 0) {
        $flight_bookings_query = "SELECT user_id, COUNT(*) as count FROM flight_bookings WHERE user_id IN ($user_ids_str) GROUP BY user_id";
        $flight_bookings_result = mysqli_query($conn, $flight_bookings_query);
        if($flight_bookings_result) {
            while($row = mysqli_fetch_assoc($flight_bookings_result)) {
                if(!isset($user_booking_stats[$row['user_id']])) {
                    $user_booking_stats[$row['user_id']] = ['hotel' => 0, 'flight' => 0, 'train' => 0];
                }
                $user_booking_stats[$row['user_id']]['flight'] = $row['count'];
            }
        }
    }
    
    // Check if train_bookings table exists
    $check_train_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'train_bookings'");
    if(mysqli_num_rows($check_train_bookings) > 0) {
        $train_bookings_query = "SELECT user_id, COUNT(*) as count FROM train_bookings WHERE user_id IN ($user_ids_str) GROUP BY user_id";
        $train_bookings_result = mysqli_query($conn, $train_bookings_query);
        if($train_bookings_result) {
            while($row = mysqli_fetch_assoc($train_bookings_result)) {
                if(!isset($user_booking_stats[$row['user_id']])) {
                    $user_booking_stats[$row['user_id']] = ['hotel' => 0, 'flight' => 0, 'train' => 0];
                }
                $user_booking_stats[$row['user_id']]['train'] = $row['count'];
            }
        }
    }
}
?>

<div class="dashboard-title">
    <h2>User Management</h2>
</div>

<?php if(isset($action_message)): ?>
    <div class="alert alert-<?php echo $action_status; ?>" style="background-color: <?php echo $action_status === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $action_status === 'success' ? '#155724' : '#721c24'; ?>; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $action_message; ?>
    </div>
<?php endif; ?>

<!-- User Search and Stats -->
<div class="content-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <form method="GET" action="" style="max-width: 400px; width: 100%;">
            <input type="hidden" name="section" value="users">
            <div style="display: flex;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search users..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px 0 0 5px;">
                <button type="submit" style="background-color: var(--primary-color); color: white; border: none; padding: 10px 15px; border-radius: 0 5px 5px 0; cursor: pointer;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <div>
            <span style="color: #6c757d; font-size: 14px;">
                Total Users: <strong><?php echo $total_records; ?></strong>
            </span>
        </div>
    </div>
    
    <?php if(empty($users)): ?>
        <div style="text-align: center; padding: 30px; background-color: #f8f9fa; border-radius: 5px;">
            <?php if(!empty($search)): ?>
                <p style="color: #6c757d;">No users found matching your search criteria.</p>
                <a href="?section=users" style="color: var(--primary-color); text-decoration: none;">Clear search</a>
            <?php else: ?>
                <p style="color: #6c757d;">No users registered yet.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Join Date</th>
                        <th>Last Login</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['user_email']; ?></td>
                            <td><?php echo !empty($user['phone_number']) ? $user['phone_number'] : '<em style="color: #999;">Not set</em>'; ?></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php 
                                if(!empty($user['last_login'])) {
                                    echo date('d M Y H:i', strtotime($user['last_login']));
                                } else {
                                    echo '<em style="color: #999;">Never</em>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $bookings = isset($user_booking_stats[$user['user_id']]) ? $user_booking_stats[$user['user_id']] : ['hotel' => 0, 'flight' => 0, 'train' => 0];
                                $total_bookings = $bookings['hotel'] + $bookings['flight'] + $bookings['train'];
                                ?>
                                <div style="display: flex; gap: 5px;">
                                    <?php if($bookings['hotel'] > 0): ?>
                                        <span style="background-color: rgba(23, 162, 184, 0.1); color: var(--info-color); padding: 3px 6px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-hotel"></i> <?php echo $bookings['hotel']; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($bookings['flight'] > 0): ?>
                                        <span style="background-color: rgba(255, 193, 7, 0.1); color: var(--warning-color); padding: 3px 6px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-plane"></i> <?php echo $bookings['flight']; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($bookings['train'] > 0): ?>
                                        <span style="background-color: rgba(220, 53, 69, 0.1); color: var(--danger-color); padding: 3px 6px; border-radius: 4px; font-size: 12px;">
                                            <i class="fas fa-train"></i> <?php echo $bookings['train']; ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if($total_bookings == 0): ?>
                                        <em style="color: #999;">None</em>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if(isset($user['is_active']) && $user['is_active'] == 1): ?>
                                    <span class="status completed">Active</span>
                                <?php else: ?>
                                    <span class="status cancelled">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?section=users&action=view&id=<?php echo $user['user_id']; ?>" class="action-btn view-btn" title="View Details"><i class="fas fa-eye"></i></a>
                                
                                <a href="?section=users&action=toggle_status&id=<?php echo $user['user_id']; ?>" class="action-btn <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'delete-btn' : 'edit-btn'; ?>" title="<?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'Deactivate' : 'Activate'; ?>" onclick="return confirm('Are you sure you want to <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'deactivate' : 'activate'; ?> this user?');">
                                    <i class="fas <?php echo isset($user['is_active']) && $user['is_active'] == 1 ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <div style="margin-top: 20px; display: flex; justify-content: center;">
                <div style="display: flex; gap: 5px;">
                    <?php if($page > 1): ?>
                        <a href="?section=users&page=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?section=users&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?section=users&page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; <?php echo $i == $page ? 'background-color: var(--primary-color); color: white;' : 'color: var(--dark-color);'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?section=users&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?section=users&page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- User Statistics -->
<div class="content-section">
    <h3>User Statistics</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <?php
        // Calculate active vs inactive users
        $active_users_query = "SELECT COUNT(*) as active_count FROM users WHERE is_active = 1";
        $inactive_users_query = "SELECT COUNT(*) as inactive_count FROM users WHERE is_active = 0";
        
        $active_result = mysqli_query($conn, $active_users_query);
        $inactive_result = mysqli_query($conn, $inactive_users_query);
        
        $active_count = 0;
        $inactive_count = 0;
        
        if($active_result && mysqli_num_rows($active_result) > 0) {
            $active_row = mysqli_fetch_assoc($active_result);
            $active_count = $active_row['active_count'];
        }
        
        if($inactive_result && mysqli_num_rows($inactive_result) > 0) {
            $inactive_row = mysqli_fetch_assoc($inactive_result);
            $inactive_count = $inactive_row['inactive_count'];
        }
        
        // Get monthly user registrations for the past 6 months
        $months_registrations = [];
        $current_month = date('n');
        $current_year = date('Y');
        
        for($i = 5; $i >= 0; $i--) {
            $month = ($current_month - $i) > 0 ? ($current_month - $i) : (12 + ($current_month - $i));
            $year = ($current_month - $i) > 0 ? $current_year : ($current_year - 1);
            
            $month_start = sprintf("%04d-%02d-01", $year, $month);
            $month_end = date('Y-m-t', strtotime($month_start));
            
            $monthly_query = "SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN '$month_start' AND '$month_end 23:59:59'";
            $monthly_result = mysqli_query($conn, $monthly_query);
            
            $count = 0;
            if($monthly_result && mysqli_num_rows($monthly_result) > 0) {
                $monthly_row = mysqli_fetch_assoc($monthly_result);
                $count = $monthly_row['count'];
            }
            
            $month_name = date('M', strtotime($month_start));
            $months_registrations[$month_name] = $count;
        }
        
        // Get total bookings by type
        $booking_stats = ['hotel' => 0, 'flight' => 0, 'train' => 0];
        
        $hotel_query = "SELECT COUNT(*) as count FROM hotel_bookings";
        $flight_query = "SELECT COUNT(*) as count FROM flight_bookings";
        $train_query = "SELECT COUNT(*) as count FROM train_bookings";
        
        $hotel_result = mysqli_query($conn, $hotel_query);
        if($hotel_result && mysqli_num_rows($hotel_result) > 0) {
            $hotel_row = mysqli_fetch_assoc($hotel_result);
            $booking_stats['hotel'] = $hotel_row['count'];
        }
        
        $flight_result = mysqli_query($conn, $flight_query);
        if($flight_result && mysqli_num_rows($flight_result) > 0) {
            $flight_row = mysqli_fetch_assoc($flight_result);
            $booking_stats['flight'] = $flight_row['count'];
        }
        
        $train_result = mysqli_query($conn, $train_query);
        if($train_result && mysqli_num_rows($train_result) > 0) {
            $train_row = mysqli_fetch_assoc($train_result);
            $booking_stats['train'] = $train_row['count'];
        }
        ?>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">User Status</h4>
            <div style="height: 200px; display: flex; flex-direction: column; justify-content: center;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Active Users</span>
                    <span><strong><?php echo $active_count; ?></strong></span>
                </div>
                <div style="width: 100%; height: 20px; background-color: #f8f9fa; border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
                    <?php
                    $total_users = $active_count + $inactive_count;
                    $active_percent = $total_users > 0 ? ($active_count / $total_users) * 100 : 0;
                    ?>
                    <div style="width: <?php echo $active_percent; ?>%; height: 100%; background-color: var(--success-color);"></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Inactive Users</span>
                    <span><strong><?php echo $inactive_count; ?></strong></span>
                </div>
                <div style="width: 100%; height: 20px; background-color: #f8f9fa; border-radius: 10px; overflow: hidden;">
                    <?php
                    $inactive_percent = $total_users > 0 ? ($inactive_count / $total_users) * 100 : 0;
                    ?>
                    <div style="width: <?php echo $inactive_percent; ?>%; height: 100%; background-color: var(--danger-color);"></div>
                </div>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">User Growth (Last 6 Months)</h4>
            <div style="height: 200px; display: flex; align-items: flex-end; justify-content: space-between; padding-top: 20px;">
                <?php
                $max_value = max($months_registrations);
                $scale_factor = $max_value > 0 ? 150 / $max_value : 0;
                
                foreach($months_registrations as $month => $count):
                    $height = $count * $scale_factor;
                    if($height < 5 && $count > 0) $height = 5;
                ?>
                <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                    <div style="height: <?php echo $height; ?>px; width: 80%; background-color: var(--primary-color); border-radius: 4px 4px 0 0;"></div>
                    <div style="margin-top: 8px; font-size: 12px;"><?php echo $month; ?></div>
                    <div style="margin-top: 4px; font-size: 12px; font-weight: bold;"><?php echo $count; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Booking Distribution</h4>
            <div style="height: 200px; display: flex; flex-direction: column; justify-content: center;">
                <?php
                $total_bookings = array_sum($booking_stats);
                
                foreach($booking_stats as $type => $count):
                    $percent = $total_bookings > 0 ? ($count / $total_bookings) * 100 : 0;
                    $color = 'var(--primary-color)';
                    $icon = 'fa-bookmark';
                    
                    if($type == 'hotel') {
                        $color = 'var(--info-color)';
                        $icon = 'fa-hotel';
                        $label = 'Hotel Bookings';
                    } elseif($type == 'flight') {
                        $color = 'var(--warning-color)';
                        $icon = 'fa-plane';
                        $label = 'Flight Bookings';
                    } elseif($type == 'train') {
                        $color = 'var(--danger-color)';
                        $icon = 'fa-train';
                        $label = 'Train Bookings';
                    }
                ?>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <span><i class="fas <?php echo $icon; ?>"></i> <?php echo $label; ?></span>
                        <span><strong><?php echo $count; ?></strong> (<?php echo round($percent, 1); ?>%)</span>
                    </div>
                    <div style="width: 100%; height: 20px; background-color: #f8f9fa; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?php echo $percent; ?>%; height: 100%; background-color: <?php echo $color; ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div> 
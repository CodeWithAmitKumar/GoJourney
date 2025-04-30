<?php
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
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">User Growth</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">User growth chart will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">User Activity</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">User activity chart will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Booking Distribution</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Booking distribution chart will be displayed here</p>
            </div>
        </div>
    </div>
</div> 
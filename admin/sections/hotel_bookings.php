<?php
// Action handlers
if(isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $booking_id = (int)$_GET['id'];
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);
    
    // Validate status
    $valid_statuses = ['confirmed', 'pending', 'cancelled', 'completed'];
    if(in_array($new_status, $valid_statuses)) {
        // Check if hotel_bookings table exists
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
        if(mysqli_num_rows($check_table) > 0) {
            // Update status
            if(mysqli_query($conn, "UPDATE hotel_bookings SET status = '$new_status' WHERE id = $booking_id")) {
                $action_message = "Booking status updated successfully.";
                $action_status = "success";
            } else {
                $action_message = "Error updating booking status: " . mysqli_error($conn);
                $action_status = "danger";
            }
        } else {
            $action_message = "Hotel bookings table does not exist.";
            $action_status = "danger";
        }
    } else {
        $action_message = "Invalid status provided.";
        $action_status = "danger";
    }
}

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status_filter']) ? mysqli_real_escape_string($conn, $_GET['status_filter']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';

// Build search condition
$search_condition = '';
$conditions = [];

if(!empty($search)) {
    $conditions[] = "(h.hotel_name LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.user_email LIKE '%$search%' OR hb.booking_number LIKE '%$search%')";
}

if(!empty($status_filter)) {
    $conditions[] = "hb.status = '$status_filter'";
}

if(!empty($date_from)) {
    $conditions[] = "hb.check_in_date >= '$date_from'";
}

if(!empty($date_to)) {
    $conditions[] = "hb.check_out_date <= '$date_to'";
}

if(!empty($conditions)) {
    $search_condition = " WHERE " . implode(' AND ', $conditions);
}

// Check if hotels table exists, if not create it
$check_hotels_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
if(mysqli_num_rows($check_hotels_table) == 0) {
    // Create hotels table
    $create_hotels_table_sql = "CREATE TABLE hotels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        hotel_name VARCHAR(100) NOT NULL,
        location VARCHAR(100) NOT NULL,
        description TEXT,
        amenities TEXT,
        price_per_night DECIMAL(10,2) NOT NULL,
        room_types TEXT NOT NULL,
        rating DECIMAL(3,1),
        image VARCHAR(255),
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $create_hotels_table_sql);
    
    // Insert some sample data if table was just created
    $sample_hotels = [
        [
            'hotel_name' => 'Taj Palace',
            'location' => 'Mumbai',
            'description' => 'Luxury hotel in the heart of Mumbai offering world-class facilities and services.',
            'amenities' => 'Free WiFi,Swimming Pool,Spa,Restaurant,Bar,24-Hour Front Desk,Room Service',
            'price_per_night' => 12000,
            'room_types' => 'Deluxe Room,Superior Room,Executive Suite,Presidential Suite',
            'rating' => 4.8,
            'image' => 'assets/images/hotels/taj-palace.jpg',
            'status' => 'active'
        ],
        [
            'hotel_name' => 'The Leela Palace',
            'location' => 'Delhi',
            'description' => 'A 5-star luxury hotel with modern amenities and traditional Indian hospitality.',
            'amenities' => 'Free WiFi,Swimming Pool,Spa,Restaurant,Bar,Fitness Center,Airport Shuttle,Business Center',
            'price_per_night' => 15000,
            'room_types' => 'Deluxe Room,Royal Club Room,Executive Suite,Presidential Suite',
            'rating' => 4.9,
            'image' => 'assets/images/hotels/leela-palace.jpg',
            'status' => 'active'
        ],
        [
            'hotel_name' => 'JW Marriott',
            'location' => 'Bangalore',
            'description' => 'Contemporary luxury hotel offering sophisticated accommodation in Bangalore\'s business district.',
            'amenities' => 'Free WiFi,Swimming Pool,Spa,Restaurant,Bar,Fitness Center,Conference Room',
            'price_per_night' => 10000,
            'room_types' => 'Deluxe Room,Executive Room,Suite',
            'rating' => 4.7,
            'image' => 'assets/images/hotels/jw-marriott.jpg',
            'status' => 'active'
        ]
    ];
    
    foreach($sample_hotels as $hotel) {
        $insert_sql = "INSERT INTO hotels (hotel_name, location, description, amenities, price_per_night, room_types, rating, image, status) 
                      VALUES ('{$hotel['hotel_name']}', '{$hotel['location']}', '{$hotel['description']}', '{$hotel['amenities']}', {$hotel['price_per_night']}, '{$hotel['room_types']}', {$hotel['rating']}, '{$hotel['image']}', '{$hotel['status']}')";
        mysqli_query($conn, $insert_sql);
    }
}

// Check if hotel_bookings table exists, if not create it
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
if(mysqli_num_rows($check_table) == 0) {
    // Create hotel_bookings table
    $create_table_sql = "CREATE TABLE hotel_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_number VARCHAR(20) NOT NULL UNIQUE,
        user_id INT NOT NULL,
        hotel_id INT NOT NULL,
        room_type VARCHAR(50) NOT NULL,
        check_in_date DATE NOT NULL,
        check_out_date DATE NOT NULL,
        guests INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50),
        payment_status VARCHAR(20) DEFAULT 'pending',
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $create_table_sql);
}

// Fetch total bookings count
$total_records = 0;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
if(mysqli_num_rows($check_table) > 0) {
    // Only fetch counts if the hotels table also exists
    $check_hotels_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
    if(mysqli_num_rows($check_hotels_table) > 0) {
        $total_records_query = "SELECT COUNT(hb.id) as total 
                                FROM hotel_bookings hb 
                                LEFT JOIN users u ON hb.user_id = u.user_id 
                                LEFT JOIN hotels h ON hb.hotel_id = h.id" . $search_condition;
        $total_records_result = mysqli_query($conn, $total_records_query);
        if($total_records_result) {
            $total_records_row = mysqli_fetch_assoc($total_records_result);
            $total_records = $total_records_row['total'];
        }
    }
}
$total_pages = ceil($total_records / $records_per_page);

// Fetch bookings
$bookings = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
if(mysqli_num_rows($check_table) > 0) {
    // Only fetch bookings if the hotels table also exists
    $check_hotels_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
    if(mysqli_num_rows($check_hotels_table) > 0) {
        $sql = "SELECT hb.*, u.full_name, u.user_email, h.hotel_name, h.location 
                FROM hotel_bookings hb 
                LEFT JOIN users u ON hb.user_id = u.user_id 
                LEFT JOIN hotels h ON hb.hotel_id = h.id" . 
                $search_condition . 
                " ORDER BY hb.created_at DESC LIMIT $offset, $records_per_page";
        $result = mysqli_query($conn, $sql);
        if($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $bookings[] = $row;
            }
        }
    }
}

// Calculate dashboard stats
$stats = [
    'total' => $total_records,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0,
    'completed' => 0,
    'revenue' => 0
];

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
if(mysqli_num_rows($check_table) > 0) {
    // Get status counts
    $status_query = "SELECT status, COUNT(*) as count FROM hotel_bookings GROUP BY status";
    $status_result = mysqli_query($conn, $status_query);
    if($status_result && mysqli_num_rows($status_result) > 0) {
        while($row = mysqli_fetch_assoc($status_result)) {
            $stats[$row['status']] = $row['count'];
        }
    }
    
    // Get total revenue from completed/confirmed bookings
    $revenue_query = "SELECT SUM(total_price) as total_revenue FROM hotel_bookings WHERE status IN ('completed', 'confirmed')";
    $revenue_result = mysqli_query($conn, $revenue_query);
    if($revenue_result) {
        $revenue_row = mysqli_fetch_assoc($revenue_result);
        $stats['revenue'] = $revenue_row['total_revenue'] ?: 0;
    }
}
?>

<div class="dashboard-title">
    <h2>Hotel Bookings</h2>
</div>

<?php if(isset($action_message)): ?>
    <div class="alert alert-<?php echo $action_status; ?>" style="background-color: <?php echo $action_status === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $action_status === 'success' ? '#155724' : '#721c24'; ?>; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $action_message; ?>
    </div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="stats-container">
    <div class="stat-card">
        <div class="card-icon">
            <i class="fas fa-hotel"></i>
        </div>
        <h3>Total Bookings</h3>
        <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="card-icon" style="background-color: rgba(255, 193, 7, 0.1); color: var(--warning-color);">
            <i class="fas fa-clock"></i>
        </div>
        <h3>Pending</h3>
        <div class="stat-value"><?php echo number_format($stats['pending']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="card-icon" style="background-color: rgba(23, 162, 184, 0.1); color: var(--info-color);">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3>Confirmed</h3>
        <div class="stat-value"><?php echo number_format($stats['confirmed']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="card-icon" style="background-color: rgba(40, 167, 69, 0.1); color: var(--success-color);">
            <i class="fas fa-check-double"></i>
        </div>
        <h3>Completed</h3>
        <div class="stat-value"><?php echo number_format($stats['completed']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="card-icon" style="background-color: rgba(220, 53, 69, 0.1); color: var(--danger-color);">
            <i class="fas fa-times-circle"></i>
        </div>
        <h3>Cancelled</h3>
        <div class="stat-value"><?php echo number_format($stats['cancelled']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="card-icon" style="background-color: rgba(74, 93, 219, 0.1); color: var(--primary-color);">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <h3>Revenue</h3>
        <div class="stat-value">₹<?php echo number_format($stats['revenue'], 2); ?></div>
    </div>
</div>

<!-- Search & Filters -->
<div class="content-section">
    <h3>Search & Filters</h3>
    
    <form method="GET" action="">
        <input type="hidden" name="section" value="hotel_bookings">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div>
                <label for="search" style="display: block; margin-bottom: 5px; font-weight: 500;">Search:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by hotel, customer, booking #" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div>
                <label for="status_filter" style="display: block; margin-bottom: 5px; font-weight: 500;">Status:</label>
                <select id="status_filter" name="status_filter" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label for="date_from" style="display: block; margin-bottom: 5px; font-weight: 500;">Check-in From:</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div>
                <label for="date_to" style="display: block; margin-bottom: 5px; font-weight: 500;">Check-out To:</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                <i class="fas fa-search"></i> Search
            </button>
            
            <a href="?section=hotel_bookings" style="background-color: var(--secondary-color); color: white; border: none; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-flex; align-items: center;">
                <i class="fas fa-sync-alt"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- Bookings List -->
<div class="content-section">
    <h3>Hotel Bookings List</h3>
    
    <?php if(empty($bookings)): ?>
        <div style="text-align: center; padding: 30px; background-color: #f8f9fa; border-radius: 5px;">
            <?php if(!empty($search) || !empty($status_filter) || !empty($date_from) || !empty($date_to)): ?>
                <p style="color: #6c757d;">No bookings found matching your search criteria.</p>
                <a href="?section=hotel_bookings" style="color: var(--primary-color); text-decoration: none;">Clear search</a>
            <?php else: ?>
                <p style="color: #6c757d;">No hotel bookings found.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Customer</th>
                        <th>Hotel</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Room Type</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $booking): ?>
                        <tr>
                            <td><?php echo $booking['booking_number']; ?></td>
                            <td>
                                <?php echo $booking['full_name']; ?><br>
                                <small style="color: #6c757d;"><?php echo $booking['user_email']; ?></small>
                            </td>
                            <td>
                                <?php echo $booking['hotel_name']; ?><br>
                                <small style="color: #6c757d;"><?php echo $booking['location']; ?></small>
                            </td>
                            <td><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></td>
                            <td><?php echo $booking['room_type']; ?></td>
                            <td><?php echo $booking['guests']; ?></td>
                            <td>₹<?php echo number_format($booking['total_price'], 2); ?></td>
                            <td>
                                <?php 
                                $status_class = '';
                                switch($booking['status']) {
                                    case 'pending':
                                        $status_class = 'pending';
                                        break;
                                    case 'confirmed':
                                        $status_class = 'info';
                                        break;
                                    case 'completed':
                                        $status_class = 'completed';
                                        break;
                                    case 'cancelled':
                                        $status_class = 'cancelled';
                                        break;
                                }
                                ?>
                                <span class="status <?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </td>
                            <td>
                                <div class="dropdown" style="position: relative; display: inline-block;">
                                    <button style="background: none; border: none; cursor: pointer; color: var(--primary-color);">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-content" style="display: none; position: absolute; right: 0; background-color: white; min-width: 160px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1; border-radius: 5px;">
                                        <a href="?section=view_booking&id=<?php echo $booking['id']; ?>&type=hotel" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--dark-color);">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        
                                        <?php if($booking['status'] == 'pending'): ?>
                                            <a href="?section=hotel_bookings&action=change_status&id=<?php echo $booking['id']; ?>&status=confirmed" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--info-color);" onclick="return confirm('Confirm this booking?');">
                                                <i class="fas fa-check-circle"></i> Confirm
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($booking['status'] == 'confirmed'): ?>
                                            <a href="?section=hotel_bookings&action=change_status&id=<?php echo $booking['id']; ?>&status=completed" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--success-color);" onclick="return confirm('Mark this booking as completed?');">
                                                <i class="fas fa-check-double"></i> Complete
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                            <a href="?section=hotel_bookings&action=change_status&id=<?php echo $booking['id']; ?>&status=cancelled" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--danger-color);" onclick="return confirm('Cancel this booking?');">
                                                <i class="fas fa-times-circle"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="#" class="print-booking" data-id="<?php echo $booking['id']; ?>" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--dark-color);">
                                            <i class="fas fa-print"></i> Print
                                        </a>
                                    </div>
                                </div>
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
                        <a href="?section=hotel_bookings&page=1<?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter='.urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?section=hotel_bookings&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter='.urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?section=hotel_bookings&page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter='.urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; <?php echo $i == $page ? 'background-color: var(--primary-color); color: white;' : 'color: var(--dark-color);'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?section=hotel_bookings&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter='.urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?section=hotel_bookings&page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($status_filter) ? '&status_filter='.urlencode($status_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 3px; text-decoration: none; color: var(--dark-color);">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // Handle dropdown menu for actions
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButtons = document.querySelectorAll('.dropdown button');
        
        dropdownButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const content = this.nextElementSibling;
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    if(dropdown !== content) {
                        dropdown.style.display = 'none';
                    }
                });
                
                // Toggle current dropdown
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        });
        
        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.dropdown-content').forEach(content => {
            content.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        // Print booking functionality
        document.querySelectorAll('.print-booking').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                alert('Print functionality will be implemented here.');
            });
        });
    });
</script> 
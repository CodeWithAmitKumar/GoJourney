<?php
// Get booking counts from database
$hotel_bookings_count = 0;
$flight_bookings_count = 0;
$train_bookings_count = 0;
$user_count = 0;
$total_revenue = 0;

// Get hotel bookings count
$hotel_query = "SELECT COUNT(*) as count FROM hotel_bookings";
$hotel_result = mysqli_query($conn, $hotel_query);
if($hotel_result && mysqli_num_rows($hotel_result) > 0) {
    $hotel_count = mysqli_fetch_assoc($hotel_result);
    $hotel_bookings_count = $hotel_count['count'];
}

// Get flight bookings count
$flight_query = "SELECT COUNT(*) as count FROM flight_bookings";
$flight_result = mysqli_query($conn, $flight_query);
if($flight_result && mysqli_num_rows($flight_result) > 0) {
    $flight_count = mysqli_fetch_assoc($flight_result);
    $flight_bookings_count = $flight_count['count'];
}

// Get train bookings count
$train_query = "SELECT COUNT(*) as count FROM train_bookings";
$train_result = mysqli_query($conn, $train_query);
if($train_result && mysqli_num_rows($train_result) > 0) {
    $train_count = mysqli_fetch_assoc($train_result);
    $train_bookings_count = $train_count['count'];
}

// Get users count
$users_query = "SELECT COUNT(*) as count FROM users";
$users_result = mysqli_query($conn, $users_query);
if($users_result && mysqli_num_rows($users_result) > 0) {
    $users_count = mysqli_fetch_assoc($users_result);
    $user_count = $users_count['count'];
}

// Get total revenue from all bookings
$revenue_query = "SELECT 
    (SELECT IFNULL(SUM(total_price), 0) FROM hotel_bookings WHERE status IN ('completed', 'confirmed')) +
    (SELECT IFNULL(SUM(total_price), 0) FROM flight_bookings WHERE status IN ('completed', 'confirmed')) +
    (SELECT IFNULL(SUM(total_price), 0) FROM train_bookings WHERE status IN ('completed', 'confirmed')) as total_revenue";
$revenue_result = mysqli_query($conn, $revenue_query);
if($revenue_result && mysqli_num_rows($revenue_result) > 0) {
    $revenue = mysqli_fetch_assoc($revenue_result);
    $total_revenue = $revenue['total_revenue'];
}
?>

<div class="dashboard-title">
    <h2>Dashboard Overview</h2>
</div>

<!-- Stats Cards -->
<div class="stats-container">
    <div class="stat-card user-card">
        <div class="card-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Total Users</h3>
        <div class="stat-value"><?php echo $user_count; ?></div>
    </div>
    
    <div class="stat-card hotel-card">
        <div class="card-icon">
            <i class="fas fa-hotel"></i>
        </div>
        <h3>Hotel Bookings</h3>
        <div class="stat-value"><?php echo $hotel_bookings_count; ?></div>
    </div>
    
    <div class="stat-card flight-card">
        <div class="card-icon">
            <i class="fas fa-plane"></i>
        </div>
        <h3>Flight Bookings</h3>
        <div class="stat-value"><?php echo $flight_bookings_count; ?></div>
    </div>
    
    <div class="stat-card train-card">
        <div class="card-icon">
            <i class="fas fa-train"></i>
        </div>
        <h3>Train Bookings</h3>
        <div class="stat-value"><?php echo $train_bookings_count; ?></div>
    </div>
    
    <div class="stat-card revenue-card">
        <div class="card-icon" style="background-color: rgba(74, 93, 219, 0.1); color: var(--primary-color);">
            <i class="fas fa-rupee-sign"></i>
        </div>
        <h3>Total Revenue</h3>
        <div class="stat-value">₹<?php echo number_format($total_revenue, 2); ?></div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="content-section">
    <h3>Recent Bookings</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Details</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get recent bookings from all booking types
                $recent_bookings_query = "
                (SELECT hb.id, hb.booking_number, u.full_name, 'Hotel' as booking_type, 
                        h.hotel_name as detail, hb.check_in_date as date, 
                        hb.total_price, hb.status, hb.created_at, 'hotel' as type_for_url
                 FROM hotel_bookings hb
                 LEFT JOIN users u ON hb.user_id = u.user_id
                 LEFT JOIN hotels h ON hb.hotel_id = h.id)
                UNION ALL
                (SELECT fb.id, fb.booking_number, u.full_name, 'Flight' as booking_type, 
                        CONCAT(f.source, ' → ', f.destination) as detail, fb.departure_date as date,
                        fb.total_price, fb.status, fb.created_at, 'flight' as type_for_url
                 FROM flight_bookings fb
                 LEFT JOIN users u ON fb.user_id = u.user_id
                 LEFT JOIN flights f ON fb.flight_id = f.id)
                UNION ALL
                (SELECT tb.id, tb.booking_number, u.full_name, 'Train' as booking_type, 
                        CONCAT(t.source, ' → ', t.destination) as detail, tb.journey_date as date,
                        tb.total_price, tb.status, tb.created_at, 'train' as type_for_url
                 FROM train_bookings tb
                 LEFT JOIN users u ON tb.user_id = u.user_id
                 LEFT JOIN trains t ON tb.train_id = t.id)
                ORDER BY created_at DESC
                LIMIT 10";
                
                $recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
                
                if($recent_bookings_result && mysqli_num_rows($recent_bookings_result) > 0) {
                    while($booking = mysqli_fetch_assoc($recent_bookings_result)) {
                        // Determine status class
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
                        
                        // Output the booking row
                        echo '<tr>
                                <td>' . $booking['booking_number'] . '</td>
                                <td>' . htmlspecialchars($booking['full_name']) . '</td>
                                <td>' . $booking['booking_type'] . '</td>
                                <td>' . htmlspecialchars($booking['detail']) . '</td>
                                <td>' . date('d M Y', strtotime($booking['date'])) . '</td>
                                <td>₹' . number_format($booking['total_price'], 2) . '</td>
                                <td><span class="status ' . $status_class . '">' . ucfirst($booking['status']) . '</span></td>
                                <td>
                                    <a href="?section=view_booking&id=' . $booking['id'] . '&type=' . $booking['type_for_url'] . '" class="action-btn view-btn" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="?section=' . $booking['type_for_url'] . '_bookings" class="action-btn list-btn" title="View All ' . ucfirst($booking['type_for_url']) . ' Bookings"><i class="fas fa-list"></i></a>
                                </td>
                            </tr>';
                    }
                } else {
                    echo '<tr><td colspan="8" style="text-align: center; padding: 20px;">No recent bookings found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Booking Stats by Type -->
<div class="content-section">
    <h3>Booking Status Overview</h3>
    <div class="stats-grid">
        <?php
        // Get hotel booking stats
        $hotel_stats_query = "SELECT status, COUNT(*) as count FROM hotel_bookings GROUP BY status";
        $hotel_stats_result = mysqli_query($conn, $hotel_stats_query);
        $hotel_stats = [
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        if($hotel_stats_result && mysqli_num_rows($hotel_stats_result) > 0) {
            while($row = mysqli_fetch_assoc($hotel_stats_result)) {
                $hotel_stats[$row['status']] = $row['count'];
            }
        }
        
        // Get flight booking stats
        $flight_stats_query = "SELECT status, COUNT(*) as count FROM flight_bookings GROUP BY status";
        $flight_stats_result = mysqli_query($conn, $flight_stats_query);
        $flight_stats = [
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        if($flight_stats_result && mysqli_num_rows($flight_stats_result) > 0) {
            while($row = mysqli_fetch_assoc($flight_stats_result)) {
                $flight_stats[$row['status']] = $row['count'];
            }
        }
        
        // Get train booking stats
        $train_stats_query = "SELECT status, COUNT(*) as count FROM train_bookings GROUP BY status";
        $train_stats_result = mysqli_query($conn, $train_stats_query);
        $train_stats = [
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        
        if($train_stats_result && mysqli_num_rows($train_stats_result) > 0) {
            while($row = mysqli_fetch_assoc($train_stats_result)) {
                $train_stats[$row['status']] = $row['count'];
            }
        }
        ?>
        
        <div class="stat-box">
            <h4><i class="fas fa-hotel"></i> Hotel Bookings</h4>
            <div class="stat-items">
                <div class="stat-item">
                    <span class="label">Pending</span>
                    <span class="value"><?php echo $hotel_stats['pending']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Confirmed</span>
                    <span class="value"><?php echo $hotel_stats['confirmed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Completed</span>
                    <span class="value"><?php echo $hotel_stats['completed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Cancelled</span>
                    <span class="value"><?php echo $hotel_stats['cancelled']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-box">
            <h4><i class="fas fa-plane"></i> Flight Bookings</h4>
            <div class="stat-items">
                <div class="stat-item">
                    <span class="label">Pending</span>
                    <span class="value"><?php echo $flight_stats['pending']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Confirmed</span>
                    <span class="value"><?php echo $flight_stats['confirmed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Completed</span>
                    <span class="value"><?php echo $flight_stats['completed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Cancelled</span>
                    <span class="value"><?php echo $flight_stats['cancelled']; ?></span>
                </div>
            </div>
        </div>
        
        <div class="stat-box">
            <h4><i class="fas fa-train"></i> Train Bookings</h4>
            <div class="stat-items">
                <div class="stat-item">
                    <span class="label">Pending</span>
                    <span class="value"><?php echo $train_stats['pending']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Confirmed</span>
                    <span class="value"><?php echo $train_stats['confirmed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Completed</span>
                    <span class="value"><?php echo $train_stats['completed']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="label">Cancelled</span>
                    <span class="value"><?php echo $train_stats['cancelled']; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-box {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
}

.stat-box h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: var(--primary-color);
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-items {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.stat-item .label {
    color: #6c757d;
    font-size: 0.9rem;
}

.stat-item .value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark-color);
}

.action-btn {
    color: var(--primary-color);
    margin-right: 10px;
    font-size: 0.9rem;
}

.action-btn:hover {
    color: var(--secondary-color);
}

.revenue-card .card-icon {
    background-color: rgba(74, 93, 219, 0.1);
    color: var(--primary-color);
}
</style> 
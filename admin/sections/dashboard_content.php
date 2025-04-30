<?php
// Initialize counts (replace with actual database queries later)
$hotel_bookings_count = 24;
$flight_bookings_count = 16;
$train_bookings_count = 12;
$user_count = 45;
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
        <div class="stat-change">+8% <i class="fas fa-arrow-up"></i></div>
    </div>
    
    <div class="stat-card hotel-card">
        <div class="card-icon">
            <i class="fas fa-hotel"></i>
        </div>
        <h3>Hotel Bookings</h3>
        <div class="stat-value"><?php echo $hotel_bookings_count; ?></div>
        <div class="stat-change">+12% <i class="fas fa-arrow-up"></i></div>
    </div>
    
    <div class="stat-card flight-card">
        <div class="card-icon">
            <i class="fas fa-plane"></i>
        </div>
        <h3>Flight Bookings</h3>
        <div class="stat-value"><?php echo $flight_bookings_count; ?></div>
        <div class="stat-change">+5% <i class="fas fa-arrow-up"></i></div>
    </div>
    
    <div class="stat-card train-card">
        <div class="card-icon">
            <i class="fas fa-train"></i>
        </div>
        <h3>Train Bookings</h3>
        <div class="stat-value"><?php echo $train_bookings_count; ?></div>
        <div class="stat-change negative">-3% <i class="fas fa-arrow-down"></i></div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="content-section">
    <h3>Recent Bookings</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample data, replace with database queries later -->
                <tr>
                    <td>#BK-0025</td>
                    <td>John Doe</td>
                    <td>Hotel</td>
                    <td>10 Apr 2024</td>
                    <td>₹12,500</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <a href="#" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <tr>
                    <td>#BK-0024</td>
                    <td>Jane Smith</td>
                    <td>Flight</td>
                    <td>9 Apr 2024</td>
                    <td>₹8,750</td>
                    <td><span class="status pending">Pending</span></td>
                    <td>
                        <a href="#" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <tr>
                    <td>#BK-0023</td>
                    <td>Amit Kumar</td>
                    <td>Train</td>
                    <td>8 Apr 2024</td>
                    <td>₹2,200</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <a href="#" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <tr>
                    <td>#BK-0022</td>
                    <td>Priya Sharma</td>
                    <td>Hotel</td>
                    <td>7 Apr 2024</td>
                    <td>₹9,800</td>
                    <td><span class="status cancelled">Cancelled</span></td>
                    <td>
                        <a href="#" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <tr>
                    <td>#BK-0021</td>
                    <td>Rajesh Singh</td>
                    <td>Flight</td>
                    <td>6 Apr 2024</td>
                    <td>₹15,300</td>
                    <td><span class="status completed">Completed</span></td>
                    <td>
                        <a href="#" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Stats -->
<div class="content-section">
    <h3>Revenue Overview</h3>
    <div style="width: 100%; height: 300px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
        <p style="color: #6c757d; font-style: italic;">Revenue chart will be displayed here</p>
    </div>
</div> 
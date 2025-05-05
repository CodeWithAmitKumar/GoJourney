<?php
// Include database connection
require_once '../../connection/db_connect.php';

// Check if booking ID and type are provided
if(!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: index.php");
    exit();
}

$booking_id = (int)$_GET['id'];
$booking_type = mysqli_real_escape_string($conn, $_GET['type']);

// Fetch booking details based on type
$booking = null;
$table_name = '';
$join_conditions = '';

switch($booking_type) {
    case 'hotel':
        $table_name = 'hotel_bookings';
        $join_conditions = "LEFT JOIN hotels h ON hb.hotel_id = h.id";
        $sql = "SELECT hb.*, u.full_name, u.user_email, h.hotel_name, h.location 
                FROM hotel_bookings hb 
                LEFT JOIN users u ON hb.user_id = u.user_id 
                $join_conditions 
                WHERE hb.id = $booking_id";
        break;
        
    case 'flight':
        $table_name = 'flight_bookings';
        $join_conditions = "LEFT JOIN flights f ON fb.flight_id = f.id";
        $sql = "SELECT fb.*, u.full_name, u.user_email, f.airline_name, f.flight_number, f.source, f.destination 
                FROM flight_bookings fb 
                LEFT JOIN users u ON fb.user_id = u.user_id 
                $join_conditions 
                WHERE fb.id = $booking_id";
        break;
        
    case 'train':
        $table_name = 'train_bookings';
        $join_conditions = "LEFT JOIN trains t ON tb.train_id = t.id";
        $sql = "SELECT tb.*, u.full_name, u.user_email, t.train_name, t.train_number, t.source, t.destination 
                FROM train_bookings tb 
                LEFT JOIN users u ON tb.user_id = u.user_id 
                $join_conditions 
                WHERE tb.id = $booking_id";
        break;
        
    default:
        header("Location: index.php");
        exit();
}

// Execute query and fetch booking details
$result = mysqli_query($conn, $sql);
if($result && mysqli_num_rows($result) > 0) {
    $booking = mysqli_fetch_assoc($result);
} else {
    // If no booking found, redirect to index
    header("Location: index.php");
    exit();
}

// Fetch passenger details if available
$passengers = [];
if($booking_type == 'flight' || $booking_type == 'train') {
    $passenger_sql = "SELECT * FROM passengers WHERE booking_id = $booking_id";
    $passenger_result = mysqli_query($conn, $passenger_sql);
    if($passenger_result && mysqli_num_rows($passenger_result) > 0) {
        while($row = mysqli_fetch_assoc($passenger_result)) {
            $passengers[] = $row;
        }
    }
}
?>

<div class="dashboard-title">
    <h2><?php echo ucfirst($booking_type); ?> Booking Details</h2>
</div>

<div class="content-section">
    <div class="booking-details">
        <div class="booking-header">
            <div class="booking-info">
                <h3>Booking #<?php echo $booking['booking_number']; ?></h3>
                <span class="status <?php echo $booking['status']; ?>"><?php echo ucfirst($booking['status']); ?></span>
            </div>
            <div class="booking-actions">
                <a href="?section=<?php echo $booking_type; ?>_bookings" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <button class="print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="booking-body">
            <div class="details-grid">
                <div class="detail-section">
                    <h4>Customer Information</h4>
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($booking['full_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($booking['user_email']); ?></span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4>Booking Information</h4>
                    <div class="detail-item">
                        <label>Booking Date:</label>
                        <span><?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Payment Status:</label>
                        <span class="status <?php echo $booking['payment_status']; ?>"><?php echo ucfirst($booking['payment_status']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Payment Method:</label>
                        <span><?php echo ucfirst($booking['payment_method'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <?php if($booking_type == 'hotel'): ?>
                <div class="detail-section">
                    <h4>Hotel Information</h4>
                    <div class="detail-item">
                        <label>Hotel Name:</label>
                        <span><?php echo htmlspecialchars($booking['hotel_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Location:</label>
                        <span><?php echo htmlspecialchars($booking['location']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Room Type:</label>
                        <span><?php echo htmlspecialchars($booking['room_type']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Check-in Date:</label>
                        <span><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Check-out Date:</label>
                        <span><?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Number of Guests:</label>
                        <span><?php echo $booking['guests']; ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if($booking_type == 'flight'): ?>
                <div class="detail-section">
                    <h4>Flight Information</h4>
                    <div class="detail-item">
                        <label>Airline:</label>
                        <span><?php echo htmlspecialchars($booking['airline_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Flight Number:</label>
                        <span><?php echo htmlspecialchars($booking['flight_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Route:</label>
                        <span><?php echo htmlspecialchars($booking['source']) . ' → ' . htmlspecialchars($booking['destination']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Departure Date:</label>
                        <span><?php echo date('d M Y', strtotime($booking['departure_date'])); ?></span>
                    </div>
                    <?php if($booking['return_date']): ?>
                    <div class="detail-item">
                        <label>Return Date:</label>
                        <span><?php echo date('d M Y', strtotime($booking['return_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <label>Travel Class:</label>
                        <span><?php echo htmlspecialchars($booking['travel_class']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Number of Passengers:</label>
                        <span><?php echo $booking['passengers']; ?></span>
                    </div>
                </div>

                <?php if(!empty($passengers)): ?>
                <div class="detail-section">
                    <h4>Passenger Details</h4>
                    <table class="passenger-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Seat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($passengers as $passenger): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($passenger['name']); ?></td>
                                <td><?php echo $passenger['age']; ?></td>
                                <td><?php echo htmlspecialchars($passenger['gender']); ?></td>
                                <td><?php echo htmlspecialchars($passenger['seat_number'] ?? 'N/A'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if($booking_type == 'train'): ?>
                <div class="detail-section">
                    <h4>Train Information</h4>
                    <div class="detail-item">
                        <label>Train Name:</label>
                        <span><?php echo htmlspecialchars($booking['train_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Train Number:</label>
                        <span><?php echo htmlspecialchars($booking['train_number']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Route:</label>
                        <span><?php echo htmlspecialchars($booking['source']) . ' → ' . htmlspecialchars($booking['destination']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Journey Date:</label>
                        <span><?php echo date('d M Y', strtotime($booking['journey_date'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Travel Class:</label>
                        <span><?php echo htmlspecialchars($booking['seat_class']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Number of Passengers:</label>
                        <span><?php echo $booking['passengers']; ?></span>
                    </div>
                </div>

                <?php if(!empty($passengers)): ?>
                <div class="detail-section">
                    <h4>Passenger Details</h4>
                    <table class="passenger-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Seat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($passengers as $passenger): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($passenger['name']); ?></td>
                                <td><?php echo $passenger['age']; ?></td>
                                <td><?php echo htmlspecialchars($passenger['gender']); ?></td>
                                <td><?php echo htmlspecialchars($passenger['seat_number'] ?? 'N/A'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <div class="detail-section">
                    <h4>Payment Information</h4>
                    <div class="detail-item">
                        <label>Total Amount:</label>
                        <span class="amount">₹<?php echo number_format($booking['total_price'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.booking-details {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.booking-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #375990 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.booking-info h3 {
    margin: 0;
    font-size: 1.5rem;
}

.booking-actions {
    display: flex;
    gap: 10px;
}

.back-btn, .print-btn {
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.2);
    border: none;
    cursor: pointer;
}

.booking-body {
    padding: 20px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.detail-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.detail-section h4 {
    margin: 0 0 15px 0;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.detail-item {
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-item label {
    color: #666;
    font-weight: 500;
}

.detail-item .amount {
    font-weight: 600;
    color: var(--primary-color);
}

.status {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status.pending { background: #fff3cd; color: #856404; }
.status.confirmed { background: #cce5ff; color: #004085; }
.status.completed { background: #d4edda; color: #155724; }
.status.cancelled { background: #f8d7da; color: #721c24; }

.passenger-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.passenger-table th,
.passenger-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.passenger-table th {
    background-color: #f8f9fa;
    font-weight: 500;
    color: #495057;
}

.passenger-table tr:last-child td {
    border-bottom: none;
}

@media print {
    .booking-actions {
        display: none;
    }
}
</style> 
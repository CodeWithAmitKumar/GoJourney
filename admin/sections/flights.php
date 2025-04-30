<?php
// Check for flight add form submission
if(isset($_POST['add_flight']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $flight_number = mysqli_real_escape_string($conn, trim($_POST['flight_number']));
    $airline = mysqli_real_escape_string($conn, trim($_POST['airline']));
    $departure_city = mysqli_real_escape_string($conn, trim($_POST['departure_city']));
    $arrival_city = mysqli_real_escape_string($conn, trim($_POST['arrival_city']));
    $departure_time = mysqli_real_escape_string($conn, trim($_POST['departure_time']));
    $arrival_time = mysqli_real_escape_string($conn, trim($_POST['arrival_time']));
    $price = mysqli_real_escape_string($conn, trim($_POST['price']));
    $capacity = mysqli_real_escape_string($conn, trim($_POST['capacity']));
    $flight_type = mysqli_real_escape_string($conn, trim($_POST['flight_type']));
    
    // Validate input
    $errors = [];
    if(empty($flight_number)) {
        $errors[] = "Flight number is required";
    }
    
    if(empty($airline)) {
        $errors[] = "Airline name is required";
    }
    
    if(empty($departure_city)) {
        $errors[] = "Departure city is required";
    }
    
    if(empty($arrival_city)) {
        $errors[] = "Arrival city is required";
    }
    
    if(empty($departure_time)) {
        $errors[] = "Departure time is required";
    }
    
    if(empty($arrival_time)) {
        $errors[] = "Arrival time is required";
    }
    
    if(empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    
    if(empty($capacity) || !is_numeric($capacity) || $capacity <= 0) {
        $errors[] = "Valid capacity is required";
    }
    
    // Check if flights table exists, if not create it
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'flights'");
    if(mysqli_num_rows($check_table) == 0) {
        // Create flights table
        $create_table_sql = "CREATE TABLE flights (
            id INT AUTO_INCREMENT PRIMARY KEY,
            flight_number VARCHAR(20) NOT NULL,
            airline VARCHAR(100) NOT NULL,
            departure_city VARCHAR(100) NOT NULL,
            arrival_city VARCHAR(100) NOT NULL,
            departure_time DATETIME NOT NULL,
            arrival_time DATETIME NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            capacity INT NOT NULL,
            available_seats INT NOT NULL,
            flight_type ENUM('Domestic', 'International') NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if(!mysqli_query($conn, $create_table_sql)) {
            $errors[] = "Failed to create flights table: " . mysqli_error($conn);
        }
    }
    
    // If no errors, proceed with flight creation
    if(empty($errors)) {
        // Insert flight
        $available_seats = $capacity; // Initially, all seats are available
        $insert_sql = "INSERT INTO flights (flight_number, airline, departure_city, arrival_city, departure_time, arrival_time, price, capacity, available_seats, flight_type) 
                       VALUES ('$flight_number', '$airline', '$departure_city', '$arrival_city', '$departure_time', '$arrival_time', '$price', '$capacity', '$available_seats', '$flight_type')";
        
        if(mysqli_query($conn, $insert_sql)) {
            $success_message = "Flight added successfully!";
            // Clear form data
            unset($flight_number, $airline, $departure_city, $arrival_city, $departure_time, $arrival_time, $price, $capacity, $flight_type);
        } else {
            $errors[] = "Failed to add flight: " . mysqli_error($conn);
        }
    }
}

// Fetch all flights
$flights = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'flights'");
if(mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT * FROM flights ORDER BY departure_time ASC");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $flights[] = $row;
        }
    }
}
?>

<div class="dashboard-title">
    <h2>Flight Management</h2>
</div>

<!-- Add Flight Form -->
<div class="content-section">
    <h3>Add New Flight</h3>
    
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
    
    <form method="POST" action="" style="max-width: 800px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="flight_number" style="display: block; margin-bottom: 5px; font-weight: 500;">Flight Number:</label>
                <input type="text" id="flight_number" name="flight_number" value="<?php echo isset($flight_number) ? $flight_number : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="airline" style="display: block; margin-bottom: 5px; font-weight: 500;">Airline:</label>
                <input type="text" id="airline" name="airline" value="<?php echo isset($airline) ? $airline : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="departure_city" style="display: block; margin-bottom: 5px; font-weight: 500;">Departure City:</label>
                <input type="text" id="departure_city" name="departure_city" value="<?php echo isset($departure_city) ? $departure_city : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="arrival_city" style="display: block; margin-bottom: 5px; font-weight: 500;">Arrival City:</label>
                <input type="text" id="arrival_city" name="arrival_city" value="<?php echo isset($arrival_city) ? $arrival_city : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="departure_time" style="display: block; margin-bottom: 5px; font-weight: 500;">Departure Time:</label>
                <input type="datetime-local" id="departure_time" name="departure_time" value="<?php echo isset($departure_time) ? date('Y-m-d\TH:i', strtotime($departure_time)) : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="arrival_time" style="display: block; margin-bottom: 5px; font-weight: 500;">Arrival Time:</label>
                <input type="datetime-local" id="arrival_time" name="arrival_time" value="<?php echo isset($arrival_time) ? date('Y-m-d\TH:i', strtotime($arrival_time)) : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="price" style="display: block; margin-bottom: 5px; font-weight: 500;">Price (₹):</label>
                <input type="number" id="price" name="price" min="1" step="0.01" value="<?php echo isset($price) ? $price : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="capacity" style="display: block; margin-bottom: 5px; font-weight: 500;">Capacity:</label>
                <input type="number" id="capacity" name="capacity" min="1" value="<?php echo isset($capacity) ? $capacity : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="flight_type" style="display: block; margin-bottom: 5px; font-weight: 500;">Flight Type:</label>
                <select id="flight_type" name="flight_type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                    <option value="">Select Type</option>
                    <option value="Domestic" <?php echo (isset($flight_type) && $flight_type == 'Domestic') ? 'selected' : ''; ?>>Domestic</option>
                    <option value="International" <?php echo (isset($flight_type) && $flight_type == 'International') ? 'selected' : ''; ?>>International</option>
                </select>
            </div>
        </div>
        
        <div>
            <button type="submit" name="add_flight" style="background: linear-gradient(to right, #4776E6, #8E54E9); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-plane-departure"></i> Add Flight
            </button>
        </div>
    </form>
</div>

<!-- Flight List -->
<div class="content-section">
    <h3>Flight Schedule</h3>
    
    <?php if(empty($flights)): ?>
        <p style="color: #6c757d; font-style: italic;">No flights found. Add a new flight using the form above.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Flight No.</th>
                        <th>Airline</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Price</th>
                        <th>Available</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($flights as $flight): ?>
                        <tr>
                            <td><?php echo $flight['flight_number']; ?></td>
                            <td><?php echo $flight['airline']; ?></td>
                            <td><?php echo $flight['departure_city']; ?> to <?php echo $flight['arrival_city']; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($flight['departure_time'])); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($flight['arrival_time'])); ?></td>
                            <td>₹<?php echo number_format($flight['price'], 2); ?></td>
                            <td><?php echo $flight['available_seats']; ?>/<?php echo $flight['capacity']; ?></td>
                            <td><?php echo $flight['flight_type']; ?></td>
                            <td>
                                <?php if($flight['is_active'] == 1): ?>
                                    <span class="status completed">Active</span>
                                <?php else: ?>
                                    <span class="status cancelled">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?section=flights&action=view&id=<?php echo $flight['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                <a href="?section=flights&action=edit&id=<?php echo $flight['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="?section=flights&action=delete&id=<?php echo $flight['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this flight?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Flight Statistics -->
<div class="content-section">
    <h3>Flight Statistics</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Top Routes</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Route statistics will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Bookings by Month</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Booking trend chart will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Seat Occupancy</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Seat occupancy chart will be displayed here</p>
            </div>
        </div>
    </div>
</div> 
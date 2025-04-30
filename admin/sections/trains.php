<?php
// Check for train add form submission
if(isset($_POST['add_train']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $train_number = mysqli_real_escape_string($conn, trim($_POST['train_number']));
    $train_name = mysqli_real_escape_string($conn, trim($_POST['train_name']));
    $departure_station = mysqli_real_escape_string($conn, trim($_POST['departure_station']));
    $arrival_station = mysqli_real_escape_string($conn, trim($_POST['arrival_station']));
    $departure_time = mysqli_real_escape_string($conn, trim($_POST['departure_time']));
    $arrival_time = mysqli_real_escape_string($conn, trim($_POST['arrival_time']));
    $price_sleeper = mysqli_real_escape_string($conn, trim($_POST['price_sleeper']));
    $price_ac3 = mysqli_real_escape_string($conn, trim($_POST['price_ac3']));
    $price_ac2 = mysqli_real_escape_string($conn, trim($_POST['price_ac2']));
    $price_ac1 = mysqli_real_escape_string($conn, trim($_POST['price_ac1']));
    $total_seats = mysqli_real_escape_string($conn, trim($_POST['total_seats']));
    $runs_on = isset($_POST['runs_on']) ? implode(',', $_POST['runs_on']) : '';
    
    // Validate input
    $errors = [];
    if(empty($train_number)) {
        $errors[] = "Train number is required";
    }
    
    if(empty($train_name)) {
        $errors[] = "Train name is required";
    }
    
    if(empty($departure_station)) {
        $errors[] = "Departure station is required";
    }
    
    if(empty($arrival_station)) {
        $errors[] = "Arrival station is required";
    }
    
    if(empty($departure_time)) {
        $errors[] = "Departure time is required";
    }
    
    if(empty($arrival_time)) {
        $errors[] = "Arrival time is required";
    }
    
    if(empty($price_sleeper) || !is_numeric($price_sleeper) || $price_sleeper < 0) {
        $errors[] = "Valid sleeper class price is required";
    }
    
    if(empty($total_seats) || !is_numeric($total_seats) || $total_seats <= 0) {
        $errors[] = "Valid total seats is required";
    }
    
    // Check if trains table exists, if not create it
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'trains'");
    if(mysqli_num_rows($check_table) == 0) {
        // Create trains table
        $create_table_sql = "CREATE TABLE trains (
            id INT AUTO_INCREMENT PRIMARY KEY,
            train_number VARCHAR(20) NOT NULL,
            train_name VARCHAR(100) NOT NULL,
            departure_station VARCHAR(100) NOT NULL,
            arrival_station VARCHAR(100) NOT NULL,
            departure_time DATETIME NOT NULL,
            arrival_time DATETIME NOT NULL,
            price_sleeper DECIMAL(10,2) NOT NULL,
            price_ac3 DECIMAL(10,2),
            price_ac2 DECIMAL(10,2),
            price_ac1 DECIMAL(10,2),
            total_seats INT NOT NULL,
            available_seats INT NOT NULL,
            runs_on VARCHAR(100),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if(!mysqli_query($conn, $create_table_sql)) {
            $errors[] = "Failed to create trains table: " . mysqli_error($conn);
        }
    }
    
    // If no errors, proceed with train creation
    if(empty($errors)) {
        // Insert train
        $available_seats = $total_seats; // Initially, all seats are available
        $insert_sql = "INSERT INTO trains (train_number, train_name, departure_station, arrival_station, departure_time, arrival_time, 
                                           price_sleeper, price_ac3, price_ac2, price_ac1, total_seats, available_seats, runs_on) 
                       VALUES ('$train_number', '$train_name', '$departure_station', '$arrival_station', '$departure_time', '$arrival_time', 
                               '$price_sleeper', '$price_ac3', '$price_ac2', '$price_ac1', '$total_seats', '$available_seats', '$runs_on')";
        
        if(mysqli_query($conn, $insert_sql)) {
            $success_message = "Train added successfully!";
            // Clear form data
            unset($train_number, $train_name, $departure_station, $arrival_station, $departure_time, $arrival_time, 
                  $price_sleeper, $price_ac3, $price_ac2, $price_ac1, $total_seats, $runs_on);
        } else {
            $errors[] = "Failed to add train: " . mysqli_error($conn);
        }
    }
}

// Fetch all trains
$trains = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'trains'");
if(mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT * FROM trains ORDER BY departure_time ASC");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $trains[] = $row;
        }
    }
}
?>

<div class="dashboard-title">
    <h2>Train Management</h2>
</div>

<!-- Add Train Form -->
<div class="content-section">
    <h3>Add New Train</h3>
    
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
                <label for="train_number" style="display: block; margin-bottom: 5px; font-weight: 500;">Train Number:</label>
                <input type="text" id="train_number" name="train_number" value="<?php echo isset($train_number) ? $train_number : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="train_name" style="display: block; margin-bottom: 5px; font-weight: 500;">Train Name:</label>
                <input type="text" id="train_name" name="train_name" value="<?php echo isset($train_name) ? $train_name : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="departure_station" style="display: block; margin-bottom: 5px; font-weight: 500;">Departure Station:</label>
                <input type="text" id="departure_station" name="departure_station" value="<?php echo isset($departure_station) ? $departure_station : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="arrival_station" style="display: block; margin-bottom: 5px; font-weight: 500;">Arrival Station:</label>
                <input type="text" id="arrival_station" name="arrival_station" value="<?php echo isset($arrival_station) ? $arrival_station : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
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
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Pricing (₹):</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label for="price_sleeper" style="display: block; margin-bottom: 5px;">Sleeper:</label>
                    <input type="number" id="price_sleeper" name="price_sleeper" min="0" step="0.01" value="<?php echo isset($price_sleeper) ? $price_sleeper : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                </div>
                <div>
                    <label for="price_ac3" style="display: block; margin-bottom: 5px;">AC 3 Tier:</label>
                    <input type="number" id="price_ac3" name="price_ac3" min="0" step="0.01" value="<?php echo isset($price_ac3) ? $price_ac3 : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label for="price_ac2" style="display: block; margin-bottom: 5px;">AC 2 Tier:</label>
                    <input type="number" id="price_ac2" name="price_ac2" min="0" step="0.01" value="<?php echo isset($price_ac2) ? $price_ac2 : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label for="price_ac1" style="display: block; margin-bottom: 5px;">AC 1 Tier:</label>
                    <input type="number" id="price_ac1" name="price_ac1" min="0" step="0.01" value="<?php echo isset($price_ac1) ? $price_ac1 : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="total_seats" style="display: block; margin-bottom: 5px; font-weight: 500;">Total Seats:</label>
            <input type="number" id="total_seats" name="total_seats" min="1" value="<?php echo isset($total_seats) ? $total_seats : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Runs on:</label>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Mon" <?php echo (isset($runs_on) && strpos($runs_on, 'Mon') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Monday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Tue" <?php echo (isset($runs_on) && strpos($runs_on, 'Tue') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Tuesday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Wed" <?php echo (isset($runs_on) && strpos($runs_on, 'Wed') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Wednesday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Thu" <?php echo (isset($runs_on) && strpos($runs_on, 'Thu') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Thursday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Fri" <?php echo (isset($runs_on) && strpos($runs_on, 'Fri') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Friday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Sat" <?php echo (isset($runs_on) && strpos($runs_on, 'Sat') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Saturday</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="runs_on[]" value="Sun" <?php echo (isset($runs_on) && strpos($runs_on, 'Sun') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Sunday</span>
                </label>
            </div>
        </div>
        
        <div>
            <button type="submit" name="add_train" style="background: linear-gradient(to right, #4776E6, #8E54E9); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-train"></i> Add Train
            </button>
        </div>
    </form>
</div>

<!-- Train List -->
<div class="content-section">
    <h3>Train Schedule</h3>
    
    <?php if(empty($trains)): ?>
        <p style="color: #6c757d; font-style: italic;">No trains found. Add a new train using the form above.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Train No.</th>
                        <th>Name</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Sleeper Price</th>
                        <th>AC Prices</th>
                        <th>Available</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($trains as $train): ?>
                        <tr>
                            <td><?php echo $train['train_number']; ?></td>
                            <td><?php echo $train['train_name']; ?></td>
                            <td><?php echo $train['departure_station']; ?> to <?php echo $train['arrival_station']; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($train['departure_time'])); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($train['arrival_time'])); ?></td>
                            <td>₹<?php echo number_format($train['price_sleeper'], 2); ?></td>
                            <td>
                                <?php if(!empty($train['price_ac3'])): ?>
                                    3A: ₹<?php echo number_format($train['price_ac3'], 2); ?><br>
                                <?php endif; ?>
                                <?php if(!empty($train['price_ac2'])): ?>
                                    2A: ₹<?php echo number_format($train['price_ac2'], 2); ?><br>
                                <?php endif; ?>
                                <?php if(!empty($train['price_ac1'])): ?>
                                    1A: ₹<?php echo number_format($train['price_ac1'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $train['available_seats']; ?>/<?php echo $train['total_seats']; ?></td>
                            <td>
                                <?php if($train['is_active'] == 1): ?>
                                    <span class="status completed">Active</span>
                                <?php else: ?>
                                    <span class="status cancelled">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?section=trains&action=view&id=<?php echo $train['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                <a href="?section=trains&action=edit&id=<?php echo $train['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="?section=trains&action=delete&id=<?php echo $train['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this train?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Train Statistics -->
<div class="content-section">
    <h3>Train Statistics</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Popular Routes</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Route statistics will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Bookings Trend</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Booking trend chart will be displayed here</p>
            </div>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h4 style="color: #343a40; margin-bottom: 15px;">Revenue by Class</h4>
            <div style="height: 200px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border-radius: 5px;">
                <p style="color: #6c757d; font-style: italic;">Revenue chart by train class will be displayed here</p>
            </div>
        </div>
    </div>
</div> 
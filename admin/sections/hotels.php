<?php
// Check for hotel add form submission
if(isset($_POST['add_hotel']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $hotel_name = mysqli_real_escape_string($conn, trim($_POST['hotel_name']));
    $location = mysqli_real_escape_string($conn, trim($_POST['location']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price_per_night = mysqli_real_escape_string($conn, trim($_POST['price_per_night']));
    $rating = mysqli_real_escape_string($conn, trim($_POST['rating']));
    $facilities = isset($_POST['facilities']) ? implode(',', $_POST['facilities']) : '';
    
    // Validate input
    $errors = [];
    if(empty($hotel_name)) {
        $errors[] = "Hotel name is required";
    }
    
    if(empty($location)) {
        $errors[] = "Location is required";
    }
    
    if(empty($description)) {
        $errors[] = "Description is required";
    }
    
    if(empty($price_per_night) || !is_numeric($price_per_night)) {
        $errors[] = "Valid price per night is required";
    }
    
    if(empty($rating) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
        $errors[] = "Rating must be between 1 and 5";
    }
    
    // Check if hotels table exists, if not create it
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
    if(mysqli_num_rows($check_table) == 0) {
        // Create hotels table
        $create_table_sql = "CREATE TABLE hotels (
            id INT AUTO_INCREMENT PRIMARY KEY,
            hotel_name VARCHAR(100) NOT NULL,
            location VARCHAR(100) NOT NULL,
            description TEXT NOT NULL,
            price_per_night DECIMAL(10,2) NOT NULL,
            rating DECIMAL(3,1) NOT NULL,
            facilities TEXT,
            image_url VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if(!mysqli_query($conn, $create_table_sql)) {
            $errors[] = "Failed to create hotels table: " . mysqli_error($conn);
        }
    }
    
    // If no errors, proceed with hotel creation
    if(empty($errors)) {
        // Process image upload if provided
        $image_url = '';
        if(isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['hotel_image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Verify file extension
            if(in_array(strtolower($filetype), $allowed)) {
                // Create upload directory if it doesn't exist
                $upload_dir = '../uploads/hotels/';
                if(!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Create unique filename
                $new_filename = 'hotel_' . time() . '.' . $filetype;
                $upload_path = $upload_dir . $new_filename;
                
                // Upload file
                if(move_uploaded_file($_FILES['hotel_image']['tmp_name'], $upload_path)) {
                    $image_url = 'uploads/hotels/' . $new_filename;
                } else {
                    $errors[] = "Failed to upload image";
                }
            } else {
                $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowed);
            }
        }
        
        if(empty($errors)) {
            // Insert hotel
            $insert_sql = "INSERT INTO hotels (hotel_name, location, description, price_per_night, rating, facilities, image_url) 
                           VALUES ('$hotel_name', '$location', '$description', '$price_per_night', '$rating', '$facilities', '$image_url')";
            
            if(mysqli_query($conn, $insert_sql)) {
                $success_message = "Hotel added successfully!";
                // Clear form data
                unset($hotel_name, $location, $description, $price_per_night, $rating, $facilities);
            } else {
                $errors[] = "Failed to add hotel: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all hotels
$hotels = [];
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
if(mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT * FROM hotels ORDER BY created_at DESC");
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $hotels[] = $row;
        }
    }
}
?>

<div class="dashboard-title">
    <h2>Hotel Management</h2>
</div>

<!-- Add Hotel Form -->
<div class="content-section">
    <h3>Add New Hotel</h3>
    
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
    
    <form method="POST" action="" enctype="multipart/form-data" style="max-width: 800px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="hotel_name" style="display: block; margin-bottom: 5px; font-weight: 500;">Hotel Name:</label>
                <input type="text" id="hotel_name" name="hotel_name" value="<?php echo isset($hotel_name) ? $hotel_name : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="location" style="display: block; margin-bottom: 5px; font-weight: 500;">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo isset($location) ? $location : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="description" style="display: block; margin-bottom: 5px; font-weight: 500;">Description:</label>
            <textarea id="description" name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required><?php echo isset($description) ? $description : ''; ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="price_per_night" style="display: block; margin-bottom: 5px; font-weight: 500;">Price Per Night (₹):</label>
                <input type="number" id="price_per_night" name="price_per_night" min="1" step="0.01" value="<?php echo isset($price_per_night) ? $price_per_night : ''; ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="rating" style="display: block; margin-bottom: 5px; font-weight: 500;">Rating (1-5):</label>
                <select id="rating" name="rating" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" required>
                    <option value="">Select Rating</option>
                    <option value="1" <?php echo (isset($rating) && $rating == 1) ? 'selected' : ''; ?>>1 Star</option>
                    <option value="2" <?php echo (isset($rating) && $rating == 2) ? 'selected' : ''; ?>>2 Stars</option>
                    <option value="3" <?php echo (isset($rating) && $rating == 3) ? 'selected' : ''; ?>>3 Stars</option>
                    <option value="4" <?php echo (isset($rating) && $rating == 4) ? 'selected' : ''; ?>>4 Stars</option>
                    <option value="5" <?php echo (isset($rating) && $rating == 5) ? 'selected' : ''; ?>>5 Stars</option>
                </select>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Facilities:</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="wifi" <?php echo (isset($facilities) && strpos($facilities, 'wifi') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">WiFi</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="parking" <?php echo (isset($facilities) && strpos($facilities, 'parking') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Parking</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="restaurant" <?php echo (isset($facilities) && strpos($facilities, 'restaurant') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Restaurant</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="pool" <?php echo (isset($facilities) && strpos($facilities, 'pool') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Swimming Pool</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="gym" <?php echo (isset($facilities) && strpos($facilities, 'gym') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Gym</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="spa" <?php echo (isset($facilities) && strpos($facilities, 'spa') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Spa</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="ac" <?php echo (isset($facilities) && strpos($facilities, 'ac') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Air Conditioning</span>
                </label>
                <label style="display: flex; align-items: center;">
                    <input type="checkbox" name="facilities[]" value="room_service" <?php echo (isset($facilities) && strpos($facilities, 'room_service') !== false) ? 'checked' : ''; ?>>
                    <span style="margin-left: 5px;">Room Service</span>
                </label>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="hotel_image" style="display: block; margin-bottom: 5px; font-weight: 500;">Hotel Image:</label>
            <input type="file" id="hotel_image" name="hotel_image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <small style="color: #6c757d; display: block; margin-top: 5px;">Recommended image size: 800x600 pixels</small>
        </div>
        
        <div>
            <button type="submit" name="add_hotel" style="background: linear-gradient(to right, #4776E6, #8E54E9); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                <i class="fas fa-plus-circle"></i> Add Hotel
            </button>
        </div>
    </form>
</div>

<!-- Hotel List -->
<div class="content-section">
    <h3>Hotel List</h3>
    
    <?php if(empty($hotels)): ?>
        <p style="color: #6c757d; font-style: italic;">No hotels found. Add a new hotel using the form above.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Hotel Name</th>
                        <th>Location</th>
                        <th>Price/Night</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($hotels as $hotel): ?>
                        <tr>
                            <td><?php echo $hotel['id']; ?></td>
                            <td>
                                <?php if(!empty($hotel['image_url'])): ?>
                                    <img src="../<?php echo $hotel['image_url']; ?>" alt="<?php echo $hotel['hotel_name']; ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #6c757d; border-radius: 4px;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $hotel['hotel_name']; ?></td>
                            <td><?php echo $hotel['location']; ?></td>
                            <td>₹<?php echo number_format($hotel['price_per_night'], 2); ?></td>
                            <td>
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if($i <= $hotel['rating']) {
                                        echo '<i class="fas fa-star" style="color: gold;"></i>';
                                    } else {
                                        echo '<i class="far fa-star" style="color: #ccc;"></i>';
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <?php if($hotel['is_active'] == 1): ?>
                                    <span class="status completed">Active</span>
                                <?php else: ?>
                                    <span class="status cancelled">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?section=hotels&action=view&id=<?php echo $hotel['id']; ?>" class="action-btn view-btn"><i class="fas fa-eye"></i></a>
                                <a href="?section=hotels&action=edit&id=<?php echo $hotel['id']; ?>" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                                <a href="?section=hotels&action=delete&id=<?php echo $hotel['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this hotel?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div> 
<?php
if(!function_exists('check_booking_tables')) {
    function check_booking_tables($conn) {
        $result = [
            'tables_created' => [],
            'errors' => []
        ];
        
        // 1. Check and create users table if it doesn't exist
        $check_users = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
        if(mysqli_num_rows($check_users) == 0) {
            $create_users_sql = "CREATE TABLE users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                full_name VARCHAR(100) NOT NULL,
                user_email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                city VARCHAR(100),
                country VARCHAR(100),
                postal_code VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active TINYINT(1) DEFAULT 1
            )";
            
            if(mysqli_query($conn, $create_users_sql)) {
                $result['tables_created'][] = 'users';
            } else {
                $result['errors'][] = "Failed to create users table: " . mysqli_error($conn);
            }
        }
        
        // 2. Check and create hotels table if it doesn't exist
        $check_hotels = mysqli_query($conn, "SHOW TABLES LIKE 'hotels'");
        if(mysqli_num_rows($check_hotels) == 0) {
            $create_hotels_sql = "CREATE TABLE hotels (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hotel_name VARCHAR(100) NOT NULL,
                location VARCHAR(100) NOT NULL,
                address TEXT NOT NULL,
                description TEXT,
                rating DECIMAL(3,1) DEFAULT 0,
                price_per_night DECIMAL(10,2) NOT NULL,
                amenities TEXT,
                room_types TEXT,
                image VARCHAR(255),
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_hotels_sql)) {
                $result['tables_created'][] = 'hotels';
            } else {
                $result['errors'][] = "Failed to create hotels table: " . mysqli_error($conn);
            }
        }
        
        // 3. Check and create flights table if it doesn't exist
        $check_flights = mysqli_query($conn, "SHOW TABLES LIKE 'flights'");
        if(mysqli_num_rows($check_flights) == 0) {
            $create_flights_sql = "CREATE TABLE flights (
                id INT AUTO_INCREMENT PRIMARY KEY,
                airline_name VARCHAR(100) NOT NULL,
                flight_number VARCHAR(20) NOT NULL,
                source VARCHAR(100) NOT NULL,
                destination VARCHAR(100) NOT NULL,
                departure_time TIME NOT NULL,
                arrival_time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                seats_available INT NOT NULL,
                class_options VARCHAR(255) NOT NULL,
                duration VARCHAR(50) NOT NULL,
                image VARCHAR(255),
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_flights_sql)) {
                $result['tables_created'][] = 'flights';
            } else {
                $result['errors'][] = "Failed to create flights table: " . mysqli_error($conn);
            }
        }
        
        // 4. Check and create trains table if it doesn't exist
        $check_trains = mysqli_query($conn, "SHOW TABLES LIKE 'trains'");
        if(mysqli_num_rows($check_trains) == 0) {
            $create_trains_sql = "CREATE TABLE trains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                train_name VARCHAR(100) NOT NULL,
                train_number VARCHAR(20) NOT NULL,
                source VARCHAR(100) NOT NULL,
                destination VARCHAR(100) NOT NULL,
                departure_time TIME NOT NULL,
                arrival_time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                seats_available INT NOT NULL,
                class_options VARCHAR(255) NOT NULL,
                duration VARCHAR(50) NOT NULL,
                image VARCHAR(255),
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_trains_sql)) {
                $result['tables_created'][] = 'trains';
            } else {
                $result['errors'][] = "Failed to create trains table: " . mysqli_error($conn);
            }
        }
        
        // 5. Check and create hotel_bookings table if it doesn't exist
        $check_hotel_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'hotel_bookings'");
        if(mysqli_num_rows($check_hotel_bookings) == 0) {
            $create_hotel_bookings_sql = "CREATE TABLE hotel_bookings (
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
            
            if(mysqli_query($conn, $create_hotel_bookings_sql)) {
                $result['tables_created'][] = 'hotel_bookings';
            } else {
                $result['errors'][] = "Failed to create hotel_bookings table: " . mysqli_error($conn);
            }
        }
        
        // 6. Check and create flight_bookings table if it doesn't exist
        $check_flight_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'flight_bookings'");
        if(mysqli_num_rows($check_flight_bookings) == 0) {
            $create_flight_bookings_sql = "CREATE TABLE flight_bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_number VARCHAR(20) NOT NULL UNIQUE,
                user_id INT NOT NULL,
                flight_id INT NOT NULL,
                departure_date DATE NOT NULL,
                return_date DATE,
                passengers INT NOT NULL,
                travel_class VARCHAR(50) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                payment_method VARCHAR(50),
                payment_status VARCHAR(20) DEFAULT 'pending',
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_flight_bookings_sql)) {
                $result['tables_created'][] = 'flight_bookings';
            } else {
                $result['errors'][] = "Failed to create flight_bookings table: " . mysqli_error($conn);
            }
        }
        
        // 7. Check and create train_bookings table if it doesn't exist
        $check_train_bookings = mysqli_query($conn, "SHOW TABLES LIKE 'train_bookings'");
        if(mysqli_num_rows($check_train_bookings) == 0) {
            $create_train_bookings_sql = "CREATE TABLE train_bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_number VARCHAR(20) NOT NULL UNIQUE,
                user_id INT NOT NULL,
                train_id INT NOT NULL,
                journey_date DATE NOT NULL,
                passengers INT NOT NULL,
                seat_class VARCHAR(50) NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                payment_method VARCHAR(50),
                payment_status VARCHAR(20) DEFAULT 'pending',
                status VARCHAR(20) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_train_bookings_sql)) {
                $result['tables_created'][] = 'train_bookings';
            } else {
                $result['errors'][] = "Failed to create train_bookings table: " . mysqli_error($conn);
            }
        }
        
        // 8. Check and create passengers table if it doesn't exist
        $check_passengers = mysqli_query($conn, "SHOW TABLES LIKE 'passengers'");
        if(mysqli_num_rows($check_passengers) == 0) {
            $create_passengers_sql = "CREATE TABLE passengers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT NOT NULL,
                booking_type ENUM('flight', 'train') NOT NULL,
                name VARCHAR(100) NOT NULL,
                age INT NOT NULL,
                gender VARCHAR(10) NOT NULL,
                seat_number VARCHAR(10),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_passengers_sql)) {
                $result['tables_created'][] = 'passengers';
            } else {
                $result['errors'][] = "Failed to create passengers table: " . mysqli_error($conn);
            }
        }
        
        // 9. Check and create settings table if it doesn't exist
        $check_settings = mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
        if(mysqli_num_rows($check_settings) == 0) {
            $create_settings_sql = "CREATE TABLE settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";
            
            if(mysqli_query($conn, $create_settings_sql)) {
                $result['tables_created'][] = 'settings';
                // Insert default settings
                $default_settings = [
                    ['site_name', 'GoJourney'],
                    ['site_email', 'info@gojourney.com'],
                    ['contact_phone', '+91 9876543210'],
                    ['contact_address', '123 Travel Street, Mumbai, India'],
                    ['currency', '₹']
                ];
                
                foreach($default_settings as $setting) {
                    mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$setting[0]', '$setting[1]')");
                }
            } else {
                $result['errors'][] = "Failed to create settings table: " . mysqli_error($conn);
            }
        }
        
        // 10. Check and create admins table if it doesn't exist
        $check_admins = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
        if(mysqli_num_rows($check_admins) == 0) {
            $create_admins_sql = "CREATE TABLE admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fullname VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL
            )";
            
            if(mysqli_query($conn, $create_admins_sql)) {
                $result['tables_created'][] = 'admins';
                // Insert default admin
                $default_password_hash = password_hash('admin123', PASSWORD_DEFAULT);
                mysqli_query($conn, "INSERT INTO admins (id, fullname, email, password_hash) VALUES (1, 'Admin', 'admin@gojourney.com', '$default_password_hash')");
            } else {
                $result['errors'][] = "Failed to create admins table: " . mysqli_error($conn);
            }
        }
        
        return $result;
    }
}

// If this file is called directly, run the check and display results
if(basename($_SERVER['PHP_SELF']) == 'check_booking_tables.php') {
    require_once '../connection/db_connect.php';
    $result = check_booking_tables($conn);
    
    // Display results
    echo "<h1>Database Tables Check</h1>";
    
    if(!empty($result['tables_created'])) {
        echo "<h2>Tables Created:</h2>";
        echo "<ul>";
        foreach($result['tables_created'] as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tables needed to be created. All required tables already exist.</p>";
    }
    
    if(!empty($result['errors'])) {
        echo "<h2>Errors:</h2>";
        echo "<ul>";
        foreach($result['errors'] as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
}
?> 
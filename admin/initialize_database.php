<?php
// Include database connection
require_once '../connection/db_connect.php';

// Function to check if a table exists
function tableExists($conn, $tableName) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
    return mysqli_num_rows($result) > 0;
}

// Create passengers table if it doesn't exist
if(!tableExists($conn, 'passengers')) {
    $create_passengers_sql = "CREATE TABLE passengers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT NOT NULL,
        booking_type VARCHAR(20) NOT NULL,
        name VARCHAR(100) NOT NULL,
        age INT NOT NULL,
        gender VARCHAR(10) NOT NULL,
        seat_number VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if(mysqli_query($conn, $create_passengers_sql)) {
        echo "Passengers table created successfully.<br>";
    } else {
        echo "Error creating passengers table: " . mysqli_error($conn) . "<br>";
    }
}

// Create flights table if it doesn't exist
if(!tableExists($conn, 'flights')) {
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
        echo "Flights table created successfully.<br>";
        
        // Insert sample data
        $sample_flights = [
            [
                'airline_name' => 'Air India',
                'flight_number' => 'AI-123',
                'source' => 'Delhi',
                'destination' => 'Mumbai',
                'departure_time' => '08:00:00',
                'arrival_time' => '10:00:00',
                'price' => 5000,
                'seats_available' => 120,
                'class_options' => 'Economy,Business,First Class',
                'duration' => '2h 0m',
                'image' => 'assets/images/flights/air-india.png',
                'status' => 'active'
            ],
            [
                'airline_name' => 'IndiGo',
                'flight_number' => 'IN-456',
                'source' => 'Mumbai',
                'destination' => 'Bangalore',
                'departure_time' => '12:00:00',
                'arrival_time' => '13:30:00',
                'price' => 4200,
                'seats_available' => 180,
                'class_options' => 'Economy,Business',
                'duration' => '1h 30m',
                'image' => 'assets/images/flights/indigo.png',
                'status' => 'active'
            ],
            [
                'airline_name' => 'SpiceJet',
                'flight_number' => 'SJ-789',
                'source' => 'Chennai',
                'destination' => 'Kolkata',
                'departure_time' => '14:30:00',
                'arrival_time' => '16:30:00',
                'price' => 3800,
                'seats_available' => 150,
                'class_options' => 'Economy,Business',
                'duration' => '2h 0m',
                'image' => 'assets/images/flights/spicejet.png',
                'status' => 'active'
            ]
        ];
        
        foreach($sample_flights as $flight) {
            $insert_sql = "INSERT INTO flights (airline_name, flight_number, source, destination, departure_time, arrival_time, price, seats_available, class_options, duration, image, status) 
                          VALUES ('{$flight['airline_name']}', '{$flight['flight_number']}', '{$flight['source']}', '{$flight['destination']}', '{$flight['departure_time']}', '{$flight['arrival_time']}', {$flight['price']}, {$flight['seats_available']}, '{$flight['class_options']}', '{$flight['duration']}', '{$flight['image']}', '{$flight['status']}')";
            if(mysqli_query($conn, $insert_sql)) {
                echo "Sample flight '{$flight['airline_name']} {$flight['flight_number']}' added.<br>";
            } else {
                echo "Error adding sample flight: " . mysqli_error($conn) . "<br>";
            }
        }
    } else {
        echo "Error creating flights table: " . mysqli_error($conn) . "<br>";
    }
}

// Create trains table if it doesn't exist
if(!tableExists($conn, 'trains')) {
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
        echo "Trains table created successfully.<br>";
        
        // Insert sample data
        $sample_trains = [
            [
                'train_name' => 'Rajdhani Express',
                'train_number' => 'RJ-123',
                'source' => 'Delhi',
                'destination' => 'Mumbai',
                'departure_time' => '16:00:00',
                'arrival_time' => '08:00:00',
                'price' => 1200,
                'seats_available' => 500,
                'class_options' => 'Sleeper,AC 3 Tier,AC 2 Tier,AC First Class',
                'duration' => '16h 0m',
                'image' => 'assets/images/trains/rajdhani.jpg',
                'status' => 'active'
            ],
            [
                'train_name' => 'Shatabdi Express',
                'train_number' => 'SH-456',
                'source' => 'Mumbai',
                'destination' => 'Pune',
                'departure_time' => '06:00:00',
                'arrival_time' => '10:00:00',
                'price' => 800,
                'seats_available' => 400,
                'class_options' => 'Chair Car,Executive Chair Car',
                'duration' => '4h 0m',
                'image' => 'assets/images/trains/shatabdi.jpg',
                'status' => 'active'
            ],
            [
                'train_name' => 'Duronto Express',
                'train_number' => 'DR-789',
                'source' => 'Chennai',
                'destination' => 'Bangalore',
                'departure_time' => '23:00:00',
                'arrival_time' => '06:00:00',
                'price' => 950,
                'seats_available' => 450,
                'class_options' => 'Sleeper,AC 3 Tier,AC 2 Tier',
                'duration' => '7h 0m',
                'image' => 'assets/images/trains/duronto.jpg',
                'status' => 'active'
            ]
        ];
        
        foreach($sample_trains as $train) {
            $insert_sql = "INSERT INTO trains (train_name, train_number, source, destination, departure_time, arrival_time, price, seats_available, class_options, duration, image, status) 
                          VALUES ('{$train['train_name']}', '{$train['train_number']}', '{$train['source']}', '{$train['destination']}', '{$train['departure_time']}', '{$train['arrival_time']}', {$train['price']}, {$train['seats_available']}, '{$train['class_options']}', '{$train['duration']}', '{$train['image']}', '{$train['status']}')";
            if(mysqli_query($conn, $insert_sql)) {
                echo "Sample train '{$train['train_name']}' added.<br>";
            } else {
                echo "Error adding sample train: " . mysqli_error($conn) . "<br>";
            }
        }
    } else {
        echo "Error creating trains table: " . mysqli_error($conn) . "<br>";
    }
}

// Create hotels table if it doesn't exist
if(!tableExists($conn, 'hotels')) {
    $create_hotels_sql = "CREATE TABLE hotels (
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
    
    if(mysqli_query($conn, $create_hotels_sql)) {
        echo "Hotels table created successfully.<br>";
        
        // Insert sample data
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
            if(mysqli_query($conn, $insert_sql)) {
                echo "Sample hotel '{$hotel['hotel_name']}' added.<br>";
            } else {
                echo "Error adding sample hotel: " . mysqli_error($conn) . "<br>";
            }
        }
    } else {
        echo "Error creating hotels table: " . mysqli_error($conn) . "<br>";
    }
}

// Create flight_bookings table if it doesn't exist
if(!tableExists($conn, 'flight_bookings')) {
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
        echo "Flight bookings table created successfully.<br>";
    } else {
        echo "Error creating flight bookings table: " . mysqli_error($conn) . "<br>";
    }
}

// Create train_bookings table if it doesn't exist
if(!tableExists($conn, 'train_bookings')) {
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
        echo "Train bookings table created successfully.<br>";
    } else {
        echo "Error creating train bookings table: " . mysqli_error($conn) . "<br>";
    }
}

// Create hotel_bookings table if it doesn't exist
if(!tableExists($conn, 'hotel_bookings')) {
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
        echo "Hotel bookings table created successfully.<br>";
    } else {
        echo "Error creating hotel bookings table: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Database initialization completed. <a href='../admin/dashboard.php'>Go to Admin Dashboard</a>";
?> 
-- Create bookings table if it doesn't exist
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `booking_reference` varchar(10) NOT NULL,
  `pnr` varchar(15) NOT NULL,
  `booking_type` enum('train','flight') NOT NULL,
  `transport_id` varchar(20) NOT NULL,
  `from_location` varchar(100) NOT NULL,
  `to_location` varchar(100) NOT NULL,
  `journey_date` date NOT NULL,
  `travel_class` varchar(50) NOT NULL,
  `total_passengers` int(11) NOT NULL,
  `booking_date` datetime NOT NULL,
  `payment_status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `booking_status` enum('confirmed','waiting','cancelled') NOT NULL DEFAULT 'confirmed',
  `amount` decimal(10,2) NOT NULL,
  `ticket_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  UNIQUE KEY `pnr` (`pnr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create passenger details table if it doesn't exist
CREATE TABLE IF NOT EXISTS `passengers` (
  `passenger_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `seat_preference` varchar(50) DEFAULT NULL,
  `meal_preference` varchar(50) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`passenger_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payments table if it doesn't exist
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `payment_status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`payment_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 
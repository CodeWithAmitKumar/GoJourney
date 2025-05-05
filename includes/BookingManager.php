<?php
/**
 * BookingManager - Class to handle all booking related operations
 */
class BookingManager {
    private $conn;
    
    /**
     * Constructor - Initialize with database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Save booking to database
     * 
     * @param array $bookingData Booking information
     * @param array $passengerData Passenger information
     * @param array $paymentData Payment information
     * @return int|bool Booking ID on success, false on failure
     */
    public function saveBooking($bookingData, $passengerData, $paymentData) {
        try {
            // Start transaction
            mysqli_begin_transaction($this->conn);
            
            // Insert into bookings table
            $bookingSql = "INSERT INTO bookings (
                user_id, booking_reference, pnr, booking_type, 
                transport_id, from_location, to_location, journey_date, 
                travel_class, total_passengers, booking_date, 
                payment_status, booking_status, amount
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $bookingStmt = mysqli_prepare($this->conn, $bookingSql);
            
            if (!$bookingStmt) {
                throw new Exception("Prepare failed: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($bookingStmt, 
                "issssssssisssd", 
                $bookingData['user_id'],
                $bookingData['booking_reference'],
                $bookingData['pnr'],
                $bookingData['booking_type'],
                $bookingData['transport_id'],
                $bookingData['from_location'],
                $bookingData['to_location'],
                $bookingData['journey_date'],
                $bookingData['travel_class'],
                $bookingData['total_passengers'],
                $bookingData['booking_date'],
                $bookingData['payment_status'],
                $bookingData['booking_status'],
                $bookingData['amount']
            );
            
            $result = mysqli_stmt_execute($bookingStmt);
            
            if (!$result) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($bookingStmt));
            }
            
            $bookingId = mysqli_insert_id($this->conn);
            mysqli_stmt_close($bookingStmt);
            
            // Insert passenger details
            foreach ($passengerData as $passenger) {
                $passengerSql = "INSERT INTO passengers (
                    booking_id, name, age, gender, seat_preference, meal_preference
                ) VALUES (?, ?, ?, ?, ?, ?)";
                
                $passengerStmt = mysqli_prepare($this->conn, $passengerSql);
                
                if (!$passengerStmt) {
                    throw new Exception("Prepare failed for passenger: " . mysqli_error($this->conn));
                }
                
                mysqli_stmt_bind_param($passengerStmt, 
                    "isisss", 
                    $bookingId,
                    $passenger['name'],
                    $passenger['age'],
                    $passenger['gender'],
                    $passenger['seat_preference'],
                    $passenger['meal_preference']
                );
                
                $result = mysqli_stmt_execute($passengerStmt);
                
                if (!$result) {
                    throw new Exception("Execute failed for passenger: " . mysqli_stmt_error($passengerStmt));
                }
                
                mysqli_stmt_close($passengerStmt);
            }
            
            // Insert payment details
            $paymentSql = "INSERT INTO payments (
                booking_id, payment_method, amount, payment_date, payment_status, transaction_id
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $paymentStmt = mysqli_prepare($this->conn, $paymentSql);
            
            if (!$paymentStmt) {
                throw new Exception("Prepare failed for payment: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($paymentStmt, 
                "isdsss", 
                $bookingId,
                $paymentData['payment_method'],
                $paymentData['amount'],
                $paymentData['payment_date'],
                $paymentData['payment_status'],
                $paymentData['transaction_id']
            );
            
            $result = mysqli_stmt_execute($paymentStmt);
            
            if (!$result) {
                throw new Exception("Execute failed for payment: " . mysqli_stmt_error($paymentStmt));
            }
            
            mysqli_stmt_close($paymentStmt);
            
            // Commit transaction
            mysqli_commit($this->conn);
            
            return $bookingId;
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($this->conn);
            error_log("Error saving booking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's bookings
     * 
     * @param int $userId User ID
     * @param string $filter Optional filter (all, upcoming, completed, cancelled)
     * @return array Bookings
     */
    public function getUserBookings($userId, $filter = 'all') {
        $sql = "SELECT b.*, p.payment_status AS payment_status 
                FROM bookings b 
                LEFT JOIN payments p ON b.booking_id = p.booking_id 
                WHERE b.user_id = ?";
        
        // Add filter conditions
        if ($filter === 'upcoming') {
            $sql .= " AND b.journey_date >= CURDATE() AND b.booking_status != 'cancelled'";
        } elseif ($filter === 'completed') {
            $sql .= " AND b.journey_date < CURDATE() AND b.booking_status = 'confirmed'";
        } elseif ($filter === 'cancelled') {
            $sql .= " AND b.booking_status = 'cancelled'";
        }
        
        $sql .= " ORDER BY b.booking_date DESC";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->conn));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $bookings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $bookings[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        return $bookings;
    }
    
    /**
     * Get booking details by ID
     * 
     * @param int $bookingId Booking ID
     * @param int $userId User ID (for security check)
     * @return array|bool Booking details on success, false on failure
     */
    public function getBookingById($bookingId, $userId) {
        // Get booking details
        $sql = "SELECT b.*, p.payment_method, p.payment_status, p.payment_date AS payment_timestamp, p.transaction_id
                FROM bookings b 
                LEFT JOIN payments p ON b.booking_id = p.booking_id 
                WHERE b.booking_id = ? AND b.user_id = ?";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $bookingId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            return false;
        }
        
        $booking = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Get passenger details
        $sql = "SELECT * FROM passengers WHERE booking_id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Prepare failed for passengers: " . mysqli_error($this->conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $passengers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $passengers[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        $booking['passengers'] = $passengers;
        
        return $booking;
    }
    
    /**
     * Cancel a booking
     * 
     * @param int $bookingId Booking ID
     * @param int $userId User ID (for security check)
     * @return bool True on success, false on failure
     */
    public function cancelBooking($bookingId, $userId) {
        $sql = "UPDATE bookings SET booking_status = 'cancelled' 
                WHERE booking_id = ? AND user_id = ? AND booking_status != 'cancelled'";
        
        $stmt = mysqli_prepare($this->conn, $sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->conn));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $bookingId, $userId);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            return false;
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affectedRows > 0;
    }
} 
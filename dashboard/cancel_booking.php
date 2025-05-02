<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Include database connection
require_once '../connection/db_connect.php';

// Include BookingManager class
require_once '../includes/BookingManager.php';

// Check if booking ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No booking ID provided.";
    header("Location: my_bookings.php");
    exit();
}

$bookingId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Initialize BookingManager
$bookingManager = new BookingManager($conn);

// Attempt to cancel the booking
$success = $bookingManager->cancelBooking($bookingId, $userId);

if ($success) {
    $_SESSION['success'] = "Your booking has been cancelled successfully.";
} else {
    $_SESSION['error'] = "Unable to cancel booking. Please try again or contact support.";
}

// Redirect back to My Bookings
header("Location: my_bookings.php");
exit(); 
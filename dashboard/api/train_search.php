<?php
// This file handles the API integration for train search functionality

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../connection/db_connect.php';

// Set header to return JSON
header('Content-Type: application/json');

// Define error response function
function sendErrorResponse($message) {
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse("Invalid request method");
}

// Get search parameters from POST request
$fromStation = isset($_POST['from']) ? $_POST['from'] : '';
$toStation = isset($_POST['to']) ? $_POST['to'] : '';
$travelDate = isset($_POST['date']) ? $_POST['date'] : '';
$travelClass = isset($_POST['class']) ? $_POST['class'] : '';
$passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;

// Validate input
if (empty($fromStation) || empty($toStation) || empty($travelDate)) {
    sendErrorResponse("Please provide all required fields");
}

// Sanitize inputs for security
$fromStation = mysqli_real_escape_string($conn, $fromStation);
$toStation = mysqli_real_escape_string($conn, $toStation);
$travelDate = mysqli_real_escape_string($conn, $travelDate);
$travelClass = mysqli_real_escape_string($conn, $travelClass);

// You would integrate with an actual train API here
// For demonstration, we'll create a mock response with sample data

// For a real integration, you would use something like:
// $apiKey = 'your_api_key';
// $apiUrl = 'https://railwayapi.com/api/v2/between/source/' . urlencode($fromStation) . '/dest/' . urlencode($toStation) . '/date/' . urlencode($travelDate) . '/apikey/' . $apiKey;
// $response = file_get_contents($apiUrl);
// $trainData = json_decode($response, true);

// Create mock data for demonstration
$mockTrains = [
    [
        'train_number' => '12345',
        'train_name' => 'Rajdhani Express',
        'from_station' => $fromStation,
        'to_station' => $toStation,
        'departure_time' => '06:00',
        'arrival_time' => '14:30',
        'duration' => '8h 30m',
        'class_available' => ['SL', '3A', '2A', '1A'],
        'price' => [
            'SL' => 450,
            '3A' => 1200,
            '2A' => 2100,
            '1A' => 3600
        ],
        'availability' => 'Available',
        'date' => $travelDate
    ],
    [
        'train_number' => '12856',
        'train_name' => 'Shatabdi Express',
        'from_station' => $fromStation,
        'to_station' => $toStation,
        'departure_time' => '10:15',
        'arrival_time' => '16:45',
        'duration' => '6h 30m',
        'class_available' => ['CC', '2A', '1A'],
        'price' => [
            'CC' => 850,
            '2A' => 1800,
            '1A' => 3200
        ],
        'availability' => 'Few Seats Left',
        'date' => $travelDate
    ],
    [
        'train_number' => '18637',
        'train_name' => 'Mysore Express',
        'from_station' => $fromStation,
        'to_station' => $toStation,
        'departure_time' => '23:55',
        'arrival_time' => '08:30',
        'duration' => '8h 35m',
        'class_available' => ['SL', '3A', '2A'],
        'price' => [
            'SL' => 380,
            '3A' => 990,
            '2A' => 1770
        ],
        'availability' => 'Waiting List',
        'date' => $travelDate
    ]
];

// Filter trains by selected class if specified
if (!empty($travelClass)) {
    $filteredTrains = array_filter($mockTrains, function($train) use ($travelClass) {
        return in_array($travelClass, $train['class_available']);
    });
    $mockTrains = array_values($filteredTrains);
}

// Log the search query (optional)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$searchQuery = "INSERT INTO search_history (user_id, search_type, from_location, to_location, travel_date, created_at) 
                VALUES ($userId, 'train', '$fromStation', '$toStation', '$travelDate', NOW())";
// Uncomment when you have the search_history table created
// mysqli_query($conn, $searchQuery);

// Return the search results
echo json_encode([
    'status' => 'success',
    'message' => 'Train search completed successfully',
    'trains' => $mockTrains,
    'count' => count($mockTrains)
]); 
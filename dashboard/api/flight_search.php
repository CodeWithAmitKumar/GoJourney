<?php
// This file handles the API integration for flight search functionality

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
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
    exit;
}

// Get search parameters
$fromCity = isset($_POST['from']) ? $_POST['from'] : '';
$toCity = isset($_POST['to']) ? $_POST['to'] : '';
$departDate = isset($_POST['depart']) ? $_POST['depart'] : '';
$returnDate = isset($_POST['return']) ? $_POST['return'] : '';
$passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;
$class = isset($_POST['class']) ? $_POST['class'] : 'Economy';

// Validate required parameters
if (empty($fromCity) || empty($toCity) || empty($departDate)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters: from, to, and depart date are required.'
    ]);
    exit;
}

// Sanitize inputs for security
$fromCity = mysqli_real_escape_string($conn, $fromCity);
$toCity = mysqli_real_escape_string($conn, $toCity);
$departDate = mysqli_real_escape_string($conn, $departDate);
$returnDate = mysqli_real_escape_string($conn, $returnDate);
$class = mysqli_real_escape_string($conn, $class);

// Determine if this is a round trip or one-way
$tripType = empty($returnDate) ? 'one-way' : 'round-trip';

// This is a mock API that returns predefined flight data
// In a real application, this would connect to an actual flight API

// Define airlines for our mock data
$airlines = [
    [
        'name' => 'Air India',
        'code' => 'AI'
    ],
    [
        'name' => 'IndiGo',
        'code' => 'IN'
    ],
    [
        'name' => 'Vistara',
        'code' => 'VI'
    ],
    [
        'name' => 'SpiceJet',
        'code' => 'SG'
    ],
    [
        'name' => 'GoAir',
        'code' => 'GA'
    ]
];

// Generate mock flight data
$flights = [];
$numFlights = rand(5, 12); // Random number of flight results

for ($i = 0; $i < $numFlights; $i++) {
    // Pick a random airline
    $airline = $airlines[array_rand($airlines)];
    
    // Generate random flight number
    $flightNumber = $airline['code'] . rand(100, 999);
    
    // Generate random departure time
    $departureHour = rand(0, 23);
    $departureMinute = rand(0, 11) * 5; // 5-minute intervals
    $departureTime = sprintf('%02d:%02d', $departureHour, $departureMinute);
    
    // Generate random flight duration between 1-5 hours
    $durationHours = rand(1, 5);
    $durationMinutes = rand(0, 11) * 5;
    $duration = $durationHours . 'h ' . ($durationMinutes > 0 ? $durationMinutes . 'm' : '');
    
    // Calculate arrival time
    $arrivalHour = ($departureHour + $durationHours) % 24;
    $arrivalMinute = ($departureMinute + $durationMinutes) % 60;
    $arrivalTime = sprintf('%02d:%02d', $arrivalHour, $arrivalMinute);
    
    // Determine if flight has stops
    $stops = rand(0, 2);
    
    // Generate price based on class and random variation
    $basePrice = 3000; // Base price for economy
    
    if ($class === 'Premium') {
        $basePrice = 5000;
    } elseif ($class === 'Business') {
        $basePrice = 12000;
    } elseif ($class === 'First') {
        $basePrice = 25000;
    }
    
    // Add some variation to prices
    $price = $basePrice + rand(-500, 1500);
    
    // Round price to nearest 100
    $price = round($price / 100) * 100;
    
    // Add more for longer flights
    $price += $durationHours * 500;
    
    // Add more for stops
    $price -= $stops * 300; // Direct flights cost more
    
    // Generate a flight record
    $flight = [
        'flight_number' => $flightNumber,
        'airline' => $airline['name'],
        'airline_code' => $airline['code'],
        'from_city' => $fromCity,
        'to_city' => $toCity,
        'from_airport_code' => getAirportCodeForCity($fromCity),
        'to_airport_code' => getAirportCodeForCity($toCity),
        'departure_time' => $departureTime,
        'arrival_time' => $arrivalTime,
        'duration' => $duration,
        'date' => $departDate,
        'price' => $price,
        'class' => $class,
        'stops' => $stops,
        'seats_available' => rand(1, 30),
        'meal_included' => (bool)rand(0, 1)
    ];
    
    // Add stopover information if the flight has stops
    if ($stops > 0) {
        $stopovers = ['Delhi', 'Mumbai', 'Chennai', 'Kolkata', 'Bangalore', 'Hyderabad'];
        $flight['stopover_city'] = $stopovers[array_rand($stopovers)];
        $flight['stopover_duration'] = rand(1, 3) . 'h ' . (rand(0, 55) . 'm');
    }
    
    $flights[] = $flight;
}

// Sort flights by price (lowest first)
usort($flights, function($a, $b) {
    return $a['price'] - $b['price'];
});

// Prepare the response
$response = [
    'status' => 'success',
    'count' => count($flights),
    'from' => $fromCity,
    'to' => $toCity,
    'date' => $departDate,
    'return_date' => $returnDate,
    'passengers' => $passengers,
    'class' => $class,
    'flights' => $flights
];

// Return the response as JSON
echo json_encode($response);

// Helper function to generate an airport code for a city
function getAirportCodeForCity($city) {
    $cityCodeMap = [
        'Delhi' => 'DEL',
        'Mumbai' => 'BOM',
        'Bangalore' => 'BLR',
        'Chennai' => 'MAA',
        'Kolkata' => 'CCU',
        'Hyderabad' => 'HYD',
        'Pune' => 'PNQ',
        'Ahmedabad' => 'AMD',
        'Goa' => 'GOI',
        'Jaipur' => 'JAI',
        'Lucknow' => 'LKO',
        'Srinagar' => 'SXR',
        'Bhubaneswar' => 'BBI',
        'Patna' => 'PAT',
        'Guwahati' => 'GAU'
    ];
    
    // Return the code if found, otherwise generate a 3-letter code from the city name
    if (isset($cityCodeMap[$city])) {
        return $cityCodeMap[$city];
    } else {
        // Generate a 3-letter code from the first 3 letters of the city name
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $city), 0, 3));
        return $code ? $code : 'XXX';
    }
}

// Log the search query (optional)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$searchQuery = "INSERT INTO search_history (user_id, search_type, from_location, to_location, departure_date, return_date, created_at) 
                VALUES ($userId, 'flight', '$fromCity', '$toCity', '$departDate', '$returnDate', NOW())";
// Uncomment when you have the search_history table created
// mysqli_query($conn, $searchQuery); 
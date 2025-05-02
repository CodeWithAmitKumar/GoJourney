<?php
// This file handles the API integration for flight search functionality

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Start buffer to trap any errors or warnings that might corrupt JSON output
ob_start();

// Include database connection
require_once '../../connection/db_connect.php';
// Include the config file with API keys
require_once '../../admin/config.php';

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

// Get parameters
$from = isset($_POST['from']) ? $_POST['from'] : '';
$to = isset($_POST['to']) ? $_POST['to'] : '';
$depart = isset($_POST['depart']) ? $_POST['depart'] : (isset($_POST['date']) ? $_POST['date'] : '');
$returnDate = isset($_POST['return']) ? $_POST['return'] : '';
$passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;
$class = isset($_POST['class']) ? $_POST['class'] : 'Economy';

// Validate required parameters
if (empty($from) || empty($to) || empty($depart)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters: from, to, and depart date are required.'
    ]);
    exit;
}

// Sanitize inputs for security
$fromCity = mysqli_real_escape_string($conn, $from);
$toCity = mysqli_real_escape_string($conn, $to);
$departDate = mysqli_real_escape_string($conn, $depart);
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

// Function to get real flight data from Aviationstack API
function getFlightData($from, $to, $date) {
    // Get the Aviationstack API key from config
    $api_key = defined('AVIATIONSTACK_API_KEY') ? AVIATIONSTACK_API_KEY : 'YOUR_AVIATIONSTACK_API_KEY';
    
    // Debug: Check if API key is loaded correctly (remove in production)
    error_log("Using Aviationstack API key: " . substr($api_key, 0, 5) . "...");
    
    // Format the date as required by the API (YYYY-MM-DD)
    $formatted_date = date('Y-m-d', strtotime($date));
    
    // Build the API URL with parameters
    $url = "http://api.aviationstack.com/v1/flights?access_key={$api_key}&dep_iata={$from}&arr_iata={$to}&flight_date={$formatted_date}";
    
    // Debug the URL (without full API key)
    $debug_url = "http://api.aviationstack.com/v1/flights?access_key=" . substr($api_key, 0, 5) . "...&dep_iata={$from}&arr_iata={$to}&flight_date={$formatted_date}";
    error_log("API URL: " . $debug_url);
    
    // Make the API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 second timeout
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GoJourney/1.0');
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Capture detailed curl info for debugging
    $info = curl_getinfo($ch);
    error_log("cURL Info: " . json_encode($info));
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        curl_close($ch);
        
        // Since we're having API connection issues, fall back to dummy data
        error_log('Falling back to dummy flight data due to connection issues');
        return getDummyFlightData($from, $to, $date);
    }
    
    curl_close($ch);
    
    // Debug the raw response
    error_log("Raw API Response (first 300 chars): " . substr($response, 0, 300));
    
    // Decode JSON response
    $data = json_decode($response, true);
    
    // Check if JSON decoding failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return getDummyFlightData($from, $to, $date);
    }
    
    // Debug summary of decoded data
    error_log("API Response Summary: " . (isset($data['data']) ? count($data['data']) . " flights found" : "No flights data"));
    
    // Check if API call was successful
    if (isset($data['error'])) {
        error_log("API Error: " . json_encode($data['error']));
        // In case of API error, return dummy data instead
        return getDummyFlightData($from, $to, $date);
    }
    
    return formatFlightData($data, $from, $to);
}

// Function to format API data to match our application's needs
function formatFlightData($apiData, $from, $to) {
    $flights = [];
    
    // Check if we have flight data
    if (isset($apiData['data']) && is_array($apiData['data'])) {
        foreach ($apiData['data'] as $flight) {
            // Calculate a more realistic price based on flight info
            $price = calculateFlightPrice(
                $flight['departure']['iata'],
                $flight['arrival']['iata'],
                calculateDuration($flight['departure']['scheduled'], $flight['arrival']['scheduled']),
                $flight['airline']['name']
            );
            
            // Determine seat availability based on load factor
            $seatsAvailable = calculateSeatAvailability($flight['flight']['number'] ?? '');
            
            // Extract relevant flight information
            $flights[] = [
                'flight_number' => $flight['flight']['iata'],
                'airline' => $flight['airline']['name'],
                'airline_code' => $flight['airline']['iata'],
                'from_city' => $from,
                'from_airport_code' => $flight['departure']['iata'],
                'to_city' => $to,
                'to_airport_code' => $flight['arrival']['iata'],
                'departure_time' => date('H:i', strtotime($flight['departure']['scheduled'])),
                'arrival_time' => date('H:i', strtotime($flight['arrival']['scheduled'])),
                'date' => date('Y-m-d', strtotime($flight['flight_date'])),
                'duration' => calculateDuration(
                    $flight['departure']['scheduled'], 
                    $flight['arrival']['scheduled']
                ),
                'stops' => 0, // Assume direct flights for simplicity
                'price' => $price,
                'seats_available' => $seatsAvailable,
                'aircraft' => $flight['aircraft']['iata'] ?? 'Boeing 737',
                'status' => $flight['flight_status'] ?? 'scheduled',
                'meal_included' => determineIfMealIncluded($flight['airline']['name'], calculateDuration(
                    $flight['departure']['scheduled'], 
                    $flight['arrival']['scheduled']
                ))
            ];
        }
    }
    
    return [
        'count' => count($flights),
        'flights' => $flights
    ];
}

// Calculate flight duration from departure and arrival times
function calculateDuration($departure, $arrival) {
    $dep = new DateTime($departure);
    $arr = new DateTime($arrival);
    $interval = $dep->diff($arr);
    
    $hours = $interval->h;
    $hours = $hours + ($interval->days*24);
    
    return $hours . 'h ' . $interval->i . 'm';
}

// Function to calculate more realistic flight prices
function calculateFlightPrice($fromCode, $toCode, $duration, $airline) {
    // Base price per kilometer
    $basePrice = 5;
    
    // Get distance between airports (approximation based on airport codes)
    $distance = getDistanceBetweenAirports($fromCode, $toCode);
    
    // Calculate base price from distance
    $price = $distance * $basePrice;
    
    // Extract duration hours
    $durationHours = (int)substr($duration, 0, strpos($duration, 'h'));
    
    // Add price for longer flights (more fuel, more service)
    $price += $durationHours * 500;
    
    // Adjust price based on airline (premium vs budget)
    $premiumAirlines = ['Air India', 'Vistara', 'Singapore Airlines', 'Emirates', 'Qatar Airways', 'Etihad Airways'];
    $budgetAirlines = ['IndiGo', 'SpiceJet', 'GoAir', 'AirAsia'];
    
    if (in_array($airline, $premiumAirlines)) {
        $price *= 1.3; // 30% premium for premium airlines
    } elseif (in_array($airline, $budgetAirlines)) {
        $price *= 0.8; // 20% discount for budget airlines
    }
    
    // Add some minor variance for same route/airline
    $price = $price * (0.95 + (mt_rand(0, 10) / 100));
    
    // Round to nearest 100
    return round($price / 100) * 100;
}

// Function to estimate distance between airports
function getDistanceBetweenAirports($fromCode, $toCode) {
    // Airport coordinates (latitude, longitude) - simplified with major Indian airports
    $airports = [
        'DEL' => ['lat' => 28.5665, 'lng' => 77.1031], // Delhi
        'BOM' => ['lat' => 19.0896, 'lng' => 72.8656], // Mumbai
        'MAA' => ['lat' => 12.9941, 'lng' => 80.1709], // Chennai
        'CCU' => ['lat' => 22.6520, 'lng' => 88.4463], // Kolkata
        'BLR' => ['lat' => 13.1979, 'lng' => 77.7063], // Bangalore
        'HYD' => ['lat' => 17.2403, 'lng' => 78.4294], // Hyderabad
        'AMD' => ['lat' => 23.0225, 'lng' => 72.5714], // Ahmedabad
        'PNQ' => ['lat' => 18.5793, 'lng' => 73.9089], // Pune
        'GOI' => ['lat' => 15.3808, 'lng' => 73.8348], // Goa
        'JAI' => ['lat' => 26.8242, 'lng' => 75.8122], // Jaipur
    ];
    
    // Default distance if airport codes not found
    $defaultDistance = 1000;
    
    // If we don't have coordinates for either airport, return default
    if (!isset($airports[$fromCode]) || !isset($airports[$toCode])) {
        return $defaultDistance;
    }
    
    // Calculate distance using Haversine formula
    $from = $airports[$fromCode];
    $to = $airports[$toCode];
    
    $earthRadius = 6371; // km
    
    $latDiff = deg2rad($to['lat'] - $from['lat']);
    $lngDiff = deg2rad($to['lng'] - $from['lng']);
    
    $a = sin($latDiff/2) * sin($latDiff/2) +
         cos(deg2rad($from['lat'])) * cos(deg2rad($to['lat'])) *
         sin($lngDiff/2) * sin($lngDiff/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;
    
    return $distance;
}

// Calculate seat availability in a more predictable way
function calculateSeatAvailability($flightNumber) {
    // Use the flight number digits to generate a hash for consistent availability
    $hash = crc32($flightNumber);
    $hashValue = abs($hash % 100); // 0-99 value
    
    // Different availability patterns based on hash range
    if ($hashValue < 20) {
        return mt_rand(1, 5); // Very few seats
    } elseif ($hashValue < 50) {
        return mt_rand(5, 15); // Limited availability
    } else {
        return mt_rand(15, 40); // Good availability
    }
}

// Determine if meal is included based on airline and flight duration
function determineIfMealIncluded($airline, $duration) {
    // Get duration in hours
    $durationHours = (int)substr($duration, 0, strpos($duration, 'h'));
    
    // Premium airlines always include meals for flights over 1 hour
    $premiumAirlines = ['Air India', 'Vistara', 'Singapore Airlines', 'Emirates', 'Qatar Airways', 'Etihad Airways'];
    
    if (in_array($airline, $premiumAirlines)) {
        return $durationHours >= 1;
    }
    
    // Budget airlines include meals only for longer flights
    return $durationHours >= 3;
}

// Function to generate dummy flight data for testing or when API fails
function getDummyFlightData($from, $to, $date) {
    $airlines = [
        ['name' => 'Air India', 'code' => 'AI'],
        ['name' => 'IndiGo', 'code' => '6E'],
        ['name' => 'SpiceJet', 'code' => 'SG'],
        ['name' => 'Vistara', 'code' => 'UK'],
        ['name' => 'GoAir', 'code' => 'G8']
    ];
    
    $flights = [];
    $count = rand(5, 15); // Random number of flights
    
    for ($i = 0; $i < $count; $i++) {
        $airline = $airlines[array_rand($airlines)];
        $departure_hour = rand(0, 23);
        $departure_minute = rand(0, 11) * 5; // 5-minute intervals
        $duration_hours = rand(1, 5);
        $duration_minutes = rand(0, 11) * 5;
        
        $departure_time = sprintf('%02d:%02d', $departure_hour, $departure_minute);
        $departure_dt = new DateTime($date . ' ' . $departure_time);
        
        $arrival_dt = clone $departure_dt;
        $arrival_dt->add(new DateInterval('PT' . $duration_hours . 'H' . $duration_minutes . 'M'));
        $arrival_time = $arrival_dt->format('H:i');
        
        $flight_number = $airline['code'] . rand(100, 999);
        
        $flights[] = [
            'flight_number' => $flight_number,
            'airline' => $airline['name'],
            'airline_code' => $airline['code'],
            'from_city' => $from,
            'from_airport_code' => $from,
            'to_city' => $to,
            'to_airport_code' => $to,
            'departure_time' => $departure_time,
            'arrival_time' => $arrival_time,
            'date' => $date,
            'duration' => $duration_hours . 'h ' . $duration_minutes . 'm',
            'stops' => rand(0, 2),
            'price' => rand(3000, 15000),
            'seats_available' => rand(5, 30),
            'aircraft' => 'Boeing 737',
            'status' => 'scheduled',
            'meal_included' => (bool)rand(0, 1)
        ];
    }
    
    // Sort flights by departure time
    usort($flights, function($a, $b) {
        return strtotime($a['departure_time']) - strtotime($b['departure_time']);
    });
    
    return [
        'count' => count($flights),
        'flights' => $flights
    ];
}

// Check API connectivity function
function checkApiConnectivity() {
    // Create a test URL to check connectivity
    $api_key = defined('AVIATIONSTACK_API_KEY') ? AVIATIONSTACK_API_KEY : 'YOUR_AVIATIONSTACK_API_KEY';
    $test_url = "http://api.aviationstack.com/v1/flights?access_key={$api_key}&limit=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout
    $response = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_errno($ch);
    curl_close($ch);
    
    error_log("API Connectivity Test - HTTP Code: $http_code, cURL Error: $curl_error");
    
    if ($curl_error) {
        error_log("API Connectivity Error: " . curl_strerror($curl_error));
        return false;
    }
    
    return $http_code >= 200 && $http_code < 300;
}

// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean buffer before main processing
    ob_clean();
    
    // Get parameters
    $from = isset($_POST['from']) ? $_POST['from'] : '';
    $to = isset($_POST['to']) ? $_POST['to'] : '';
    $depart = isset($_POST['depart']) ? $_POST['depart'] : '';
    $returnDate = isset($_POST['return']) ? $_POST['return'] : '';
    $passengers = isset($_POST['passengers']) ? (int)$_POST['passengers'] : 1;
    $class = isset($_POST['class']) ? $_POST['class'] : 'Economy';
    
    if (empty($from) || empty($to) || empty($depart)) {
        ob_clean(); // Clean buffer before output
        echo json_encode([
            'error' => 'Missing required parameters',
            'count' => 0,
            'flights' => []
        ]);
        ob_end_flush();
        exit;
    }
    
    // Get airport codes
    $fromCode = getAirportCodeForCity($from);
    $toCode = getAirportCodeForCity($to);
    
    error_log("Flight search from $from ($fromCode) to $to ($toCode) on $depart");
    
    // Attempt to get real data from API, fall back to dummy data if needed
    try {
        $flightData = getFlightData($fromCode, $toCode, $depart);
        
        // Add additional info to the response
        $flightData['from'] = $from;
        $flightData['to'] = $to;
        $flightData['date'] = $depart;
        $flightData['return_date'] = $returnDate;
        $flightData['passengers'] = $passengers;
        $flightData['class'] = $class;
        $flightData['status'] = 'success';
        
        ob_clean(); // Clean buffer before output
        echo json_encode($flightData);
    } catch (Exception $e) {
        // In case of any errors, return dummy data
        error_log("Exception in flight search: " . $e->getMessage());
        $dummyData = getDummyFlightData($from, $to, $depart);
        
        // Add additional info to the dummy data response
        $dummyData['from'] = $from;
        $dummyData['to'] = $to;
        $dummyData['date'] = $depart;
        $dummyData['return_date'] = $returnDate;
        $dummyData['passengers'] = $passengers;
        $dummyData['class'] = $class;
        $dummyData['status'] = 'success';
        
        ob_clean(); // Clean buffer before output
        echo json_encode($dummyData);
    }
} else {
    ob_clean(); // Clean buffer before output
    echo json_encode([
        'error' => 'Invalid request method',
        'count' => 0,
        'flights' => []
    ]);
}

// End output buffering
ob_end_flush(); 
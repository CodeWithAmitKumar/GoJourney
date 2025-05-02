<?php
// Set appropriate headers for JSON response
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
    exit;
}

// Get search parameters
$destination = isset($_POST['destination']) ? $_POST['destination'] : '';
$checkIn = isset($_POST['checkin']) ? $_POST['checkin'] : '';
$checkOut = isset($_POST['checkout']) ? $_POST['checkout'] : '';
$adults = isset($_POST['adults']) ? (int)$_POST['adults'] : 2;
$children = isset($_POST['children']) ? (int)$_POST['children'] : 0;
$rooms = isset($_POST['rooms']) ? (int)$_POST['rooms'] : 1;

// Validate required parameters
if (empty($destination) || empty($checkIn) || empty($checkOut)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required parameters: destination, checkin, and checkout are required.'
    ]);
    exit;
}

// This is a mock API that returns predefined hotel data
// In a real application, this would connect to an actual hotel API

// Define hotel names for our mock data
$hotelNames = [
    'Grand Plaza Hotel',
    'Royal Orchid Resort',
    'Sunset View Inn',
    'The Luxury Collection',
    'Mountain Retreat Hotel',
    'Paradise Beach Resort',
    'City Lights Hotel',
    'Heritage Palace Hotel',
    'Ocean Breeze Resort',
    'Urban Gateway Hotel',
    'Lakeside View Resort',
    'Emerald Valley Inn',
    'Majestic Heights Hotel',
    'Golden Sands Resort',
    'Blue Horizon Hotel'
];

// Define hotel amenities for our mock data
$allAmenities = [
    'Free WiFi',
    'Swimming Pool',
    'Restaurant',
    'Gym',
    'Spa',
    'Free Breakfast',
    'Room Service',
    'Air Conditioning',
    'Bar/Lounge',
    'Airport Shuttle',
    'Parking',
    'Conference Room',
    'Pet Friendly',
    'Kids Club',
    'Laundry Service'
];

// Define room types for our mock data
$roomTypes = [
    [
        'name' => 'Standard Room',
        'size' => '25 m²',
        'capacity' => '2 adults'
    ],
    [
        'name' => 'Deluxe Room',
        'size' => '30 m²',
        'capacity' => '2 adults, 1 child'
    ],
    [
        'name' => 'Executive Suite',
        'size' => '45 m²',
        'capacity' => '2 adults, 2 children'
    ],
    [
        'name' => 'Family Room',
        'size' => '40 m²',
        'capacity' => '3 adults, 2 children'
    ],
    [
        'name' => 'Presidential Suite',
        'size' => '75 m²',
        'capacity' => '4 adults'
    ]
];

// Generate mock hotel data
$hotels = [];
$numHotels = rand(8, 15); // Random number of hotel results

for ($i = 0; $i < $numHotels; $i++) {
    // Pick a random hotel name
    $hotelName = $hotelNames[array_rand($hotelNames)];
    
    // Generate a random star rating (3-5 stars)
    $starRating = rand(3, 5);
    
    // Generate random hotel location
    $locations = ['City Center', 'Downtown', 'Business District', 'Tourist Area', 'Near Airport', 'Beachfront', 'Historic District'];
    $location = $destination . ' - ' . $locations[array_rand($locations)];
    
    // Generate a random distance from center
    $distance = rand(1, 15) . '.' . rand(1, 9) . ' km';
    
    // Generate random amenities (4-8 amenities)
    $numAmenities = rand(4, 8);
    $amenities = [];
    $amenitiesKeys = array_rand($allAmenities, $numAmenities);
    if (!is_array($amenitiesKeys)) {
        $amenitiesKeys = [$amenitiesKeys];
    }
    foreach ($amenitiesKeys as $key) {
        $amenities[] = $allAmenities[$key];
    }
    
    // Generate random room options (2-5 room types)
    $numRoomTypes = rand(2, 5);
    $roomOptions = [];
    $roomTypesKeys = array_rand($roomTypes, $numRoomTypes);
    if (!is_array($roomTypesKeys)) {
        $roomTypesKeys = [$roomTypesKeys];
    }
    
    foreach ($roomTypesKeys as $key) {
        $basePrice = 0;
        
        // Set base price based on room type
        switch ($roomTypes[$key]['name']) {
            case 'Standard Room':
                $basePrice = rand(1500, 2500);
                break;
            case 'Deluxe Room':
                $basePrice = rand(2500, 4000);
                break;
            case 'Executive Suite':
                $basePrice = rand(5000, 7500);
                break;
            case 'Family Room':
                $basePrice = rand(4000, 6000);
                break;
            case 'Presidential Suite':
                $basePrice = rand(10000, 15000);
                break;
            default:
                $basePrice = rand(2000, 3000);
        }
        
        // Adjust price based on star rating
        $basePrice += ($starRating - 3) * 1000;
        
        $roomOption = $roomTypes[$key];
        $roomOption['price'] = $basePrice;
        $roomOption['id'] = 'room_' . ($i + 1) . '_' . ($key + 1);
        $roomOption['breakfast_included'] = (bool)rand(0, 1);
        
        $roomOptions[] = $roomOption;
    }
    
    // Sort room options by price
    usort($roomOptions, function($a, $b) {
        return $a['price'] - $b['price'];
    });
    
    // Generate base price (lowest room price)
    $pricePerNight = $roomOptions[0]['price'];
    
    // Generate a random review score (7.0-9.9)
    $reviewScore = rand(70, 99) / 10;
    
    // Generate a random hotel description
    $descriptions = [
        "Located in the heart of $destination, this $starRating-star hotel offers modern comforts with traditional charm.",
        "Experience luxury and comfort at this $starRating-star property, perfectly situated in $destination for both business and leisure travelers.",
        "This elegant $starRating-star hotel boasts stunning views of $destination and provides exceptional service for a memorable stay.",
        "A premier $starRating-star accommodation in $destination offering top-notch amenities and easy access to local attractions.",
        "Nestled in $destination, this $starRating-star hotel combines comfort, convenience, and world-class service."
    ];
    $description = $descriptions[array_rand($descriptions)];
    
    // Determine if free cancellation is available
    $freeCancellation = (bool)rand(0, 1);
    
    // Determine if there's a discount
    $hasDiscount = rand(0, 10) > 7; // 30% chance of having a discount
    $discount = $hasDiscount ? rand(10, 30) : 0;
    
    // Generate check-in and check-out times
    $checkInTime = ['12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM'][array_rand(['12:00 PM', '1:00 PM', '2:00 PM', '3:00 PM'])];
    $checkOutTime = ['10:00 AM', '11:00 AM', '12:00 PM'][array_rand(['10:00 AM', '11:00 AM', '12:00 PM'])];
    
    // Generate hotel image path
    $imageNum = rand(1, 5); // Assume we have 5 default hotel images
    $image = "../images/hotels/hotel$imageNum.jpg";
    
    // Create a hotel record
    $hotel = [
        'id' => 'hotel_' . ($i + 1),
        'name' => $hotelName,
        'star_rating' => $starRating,
        'location' => $location,
        'distance_from_center' => $distance,
        'amenities' => $amenities,
        'description' => $description,
        'price_per_night' => $pricePerNight,
        'review_score' => $reviewScore,
        'free_cancellation' => $freeCancellation,
        'image' => $image,
        'checkin_date' => $checkIn,
        'checkout_date' => $checkOut,
        'checkin_time' => $checkInTime,
        'checkout_time' => $checkOutTime,
        'room_options' => $roomOptions
    ];
    
    // Add discount if applicable
    if ($hasDiscount) {
        $hotel['discount'] = $discount;
    }
    
    $hotels[] = $hotel;
}

// Sort hotels by price (lowest first)
usort($hotels, function($a, $b) {
    return $a['price_per_night'] - $b['price_per_night'];
});

// Prepare the response
$response = [
    'status' => 'success',
    'count' => count($hotels),
    'destination' => $destination,
    'checkin' => $checkIn,
    'checkout' => $checkOut,
    'adults' => $adults,
    'children' => $children,
    'rooms' => $rooms,
    'hotels' => $hotels
];

// Return the response as JSON
echo json_encode($response);
?> 
<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../connection/db_connect.php';
// Include the config file with API keys
require_once '../../admin/config.php';

// Get test parameters
$from = isset($_GET['from']) ? $_GET['from'] : 'Delhi';
$to = isset($_GET['to']) ? $_GET['to'] : 'Mumbai';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$class = isset($_GET['class']) ? $_GET['class'] : 'SL';

// HTML output
echo '<!DOCTYPE html>
<html>
<head>
    <title>Train Search Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        form { margin-bottom: 20px; }
        label { display: inline-block; width: 100px; }
        input, select { padding: 5px; margin: 5px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Train Search Test</h1>
    
    <div class="section">
        <h2>Test Parameters</h2>
        <form method="GET">
            <div>
                <label for="from">From:</label>
                <input type="text" id="from" name="from" value="' . htmlspecialchars($from) . '">
            </div>
            <div>
                <label for="to">To:</label>
                <input type="text" id="to" name="to" value="' . htmlspecialchars($to) . '">
            </div>
            <div>
                <label for="date">Date:</label>
                <input type="date" id="date" name="date" value="' . htmlspecialchars($date) . '">
            </div>
            <div>
                <label for="class">Class:</label>
                <select id="class" name="class">
                    <option value="SL" ' . ($class === 'SL' ? 'selected' : '') . '>Sleeper (SL)</option>
                    <option value="AC3" ' . ($class === 'AC3' ? 'selected' : '') . '>AC 3 Tier (3A)</option>
                    <option value="AC2" ' . ($class === 'AC2' ? 'selected' : '') . '>AC 2 Tier (2A)</option>
                    <option value="AC1" ' . ($class === 'AC1' ? 'selected' : '') . '>AC 1st Class (1A)</option>
                </select>
            </div>
            <button type="submit">Test Search</button>
        </form>
    </div>';

// Step 1: Check if Indian Rail API key is defined
echo '<div class="section">
    <h2>API Key Check</h2>';
if (defined('INDIAN_RAIL_API_KEY')) {
    $api_key = INDIAN_RAIL_API_KEY;
    $masked_key = substr($api_key, 0, 5) . '...' . substr($api_key, -4);
    echo '<p class="success">✓ Indian Rail API Key is defined: ' . $masked_key . '</p>';
} else {
    echo '<p class="error">✗ Indian Rail API Key is NOT defined. Please check your config.php file.</p>';
    $api_key = 'YOUR_API_KEY';
}
echo '</div>';

// Step 2: Process station names to codes
echo '<div class="section">
    <h2>Station Code Processing</h2>';

// Common Indian stations mapping
$stationCodes = [
    'delhi' => 'NDLS',
    'new delhi' => 'NDLS',
    'mumbai' => 'BCT',
    'mumbai central' => 'BCT',
    'chennai' => 'MAS',
    'chennai central' => 'MAS',
    'kolkata' => 'HWH',
    'howrah' => 'HWH',
    'bangalore' => 'SBC',
    'bengaluru' => 'SBC',
    'hyderabad' => 'SC',
    'secunderabad' => 'SC',
    'ahmedabad' => 'ADI',
    'pune' => 'PUNE',
    'jaipur' => 'JP',
    'lucknow' => 'LKO',
    'patna' => 'PNBE',
    'guwahati' => 'GHY',
    'chandigarh' => 'CDG',
    'bhubaneswar' => 'BBS',
    'trivandrum' => 'TVC',
    'kochi' => 'ERS',
    'goa' => 'MAO',
    'madgaon' => 'MAO',
    'nagpur' => 'NGP',
    'varanasi' => 'BSB',
    'kanpur' => 'CNB',
    'vijayawada' => 'BZA',
    'bhopal' => 'BPL',
    'jammu' => 'JAT'
];

// Process from station
$fromCode = preg_match('/\(([A-Z]+)\)/', $from, $matches) ? $matches[1] : strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $from), 0, 3));
$lowercaseFrom = strtolower(trim(preg_replace('/\([A-Z]+\)/', '', $from)));

if (array_key_exists($lowercaseFrom, $stationCodes)) {
    $fromCode = $stationCodes[$lowercaseFrom];
    echo '<p class="success">✓ Found code for ' . htmlspecialchars($from) . ': ' . $fromCode . ' (matched in station codes)</p>';
} else {
    echo '<p>Generated code for ' . htmlspecialchars($from) . ': ' . $fromCode . ' (generated from name)</p>';
}

// Process to station
$toCode = preg_match('/\(([A-Z]+)\)/', $to, $matches) ? $matches[1] : strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $to), 0, 3));
$lowercaseTo = strtolower(trim(preg_replace('/\([A-Z]+\)/', '', $to)));

if (array_key_exists($lowercaseTo, $stationCodes)) {
    $toCode = $stationCodes[$lowercaseTo];
    echo '<p class="success">✓ Found code for ' . htmlspecialchars($to) . ': ' . $toCode . ' (matched in station codes)</p>';
} else {
    echo '<p>Generated code for ' . htmlspecialchars($to) . ': ' . $toCode . ' (generated from name)</p>';
}

echo '</div>';

// Step 3: Build API URL
$formatted_date = date('Y-m-d', strtotime($date));
$url = "https://indianrailapi.com/api/v2/TrainBetweenStation/apikey/{$api_key}/From/{$fromCode}/To/{$toCode}/Date/{$formatted_date}";
$masked_url = str_replace($api_key, substr($api_key, 0, 5) . '...', $url);

echo '<div class="section">
    <h2>API Request</h2>
    <p>API URL: <code>' . htmlspecialchars($masked_url) . '</code></p>';

// Step 4: Make the API request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'GoJourney-Diagnostic/1.0');

// Execute the request
$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_errno($ch) ? curl_error($ch) : null;

echo '<p>HTTP Status: ' . $info['http_code'] . '</p>';
if ($error) {
    echo '<p class="error">cURL Error: ' . $error . '</p>';
}

curl_close($ch);

// Step 5: Process API response
echo '<h3>API Response</h3>';
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo '<p class="error">JSON Parsing Error: ' . json_last_error_msg() . '</p>';
    echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '...</pre>';
} else {
    echo '<p>Response Status: ' . (isset($data['ResponseCode']) ? $data['ResponseCode'] . ' - ' . $data['Message'] : 'Unknown') . '</p>';
    
    if (isset($data['Trains']) && is_array($data['Trains'])) {
        echo '<p class="success">✓ Found ' . count($data['Trains']) . ' trains</p>';
        
        // Display trains in a readable format
        echo '<h3>Trains Found</h3>';
        echo '<pre>';
        foreach ($data['Trains'] as $index => $train) {
            echo "Train " . ($index + 1) . ":\n";
            echo "  Number: " . ($train['TrainNo'] ?? 'N/A') . "\n";
            echo "  Name: " . ($train['TrainName'] ?? 'N/A') . "\n";
            echo "  Departure: " . ($train['DepartureTime'] ?? 'N/A') . "\n";
            echo "  Arrival: " . ($train['ArrivalTime'] ?? 'N/A') . "\n";
            echo "  Duration: " . ($train['Duration'] ?? 'N/A') . "\n";
            echo "  Classes: " . ($train['ClassesAvailable'] ?? 'N/A') . "\n\n";
        }
        echo '</pre>';
    } else {
        echo '<p class="error">✗ No trains found in the response</p>';
        echo '<pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
    }
}

echo '</div>';

// Step 6: Test calling our train_search.php directly
echo '<div class="section">
    <h2>Test Local API Endpoint</h2>
    <p>Testing our train_search.php with the same parameters:</p>';

// Create form data
$formData = [
    'from' => $from,
    'to' => $to,
    'depart' => $date,
    'date' => $date,  // Send both for compatibility
    'class' => $class,
    'passengers' => 1
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/train_search.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute the request
$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_errno($ch) ? curl_error($ch) : null;

echo '<p>HTTP Status: ' . $info['http_code'] . '</p>';
if ($error) {
    echo '<p class="error">cURL Error: ' . $error . '</p>';
}

curl_close($ch);

// Process response from our API
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo '<p class="error">JSON Parsing Error: ' . json_last_error_msg() . '</p>';
    echo '<pre>' . htmlspecialchars(substr($response, 0, 1000)) . '...</pre>';
} else {
    if (isset($data['trains']) && is_array($data['trains'])) {
        echo '<p class="success">✓ Our API returned ' . count($data['trains']) . ' trains</p>';
        
        // Display first train as example
        if (count($data['trains']) > 0) {
            echo '<h3>Sample Train Data</h3>';
            echo '<pre>' . htmlspecialchars(json_encode($data['trains'][0], JSON_PRETTY_PRINT)) . '</pre>';
        }
    } else {
        echo '<p class="error">✗ No trains found in our API response</p>';
        echo '<pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>';
    }
}

echo '</div>';

echo '</body>
</html>';
?> 
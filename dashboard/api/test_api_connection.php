<?php
// This is a diagnostic tool to test the API connections

// Set error reporting to show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../../connection/db_connect.php';
require_once '../../admin/config.php';

// HTML header with basic styling
echo '<!DOCTYPE html>
<html>
<head>
    <title>API Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .code { font-family: monospace; background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>API Connection Test</h1>';

// Function to test if a constant is defined
function checkConstant($name) {
    if (defined($name)) {
        $value = constant($name);
        $masked = substr($value, 0, 5) . '...' . substr($value, -3);
        echo "<p class='success'>✓ {$name} is defined ({$masked})</p>";
        return true;
    } else {
        echo "<p class='error'>✗ {$name} is not defined</p>";
        return false;
    }
}

// Function to test a URL connection
function testUrlConnection($url, $description) {
    echo "<div class='section'>";
    echo "<h2>{$description}</h2>";
    echo "<p>Testing connection to: <span class='code'>" . htmlspecialchars($url) . "</span></p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GoJourney-Test/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $info = curl_getinfo($ch);
    
    if ($errno) {
        echo "<p class='error'>cURL Error ({$errno}): {$error}</p>";
        echo "<p>Details:</p>";
        echo "<pre class='code'>" . print_r($info, true) . "</pre>";
    } else {
        echo "<p class='success'>Connection successful!</p>";
        echo "<p>HTTP Code: {$httpCode}</p>";
        
        // Try to parse response as JSON
        $jsonData = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>Response parsed as valid JSON</p>";
            echo "<p>Response Preview:</p>";
            echo "<pre class='code'>" . htmlspecialchars(substr(json_encode($jsonData, JSON_PRETTY_PRINT), 0, 1000)) . "...</pre>";
        } else {
            echo "<p class='warning'>Response is not valid JSON: " . json_last_error_msg() . "</p>";
            echo "<p>Raw Response Preview:</p>";
            echo "<pre class='code'>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
        }
    }
    
    curl_close($ch);
    echo "</div>";
}

// Test Flight API (Aviationstack)
echo "<div class='section'>";
echo "<h2>Flight API (Aviationstack)</h2>";
$aviationstack_api_key_exists = checkConstant('AVIATIONSTACK_API_KEY');

if ($aviationstack_api_key_exists) {
    $api_key = AVIATIONSTACK_API_KEY;
    $test_url = "http://api.aviationstack.com/v1/flights?access_key={$api_key}&limit=1";
    testUrlConnection($test_url, "Testing Aviationstack API");
    
    // Test with specific parameters
    $from = 'DEL';
    $to = 'BOM';
    $date = date('Y-m-d');
    $url = "http://api.aviationstack.com/v1/flights?access_key={$api_key}&dep_iata={$from}&arr_iata={$to}&flight_date={$date}";
    testUrlConnection($url, "Testing Flight Search (Delhi to Mumbai)");
} else {
    echo "<p>Cannot test Aviationstack API without API key</p>";
}
echo "</div>";

// Test Train API (Indian Rail API)
echo "<div class='section'>";
echo "<h2>Train API (Indian Rail API)</h2>";
$indian_rail_api_key_exists = checkConstant('INDIAN_RAIL_API_KEY');

if ($indian_rail_api_key_exists) {
    $api_key = INDIAN_RAIL_API_KEY;
    $from = 'NDLS';
    $to = 'BCT';
    $date = date('Y-m-d');
    $url = "https://indianrailapi.com/api/v2/TrainBetweenStation/apikey/{$api_key}/From/{$from}/To/{$to}/Date/{$date}";
    testUrlConnection($url, "Testing Train Search (New Delhi to Mumbai Central)");
} else {
    echo "<p>Cannot test Indian Rail API without API key</p>";
}
echo "</div>";

// Fallback to dummy data test
echo "<div class='section'>";
echo "<h2>Fallback Dummy Data</h2>";
echo "<p>Testing that the fallback data generation works as expected</p>";

// Link to actual search endpoints with test parameters
echo "<p>Test search endpoints directly:</p>";
echo "<ul>";
echo "<li><a href='flight_search.php?from=Delhi&to=Mumbai&depart=" . date('Y-m-d') . "' target='_blank'>Test Flight Search</a> (note: requires POST but useful for seeing error response)</li>";
echo "<li><a href='train_search.php?from=Delhi&to=Mumbai&depart=" . date('Y-m-d') . "' target='_blank'>Test Train Search</a> (note: requires POST but useful for seeing error response)</li>";
echo "</ul>";

echo "</div>";

// Configuration instructions
echo "<div class='section'>";
echo "<h2>Configuration Instructions</h2>";
echo "<p>To properly set up the API keys, add the following constants in <code>admin/config.php</code>:</p>";
echo "<pre class='code'>
// Aviationstack API for flight data (free tier available)
define('AVIATIONSTACK_API_KEY', 'your_aviationstack_api_key'); 

// Indian Rail API for train data
define('INDIAN_RAIL_API_KEY', 'your_indian_rail_api_key');
</pre>";
echo "</div>";

echo "</body></html>";
?> 
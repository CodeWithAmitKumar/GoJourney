<?php
// This file provides client-side debugging for the flight and train search APIs

// Start output buffering to capture any PHP errors/warnings
ob_start();

// Set header to return JSON
header('Content-Type: application/json');

// Get request type, defaulting to flight
$type = isset($_GET['type']) ? $_GET['type'] : 'flight';

// Create a response array
$response = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_type' => $type,
    'debug_info' => [],
    'search_params' => []
];

// Capture search parameters
$params = [];
foreach ($_GET as $key => $value) {
    if ($key !== 'type') {
        $params[$key] = $value;
    }
}
$response['search_params'] = $params;

// Verify database connection
try {
    require_once '../../connection/db_connect.php';
    if ($conn) {
        $response['debug_info']['db_connection'] = 'success';
    } else {
        $response['debug_info']['db_connection'] = 'failed';
    }
} catch (Exception $e) {
    $response['debug_info']['db_connection'] = 'error: ' . $e->getMessage();
}

// Check config file and API keys
try {
    if (file_exists('../../admin/config.php')) {
        require_once '../../admin/config.php';
        $response['debug_info']['config_file'] = 'exists';
        
        if ($type === 'flight') {
            $response['debug_info']['api_key_defined'] = defined('AVIATIONSTACK_API_KEY');
            if (defined('AVIATIONSTACK_API_KEY')) {
                $response['debug_info']['api_key_preview'] = substr(AVIATIONSTACK_API_KEY, 0, 5) . '...';
            }
        } else if ($type === 'train') {
            $response['debug_info']['api_key_defined'] = defined('INDIAN_RAIL_API_KEY');
            if (defined('INDIAN_RAIL_API_KEY')) {
                $response['debug_info']['api_key_preview'] = substr(INDIAN_RAIL_API_KEY, 0, 5) . '...';
            }
        }
    } else {
        $response['debug_info']['config_file'] = 'missing';
    }
} catch (Exception $e) {
    $response['debug_info']['config_file'] = 'error: ' . $e->getMessage();
}

// Check for PHP info
$response['debug_info']['php_version'] = phpversion();
$response['debug_info']['curl_available'] = function_exists('curl_init');
$response['debug_info']['json_available'] = function_exists('json_encode');

// Check if the correct search API file exists
$api_file = ($type === 'flight') ? 'flight_search.php' : 'train_search.php';
$response['debug_info']['api_file_exists'] = file_exists($api_file);

// Test a simple curl request to a reliable external API
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $test_response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_errno($ch) ? curl_error($ch) : null;
    curl_close($ch);
    
    $response['debug_info']['test_curl'] = [
        'success' => $error === null && $info['http_code'] === 200,
        'http_code' => $info['http_code'],
        'error' => $error
    ];
}

// Clean any output and return the debug info
ob_clean();
echo json_encode($response, JSON_PRETTY_PRINT);
ob_end_flush();
exit;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search API Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .container { display: flex; gap: 20px; flex-wrap: wrap; }
        .panel { 
            flex: 1; 
            min-width: 400px; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        form { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { 
            width: 100%; 
            padding: 8px; 
            margin-bottom: 15px; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        button { 
            background-color: #4CAF50; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        button:hover { background-color: #45a049; }
        .results { 
            margin-top: 20px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            padding: 15px;
            max-height: 500px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }
        .json { font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Search API Debug Tool</h1>
    <p>Use this tool to directly test the flight and train search APIs without going through the main UI.</p>
    
    <div class="container">
        <!-- Flight Search Test -->
        <div class="panel">
            <h2>Flight Search Test</h2>
            <form id="flightForm" method="post">
                <input type="hidden" name="api_type" value="flight">
                
                <label for="flight_from">From City:</label>
                <input type="text" id="flight_from" name="from" value="Delhi" required>
                
                <label for="flight_to">To City:</label>
                <input type="text" id="flight_to" name="to" value="Mumbai" required>
                
                <label for="flight_depart">Departure Date:</label>
                <input type="date" id="flight_depart" name="depart" value="<?php echo date('Y-m-d'); ?>" required>
                
                <label for="flight_class">Class:</label>
                <select id="flight_class" name="class">
                    <option value="Economy">Economy</option>
                    <option value="Premium">Premium Economy</option>
                    <option value="Business">Business</option>
                    <option value="First">First Class</option>
                </select>
                
                <label for="flight_passengers">Passengers:</label>
                <input type="number" id="flight_passengers" name="passengers" value="1" min="1" max="9">
                
                <button type="submit">Test Flight Search</button>
            </form>
            
            <div id="flightResults" class="results" style="display: none;">
                <h3>Search Results:</h3>
                <div id="flightJson" class="json"></div>
            </div>
        </div>
        
        <!-- Train Search Test -->
        <div class="panel">
            <h2>Train Search Test</h2>
            <form id="trainForm" method="post">
                <input type="hidden" name="api_type" value="train">
                
                <label for="train_from">From Station:</label>
                <input type="text" id="train_from" name="from" value="Delhi" required>
                
                <label for="train_to">To Station:</label>
                <input type="text" id="train_to" name="to" value="Mumbai" required>
                
                <label for="train_depart">Departure Date:</label>
                <input type="date" id="train_depart" name="depart" value="<?php echo date('Y-m-d'); ?>" required>
                
                <label for="train_class">Class:</label>
                <select id="train_class" name="class">
                    <option value="SL">Sleeper (SL)</option>
                    <option value="AC3">AC 3 Tier (3A)</option>
                    <option value="AC2">AC 2 Tier (2A)</option>
                    <option value="AC1">AC 1st Class (1A)</option>
                </select>
                
                <label for="train_passengers">Passengers:</label>
                <input type="number" id="train_passengers" name="passengers" value="1" min="1" max="9">
                
                <button type="submit">Test Train Search</button>
            </form>
            
            <div id="trainResults" class="results" style="display: none;">
                <h3>Search Results:</h3>
                <div id="trainJson" class="json"></div>
            </div>
        </div>
    </div>
    
    <script>
        // Function to format JSON for display
        function formatJson(json) {
            if (typeof json === 'string') {
                try {
                    json = JSON.parse(json);
                } catch (e) {
                    return json;
                }
            }
            return JSON.stringify(json, null, 2);
        }
        
        // Handle Flight Search Form
        document.getElementById('flightForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultsDiv = document.getElementById('flightResults');
            const jsonDiv = document.getElementById('flightJson');
            
            resultsDiv.style.display = 'block';
            jsonDiv.innerHTML = 'Loading...';
            
            fetch('flight_search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                jsonDiv.innerHTML = formatJson(data);
            })
            .catch(error => {
                jsonDiv.innerHTML = 'Error: ' + error;
            });
        });
        
        // Handle Train Search Form
        document.getElementById('trainForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultsDiv = document.getElementById('trainResults');
            const jsonDiv = document.getElementById('trainJson');
            
            resultsDiv.style.display = 'block';
            jsonDiv.innerHTML = 'Loading...';
            
            fetch('train_search.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                jsonDiv.innerHTML = formatJson(data);
            })
            .catch(error => {
                jsonDiv.innerHTML = 'Error: ' + error;
            });
        });
    </script>
</body>
</html> 
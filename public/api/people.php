<?php
// api/people.php - Handle people location tracking

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$data_file = __DIR__ . '/../data/people_locations.json';

// Ensure data directory exists
if (!is_dir(dirname($data_file))) {
    mkdir(dirname($data_file), 0777, true);
}

function loadPeopleData() {
    global $data_file;
    if (file_exists($data_file)) {
        $content = file_get_contents($data_file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

function savePeopleData($data) {
    global $data_file;
    return file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
}

function generateDeviceName() {
    // Generate a unique device name if none provided
    $adjectives = ['Swift', 'Bright', 'Happy', 'Quick', 'Smart', 'Cool', 'Fast', 'Nice', 'Good', 'Kind'];
    $nouns = ['Explorer', 'Traveler', 'Wanderer', 'Tourist', 'Adventurer', 'Visitor', 'Guest', 'Friend', 'User', 'Person'];
    
    return $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)] . ' ' . rand(100, 999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save location data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['lat']) || !isset($input['lng'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data. Latitude and longitude are required.']);
        exit;
    }
    
    $lat = floatval($input['lat']);
    $lng = floatval($input['lng']);
    $deviceName = isset($input['deviceName']) && !empty(trim($input['deviceName'])) 
        ? trim($input['deviceName']) 
        : generateDeviceName();
    
    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid coordinates']);
        exit;
    }
    
    $peopleData = loadPeopleData();
    
    // Update or add device location
    $peopleData[$deviceName] = [
        'lat' => $lat,
        'lng' => $lng,
        'time' => time(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (savePeopleData($peopleData)) {
        echo json_encode([
            'success' => true,
            'deviceName' => $deviceName,
            'message' => 'Location saved successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save location data']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve all people locations
    $peopleData = loadPeopleData();
    
    // Clean up old entries (older than 24 hours)
    $cutoffTime = time() - (24 * 60 * 60);
    $cleanedData = [];
    
    foreach ($peopleData as $deviceName => $location) {
        if ($location['time'] > $cutoffTime) {
            $cleanedData[$deviceName] = $location;
        }
    }
    
    // Save cleaned data back if any entries were removed
    if (count($cleanedData) !== count($peopleData)) {
        savePeopleData($cleanedData);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $cleanedData,
        'count' => count($cleanedData)
    ]);
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

<?php
// api/people.php - Handle people location tracking

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
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
    // Generate a more descriptive device name if none provided
    $adjectives = ['Swift', 'Bright', 'Happy', 'Quick', 'Smart', 'Cool', 'Fast', 'Nice', 'Good', 'Kind', 'Brave', 'Calm', 'Bold', 'Free'];
    $nouns = ['Explorer', 'Traveler', 'Wanderer', 'Tourist', 'Adventurer', 'Visitor', 'Guest', 'Friend', 'User', 'Person', 'Navigator', 'Roamer'];
    
    // Add time-based uniqueness
    $timeStamp = date('His'); // HHMMSS format
    
    return $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)] . ' ' . $timeStamp;
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    // Calculate distance between two coordinates in kilometers using Haversine formula
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $lat1Rad = deg2rad($lat1);
    $lng1Rad = deg2rad($lng1);
    $lat2Rad = deg2rad($lat2);
    $lng2Rad = deg2rad($lng2);
    
    $deltaLat = $lat2Rad - $lat1Rad;
    $deltaLng = $lng2Rad - $lng1Rad;
    
    $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
         cos($lat1Rad) * cos($lat2Rad) *
         sin($deltaLng / 2) * sin($deltaLng / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c;
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
    
    // Handle potential duplicate device names by checking if location is significantly different
    if (isset($peopleData[$deviceName])) {
        $existingLat = $peopleData[$deviceName]['lat'];
        $existingLng = $peopleData[$deviceName]['lng'];
        
        // Calculate distance between existing and new location
        $distance = calculateDistance($lat, $lng, $existingLat, $existingLng);
        
        // If distance is more than 100 meters and time difference is small, 
        // it might be a different device with same name
        $timeDiff = time() - $peopleData[$deviceName]['time'];
        if ($distance > 0.1 && $timeDiff < 300) { // 5 minutes
            // Add a number suffix to make it unique
            $counter = 2;
            $originalName = $deviceName;
            while (isset($peopleData[$deviceName])) {
                $deviceName = $originalName . ' (' . $counter . ')';
                $counter++;
            }
        }
    }
    
    // Get alternate name if provided
    $alternateName = isset($input['alternateName']) && !empty(trim($input['alternateName'])) 
        ? trim($input['alternateName']) 
        : null;
    
    // Update or add device location
    // Set timezone to Asia/Ho_Chi_Minh for correct local time
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $peopleData[$deviceName] = [
        'lat' => $lat,
        'lng' => $lng,
        'time' => time(),
        'timestamp' => date('Y-m-d H:i:s'),
        'alternateName' => $alternateName
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
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update alternate name for existing device
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['deviceName']) || !isset($input['alternateName'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Device name and alternate name are required.']);
        exit;
    }
    
    $deviceName = trim($input['deviceName']);
    $alternateName = trim($input['alternateName']);
    
    $peopleData = loadPeopleData();
    
    if (!isset($peopleData[$deviceName])) {
        http_response_code(404);
        echo json_encode(['error' => 'Device not found.']);
        exit;
    }
    
    // Update only the alternate name
    $peopleData[$deviceName]['alternateName'] = !empty($alternateName) ? $alternateName : null;
    
    if (savePeopleData($peopleData)) {
        echo json_encode([
            'success' => true,
            'message' => 'Alternate name updated successfully',
            'deviceName' => $deviceName,
            'alternateName' => $peopleData[$deviceName]['alternateName']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update alternate name']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

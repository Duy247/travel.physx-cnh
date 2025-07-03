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

// Define file paths
$data_file = __DIR__ . '/../data/people_locations.json';
$queue_path = __DIR__ . '/../data/people_queue.json';
$lock_file_path = __DIR__ . '/../data/people_queue.lock';

// Queue processing constants
define('QUEUE_PROCESS_TIMEOUT', 10); // seconds
define('QUEUE_LOCK_TIMEOUT', 30);    // seconds

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

// Queue Management Functions
function acquireLock($lockFilePath) {
    $lockFile = fopen($lockFilePath, 'c+');
    
    if (!$lockFile) {
        return false;
    }
    
    // Try to get an exclusive lock, don't block (LOCK_NB)
    if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
        // If we can't get the lock, check if it's stale
        $lockData = fread($lockFile, 1024);
        if ($lockData) {
            $lockInfo = json_decode($lockData, true);
            if ($lockInfo && isset($lockInfo['timestamp'])) {
                // If the lock is older than timeout, force it
                if (time() - $lockInfo['timestamp'] > QUEUE_LOCK_TIMEOUT) {
                    ftruncate($lockFile, 0);
                    rewind($lockFile);
                    // Try again to get the lock
                    if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
                        fclose($lockFile);
                        return false;
                    }
                } else {
                    // Lock is still valid
                    fclose($lockFile);
                    return false;
                }
            }
        }
    }
    
    // Write lock information
    ftruncate($lockFile, 0);
    rewind($lockFile);
    fwrite($lockFile, json_encode([
        'timestamp' => time(),
        'pid' => getmypid()
    ]));
    
    // Keep the file handle open while we have the lock
    return $lockFile;
}

function releaseLock($lockFileHandle) {
    if ($lockFileHandle) {
        flock($lockFileHandle, LOCK_UN);
        fclose($lockFileHandle);
    }
}

function addToQueue($action, $data) {
    global $queue_path;
    
    // Create a unique operation ID
    $opId = uniqid('ploc_', true);
    
    // Create queue item
    $queueItem = [
        'id' => $opId,
        'action' => $action,
        'data' => $data,
        'timestamp' => time(),
        'status' => 'pending'
    ];
    
    // Load current queue
    $queue = [];
    if (file_exists($queue_path)) {
        $queueContent = file_get_contents($queue_path);
        if ($queueContent) {
            $queue = json_decode($queueContent, true) ?: [];
        }
    }
    
    // Add new item to queue
    $queue[] = $queueItem;
    
    // Save queue
    file_put_contents($queue_path, json_encode($queue, JSON_PRETTY_PRINT));
    
    // Try to process queue
    processQueue();
    
    return $opId;
}

function processQueue() {
    global $queue_path, $lock_file_path;
    
    // Try to get lock for queue processing
    $lockFile = acquireLock($lock_file_path);
    if (!$lockFile) {
        // Another process is already handling the queue
        return false;
    }
    
    // Check if queue file exists
    if (!file_exists($queue_path)) {
        releaseLock($lockFile);
        return true; // No queue to process
    }
    
    // Load queue
    $queueContent = file_get_contents($queue_path);
    $queue = json_decode($queueContent, true) ?: [];
    
    // Process pending items
    $startTime = time();
    $updated = false;
    
    foreach ($queue as $key => &$item) {
        // Stop processing if we've been at it too long
        if (time() - $startTime > QUEUE_PROCESS_TIMEOUT) {
            break;
        }
        
        // Only process pending items
        if ($item['status'] !== 'pending') {
            continue;
        }
        
        // Process the operation based on action
        $success = false;
        
        switch ($item['action']) {
            case 'save_location':
                $success = processSaveLocation($item['data']);
                break;
                
            case 'update_name':
                $success = processUpdateName($item['data']);
                break;
        }
        
        // Update status
        $item['status'] = $success ? 'completed' : 'failed';
        $item['processed_at'] = time();
        $updated = true;
    }
    
    // Save updated queue
    if ($updated) {
        file_put_contents($queue_path, json_encode($queue, JSON_PRETTY_PRINT));
    }
    
    // Clean up completed/failed items older than 1 hour
    cleanupQueue();
    
    // Release lock
    releaseLock($lockFile);
    
    return true;
}

function cleanupQueue() {
    global $queue_path;
    
    // Load queue
    if (!file_exists($queue_path)) {
        return;
    }
    
    $queueContent = file_get_contents($queue_path);
    $queue = json_decode($queueContent, true) ?: [];
    
    // Keep only pending items and items processed in the last hour
    $oneHourAgo = time() - 3600;
    $newQueue = [];
    
    foreach ($queue as $item) {
        if ($item['status'] === 'pending' || 
            ($item['processed_at'] ?? 0) > $oneHourAgo) {
            $newQueue[] = $item;
        }
    }
    
    // Save cleaned queue
    if (count($newQueue) !== count($queue)) {
        file_put_contents($queue_path, json_encode($newQueue, JSON_PRETTY_PRINT));
    }
}

// Process functions for different operations
function processSaveLocation($data) {
    global $data_file;
    
    if (!isset($data['lat']) || !isset($data['lng'])) {
        return false;
    }
    
    $lat = floatval($data['lat']);
    $lng = floatval($data['lng']);
    $deviceName = isset($data['deviceName']) && !empty(trim($data['deviceName'])) 
        ? trim($data['deviceName']) 
        : generateDeviceName();
    
    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return false;
    }
    
    $peopleData = loadPeopleData();
    
    // Get alternate name if provided
    $alternateName = isset($data['alternateName']) && !empty(trim($data['alternateName'])) 
        ? trim($data['alternateName']) 
        : null;
    
    // If device exists and has an alternate name, preserve it unless explicitly overridden
    if (isset($peopleData[$deviceName]) && $peopleData[$deviceName]['alternateName'] && !$alternateName) {
        $alternateName = $peopleData[$deviceName]['alternateName'];
    }
    
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
    
    return savePeopleData($peopleData);
}

function processUpdateName($data) {
    global $data_file;
    
    if (!isset($data['deviceName']) || !isset($data['alternateName'])) {
        return false;
    }
    
    $deviceName = trim($data['deviceName']);
    $alternateName = trim($data['alternateName']);
    
    $peopleData = loadPeopleData();
    
    if (!isset($peopleData[$deviceName])) {
        return false;
    }
    
    // Update only the alternate name
    $peopleData[$deviceName]['alternateName'] = !empty($alternateName) ? $alternateName : null;
    
    return savePeopleData($peopleData);
}

// Check operation status endpoint
function checkOperationStatus($opId) {
    global $queue_path;
    
    // Try to process queue first
    processQueue();
    
    // Check operation status
    $status = 'not_found';
    
    if (file_exists($queue_path)) {
        $queueContent = file_get_contents($queue_path);
        $queue = json_decode($queueContent, true) ?: [];
        
        foreach ($queue as $item) {
            if ($item['id'] === $opId) {
                $status = $item['status'];
                break;
            }
        }
    }
    
    return $status;
}

// Special handling for operation status checks
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_operation'])) {
    $operationId = isset($_GET['operation_id']) ? $_GET['operation_id'] : '';
    
    if (!$operationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing operation ID']);
    } else {
        $status = checkOperationStatus($operationId);
        echo json_encode([
            'success' => true,
            'operation_id' => $operationId,
            'status' => $status
        ]);
    }
    exit;
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
    
    // Add to queue instead of processing immediately
    $opId = addToQueue('save_location', $input);
    
    // Set timezone to Asia/Ho_Chi_Minh for correct local time
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    
    // Provide immediate feedback with temporary data
    echo json_encode([
        'success' => true,
        'deviceName' => $deviceName,
        'message' => 'Location queued for saving',
        'operation_id' => $opId,
        'queued' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
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
    
    // Add to queue instead of processing immediately
    $opId = addToQueue('update_name', $input);
    
    // Provide immediate feedback
    echo json_encode([
        'success' => true,
        'message' => 'Name update queued for processing',
        'deviceName' => $deviceName,
        'alternateName' => $alternateName,
        'operation_id' => $opId,
        'queued' => true
    ]);
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

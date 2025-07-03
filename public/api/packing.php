<?php
// public/api/packing.php

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Type: application/json');

// Define file paths
$groupPackingPath = "../data/packing_group.json";
$personalPackingPath = "../data/packing_personal.json";
$queuePath = "../data/packing_queue.json";
$lockFilePath = "../data/packing_queue.lock";

// Queue processing constants
define('QUEUE_PROCESS_TIMEOUT', 10); // seconds
define('QUEUE_LOCK_TIMEOUT', 30);    // seconds

// Function to read JSON file
function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        // Create default structure if file doesn't exist
        $defaultData = [];
        
        if (strpos($filePath, 'packing_group.json') !== false) {
            $defaultData = ['items' => []];
        } elseif (strpos($filePath, 'packing_personal.json') !== false) {
            $defaultData = ['personal_items' => []];
        }
        
        writeJsonFile($filePath, $defaultData);
        return $defaultData;
    }
    $content = file_get_contents($filePath);
    return json_decode($content, true);
}

// Function to write to JSON file
function writeJsonFile($filePath, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $jsonData);
}

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_group_items':
        $data = readJsonFile($groupPackingPath);
        echo json_encode($data);
        break;
        
    case 'get_personal_items':
        $member = isset($_GET['member']) ? $_GET['member'] : '';
        $data = readJsonFile($personalPackingPath);
        
        if (!empty($member) && isset($data['personal_items'][$member])) {
            echo json_encode(['personal_items' => [$member => $data['personal_items'][$member]]]);
        } else {
            echo json_encode($data);
        }
        break;
        
    case 'add_group_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['name'])) {
            // Add operation to queue
            $opId = addToQueue('add_group_item', $postData);
            
            // For immediate feedback, calculate what the new item would look like
            $id = 'item' . time() . rand(1000, 9999);
            
            $newItem = [
                'id' => $id,
                'name' => $postData['name'],
                'carriers' => isset($postData['carrier']) ? [$postData['carrier']] : []
            ];
            
            // Return success with operation ID and temporary item representation
            echo json_encode([
                'success' => true, 
                'item' => $newItem,
                'operation_id' => $opId,
                'queued' => true
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'add_personal_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['name']) && isset($postData['member'])) {
            // Add operation to queue
            $opId = addToQueue('add_personal_item', $postData);
            
            // Generate a temporary item for immediate feedback
            $memberId = strtolower(substr($postData['member'], 0, 2));
            $id = $memberId . time() . rand(100, 999);
            
            $newItem = [
                'id' => $id,
                'name' => $postData['name'],
                'packed' => false
            ];
            
            // Return success with operation ID and temporary item representation
            echo json_encode([
                'success' => true, 
                'item' => $newItem,
                'operation_id' => $opId,
                'queued' => true
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'update_group_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['id']) && isset($postData['action']) && isset($postData['member'])) {
            // Add operation to queue
            $opId = addToQueue('update_group_item', $postData);
            
            // Return success with operation ID
            echo json_encode([
                'success' => true,
                'operation_id' => $opId,
                'queued' => true,
                'message' => 'Operation added to queue'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'update_personal_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['id']) && isset($postData['member']) && isset($postData['action'])) {
            // Add operation to queue
            $opId = addToQueue('update_personal_item', $postData);
            
            // Return success with operation ID
            echo json_encode([
                'success' => true,
                'operation_id' => $opId,
                'queued' => true,
                'message' => 'Operation added to queue'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'check_operation':
        $operationId = isset($_GET['operation_id']) ? $_GET['operation_id'] : '';
        
        if (!$operationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing operation ID']);
            break;
        }
        
        // Try to process queue first
        processQueue();
        
        // Check operation status
        $status = 'not_found';
        
        if (file_exists($queuePath)) {
            $queueContent = file_get_contents($queuePath);
            $queue = json_decode($queueContent, true) ?: [];
            
            foreach ($queue as $item) {
                if ($item['id'] === $operationId) {
                    $status = $item['status'];
                    break;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'operation_id' => $operationId,
            'status' => $status
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
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
    global $queuePath;
    
    // Create a unique operation ID
    $opId = uniqid('op_', true);
    
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
    if (file_exists($queuePath)) {
        $queueContent = file_get_contents($queuePath);
        if ($queueContent) {
            $queue = json_decode($queueContent, true) ?: [];
        }
    }
    
    // Add new item to queue
    $queue[] = $queueItem;
    
    // Save queue
    file_put_contents($queuePath, json_encode($queue, JSON_PRETTY_PRINT));
    
    // Try to process queue
    processQueue();
    
    return $opId;
}

function processQueue() {
    global $queuePath, $lockFilePath, $groupPackingPath, $personalPackingPath;
    
    // Try to get lock for queue processing
    $lockFile = acquireLock($lockFilePath);
    if (!$lockFile) {
        // Another process is already handling the queue
        return false;
    }
    
    // Check if queue file exists
    if (!file_exists($queuePath)) {
        releaseLock($lockFile);
        return true; // No queue to process
    }
    
    // Load queue
    $queueContent = file_get_contents($queuePath);
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
            case 'add_group_item':
                $success = processAddGroupItem($item['data']);
                break;
                
            case 'update_group_item':
                $success = processUpdateGroupItem($item['data']);
                break;
                
            case 'add_personal_item':
                $success = processAddPersonalItem($item['data']);
                break;
                
            case 'update_personal_item':
                $success = processUpdatePersonalItem($item['data']);
                break;
        }
        
        // Update status
        $item['status'] = $success ? 'completed' : 'failed';
        $item['processed_at'] = time();
        $updated = true;
    }
    
    // Save updated queue
    if ($updated) {
        file_put_contents($queuePath, json_encode($queue, JSON_PRETTY_PRINT));
    }
    
    // Clean up completed/failed items older than 1 hour
    cleanupQueue();
    
    // Release lock
    releaseLock($lockFile);
    
    return true;
}

function cleanupQueue() {
    global $queuePath;
    
    // Load queue
    if (!file_exists($queuePath)) {
        return;
    }
    
    $queueContent = file_get_contents($queuePath);
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
        file_put_contents($queuePath, json_encode($newQueue, JSON_PRETTY_PRINT));
    }
}

// Process functions for different operations
function processAddGroupItem($data) {
    global $groupPackingPath;
    
    if (!isset($data['name'])) {
        return false;
    }
    
    $fileData = readJsonFile($groupPackingPath);
    
    // Generate a unique ID
    $id = 'item' . time() . rand(1000, 9999);
    
    $newItem = [
        'id' => $id,
        'name' => $data['name'],
        'carriers' => []
    ];
    
    // Add carrier if specified
    if (isset($data['carrier'])) {
        $newItem['carriers'] = [$data['carrier']];
    }
    
    $fileData['items'][] = $newItem;
    
    return writeJsonFile($groupPackingPath, $fileData);
}

function processUpdateGroupItem($data) {
    global $groupPackingPath;
    
    if (!isset($data['id']) || !isset($data['action']) || !isset($data['member'])) {
        return false;
    }
    
    $fileData = readJsonFile($groupPackingPath);
    $updated = false;
    
    foreach ($fileData['items'] as $key => $item) {
        if ($item['id'] === $data['id']) {
            if ($data['action'] === 'add_carrier') {
                if (!isset($item['carriers'])) {
                    $fileData['items'][$key]['carriers'] = [];
                }
                
                if (!in_array($data['member'], $fileData['items'][$key]['carriers'])) {
                    $fileData['items'][$key]['carriers'][] = $data['member'];
                    sort($fileData['items'][$key]['carriers']);
                }
                $updated = true;
            } elseif ($data['action'] === 'remove_carrier') {
                if (isset($item['carriers'])) {
                    $index = array_search($data['member'], $item['carriers']);
                    if ($index !== false) {
                        array_splice($fileData['items'][$key]['carriers'], $index, 1);
                        $updated = true;
                    }
                }
            } elseif ($data['action'] === 'delete' && $data['member'] === 'Duy') {
                // Only Duy can delete items
                array_splice($fileData['items'], $key, 1);
                $updated = true;
            }
            
            break;
        }
    }
    
    if ($updated) {
        return writeJsonFile($groupPackingPath, $fileData);
    }
    
    return false;
}

function processAddPersonalItem($data) {
    global $personalPackingPath;
    
    if (!isset($data['name']) || !isset($data['member'])) {
        return false;
    }
    
    $fileData = readJsonFile($personalPackingPath);
    
    // Generate a unique ID
    $memberId = strtolower(substr($data['member'], 0, 2));
    $id = $memberId . time() . rand(100, 999);
    
    $newItem = [
        'id' => $id,
        'name' => $data['name'],
        'packed' => false
    ];
    
    // Create member entry if it doesn't exist
    if (!isset($fileData['personal_items'][$data['member']])) {
        $fileData['personal_items'][$data['member']] = [];
    }
    
    $fileData['personal_items'][$data['member']][] = $newItem;
    
    return writeJsonFile($personalPackingPath, $fileData);
}

function processUpdatePersonalItem($data) {
    global $personalPackingPath;
    
    if (!isset($data['id']) || !isset($data['member']) || !isset($data['action'])) {
        return false;
    }
    
    $fileData = readJsonFile($personalPackingPath);
    $updated = false;
    
    if (isset($fileData['personal_items'][$data['member']])) {
        foreach ($fileData['personal_items'][$data['member']] as $key => $item) {
            if ($item['id'] === $data['id']) {
                if ($data['action'] === 'toggle_packed') {
                    $fileData['personal_items'][$data['member']][$key]['packed'] = !$item['packed'];
                    $updated = true;
                } elseif ($data['action'] === 'delete') {
                    array_splice($fileData['personal_items'][$data['member']], $key, 1);
                    $updated = true;
                }
                break;
            }
        }
    }
    
    if ($updated) {
        return writeJsonFile($personalPackingPath, $fileData);
    }
    
    return false;
}

// Function to write to JSON file

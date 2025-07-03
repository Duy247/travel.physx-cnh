<?php
// public/api/expenses.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Define file paths
$dataFile = '../data/expenses.json';
$queuePath = '../data/expenses_queue.json';
$lockFilePath = '../data/expenses_queue.lock';

// Queue processing constants
define('QUEUE_PROCESS_TIMEOUT', 10); // seconds
define('QUEUE_LOCK_TIMEOUT', 30);    // seconds

function readExpenses() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        return [];
    }
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true);
    return $data ? $data : [];
}

function writeExpenses($expenses) {
    global $dataFile;
    // Ensure directory exists
    $dir = dirname($dataFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($dataFile, json_encode($expenses, JSON_PRETTY_PRINT));
}

function generateId() {
    return uniqid('exp_', true);
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
    $opId = uniqid('expop_', true);
    
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
    global $queuePath, $lockFilePath;
    
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
            case 'create_expense':
                $success = processCreateExpense($item['data']);
                break;
                
            case 'update_expense':
                $success = processUpdateExpense($item['data']);
                break;
                
            case 'delete_expense':
                $success = processDeleteExpense($item['data']);
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
function processCreateExpense($data) {
    global $dataFile;
    
    if (!isset($data['content']) || !isset($data['amount']) || !isset($data['payer'])) {
        return false;
    }
    
    $expense = [
        'id' => generateId(),
        'content' => trim($data['content']),
        'amount' => floatval($data['amount']),
        'payer' => trim($data['payer']),
        'date' => date('Y-m-d H:i:s'),
        'created_at' => time()
    ];
    
    $expenses = readExpenses();
    $expenses[] = $expense;
    
    return writeExpenses($expenses);
}

function processUpdateExpense($data) {
    global $dataFile;
    
    if (!isset($data['id']) || !isset($data['content']) || !isset($data['amount']) || !isset($data['payer'])) {
        return false;
    }
    
    $expenses = readExpenses();
    $found = false;
    
    for ($i = 0; $i < count($expenses); $i++) {
        if ($expenses[$i]['id'] === $data['id']) {
            $expenses[$i]['content'] = trim($data['content']);
            $expenses[$i]['amount'] = floatval($data['amount']);
            $expenses[$i]['payer'] = trim($data['payer']);
            $expenses[$i]['updated_at'] = time();
            $found = true;
            break;
        }
    }
    
    if ($found) {
        return writeExpenses($expenses);
    }
    
    return false;
}

function processDeleteExpense($data) {
    global $dataFile;
    
    if (!isset($data['id'])) {
        return false;
    }
    
    $expenses = readExpenses();
    $filteredExpenses = array_filter($expenses, function($expense) use ($data) {
        return $expense['id'] !== $data['id'];
    });
    
    if (count($filteredExpenses) < count($expenses)) {
        $filteredExpenses = array_values($filteredExpenses); // Re-index array
        return writeExpenses($filteredExpenses);
    }
    
    return false;
}

// Check operation status endpoint
function checkOperationStatus($opId) {
    global $queuePath;
    
    // Try to process queue first
    processQueue();
    
    // Check operation status
    $status = 'not_found';
    
    if (file_exists($queuePath)) {
        $queueContent = file_get_contents($queuePath);
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

$method = $_SERVER['REQUEST_METHOD'];

// Special handling for operation status checks
if ($method === 'GET' && isset($_GET['check_operation'])) {
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

switch ($method) {
    case 'GET':
        $expenses = readExpenses();
        echo json_encode($expenses);
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Debug logging
        error_log('POST input: ' . print_r($input, true));
        
        if (!isset($input['content']) || !isset($input['amount']) || !isset($input['payer'])) {
            error_log('Missing fields - content: ' . (isset($input['content']) ? 'yes' : 'no') . 
                     ', amount: ' . (isset($input['amount']) ? 'yes' : 'no') . 
                     ', payer: ' . (isset($input['payer']) ? 'yes' : 'no'));
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            break;
        }
        
        // Add to queue instead of processing immediately
        $opId = addToQueue('create_expense', $input);
        
        // Generate temporary response for immediate feedback
        $expense = [
            'id' => generateId(), // This ID might be different from the one generated in the queue
            'content' => trim($input['content']),
            'amount' => floatval($input['amount']),
            'payer' => trim($input['payer']),
            'date' => date('Y-m-d H:i:s'),
            'created_at' => time(),
            'operation_id' => $opId,
            'queued' => true
        ];
        
        echo json_encode($expense);
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing expense ID']);
            break;
        }
        
        // Add delete operation to queue
        $opId = addToQueue('delete_expense', $input);
        
        // Check if the expense exists before giving success feedback
        $exists = false;
        $expenses = readExpenses();
        foreach ($expenses as $expense) {
            if ($expense['id'] === $input['id']) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            echo json_encode([
                'success' => true,
                'operation_id' => $opId,
                'queued' => true,
                'message' => 'Delete operation added to queue'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
        }
        break;
        
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || !isset($input['content']) || !isset($input['amount']) || !isset($input['payer'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            break;
        }
        
        // Check if the expense exists
        $exists = false;
        $expenses = readExpenses();
        foreach ($expenses as $expense) {
            if ($expense['id'] === $input['id']) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) {
            // Add update operation to queue
            $opId = addToQueue('update_expense', $input);
            
            // Provide immediate feedback
            $updatedExpense = $input;
            $updatedExpense['updated_at'] = time();
            $updatedExpense['operation_id'] = $opId;
            $updatedExpense['queued'] = true;
            
            echo json_encode($updatedExpense);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>

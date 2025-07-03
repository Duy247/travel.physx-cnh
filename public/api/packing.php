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
        
        if (isset($postData['name']) && isset($postData['carrier'])) {
            $data = readJsonFile($groupPackingPath);
            
            // Generate a unique ID
            $id = 'item' . time() . rand(1000, 9999);
            
            $newItem = [
                'id' => $id,
                'name' => $postData['name'],
                'carriers' => [$postData['carrier']]
            ];
            
            $data['items'][] = $newItem;
            
            if (writeJsonFile($groupPackingPath, $data)) {
                echo json_encode(['success' => true, 'item' => $newItem]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'add_personal_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['name']) && isset($postData['member'])) {
            $data = readJsonFile($personalPackingPath);
            
            // Generate a unique ID
            $memberId = strtolower(substr($postData['member'], 0, 2));
            $id = $memberId . time() . rand(100, 999);
            
            $newItem = [
                'id' => $id,
                'name' => $postData['name'],
                'packed' => false
            ];
            
            // Create member entry if it doesn't exist
            if (!isset($data['personal_items'][$postData['member']])) {
                $data['personal_items'][$postData['member']] = [];
            }
            
            $data['personal_items'][$postData['member']][] = $newItem;
            
            if (writeJsonFile($personalPackingPath, $data)) {
                echo json_encode(['success' => true, 'item' => $newItem]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to write to file']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'update_group_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['id']) && isset($postData['action']) && isset($postData['member'])) {
            $data = readJsonFile($groupPackingPath);
            $updated = false;
            
            foreach ($data['items'] as $key => $item) {
                if ($item['id'] === $postData['id']) {
                    if ($postData['action'] === 'add_carrier') {
                        if (!isset($item['carriers'])) {
                            $data['items'][$key]['carriers'] = [];
                        }
                        
                        if (!in_array($postData['member'], $data['items'][$key]['carriers'])) {
                            $data['items'][$key]['carriers'][] = $postData['member'];
                            sort($data['items'][$key]['carriers']);
                        }
                    } elseif ($postData['action'] === 'remove_carrier') {
                        if (isset($item['carriers'])) {
                            $index = array_search($postData['member'], $data['items'][$key]['carriers']);
                            if ($index !== false) {
                                array_splice($data['items'][$key]['carriers'], $index, 1);
                            }
                        }
                    } elseif ($postData['action'] === 'delete' && $postData['member'] === 'Duy') {
                        // Only Duy can delete items
                        array_splice($data['items'], $key, 1);
                    }
                    
                    $updated = true;
                    break;
                }
            }
            
            if ($updated && writeJsonFile($groupPackingPath, $data)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update or write to file']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    case 'update_personal_item':
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (isset($postData['id']) && isset($postData['member']) && isset($postData['action'])) {
            $data = readJsonFile($personalPackingPath);
            $updated = false;
            
            if (isset($data['personal_items'][$postData['member']])) {
                foreach ($data['personal_items'][$postData['member']] as $key => $item) {
                    if ($item['id'] === $postData['id']) {
                        if ($postData['action'] === 'toggle_packed') {
                            $data['personal_items'][$postData['member']][$key]['packed'] = !$item['packed'];
                            $updated = true;
                        } elseif ($postData['action'] === 'delete') {
                            array_splice($data['personal_items'][$postData['member']], $key, 1);
                            $updated = true;
                        }
                        break;
                    }
                }
            }
            
            if ($updated && writeJsonFile($personalPackingPath, $data)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update or write to file']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

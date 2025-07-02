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

$dataFile = '../data/expenses.json';

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

$method = $_SERVER['REQUEST_METHOD'];

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
        
        $expense = [
            'id' => generateId(),
            'content' => trim($input['content']),
            'amount' => floatval($input['amount']),
            'payer' => trim($input['payer']),
            'date' => date('Y-m-d H:i:s'),
            'created_at' => time()
        ];
        
        $expenses = readExpenses();
        $expenses[] = $expense;
        
        if (writeExpenses($expenses)) {
            echo json_encode($expense);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save expense']);
        }
        break;
        
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing expense ID']);
            break;
        }
        
        $expenses = readExpenses();
        $filteredExpenses = array_filter($expenses, function($expense) use ($input) {
            return $expense['id'] !== $input['id'];
        });
        
        if (count($filteredExpenses) < count($expenses)) {
            $filteredExpenses = array_values($filteredExpenses); // Re-index array
            if (writeExpenses($filteredExpenses)) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete expense']);
            }
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
        
        $expenses = readExpenses();
        $found = false;
        
        for ($i = 0; $i < count($expenses); $i++) {
            if ($expenses[$i]['id'] === $input['id']) {
                $expenses[$i]['content'] = trim($input['content']);
                $expenses[$i]['amount'] = floatval($input['amount']);
                $expenses[$i]['payer'] = trim($input['payer']);
                $expenses[$i]['updated_at'] = time();
                $found = true;
                break;
            }
        }
        
        if ($found) {
            if (writeExpenses($expenses)) {
                echo json_encode($expenses[$i]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update expense']);
            }
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

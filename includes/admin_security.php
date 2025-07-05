<?php
/**
 * Admin security middleware
 * This file should be included at the beginning of any admin-related PHP file
 */

// Function to generate today's password in dd/MM/yyyy format
function getTodayPassword() {
    return date('d/m/Y');
}

// Function to check if a path is within allowed directories
function isPathAllowed($path, $baseDir) {
    $realPath = realpath($path);
    $realBaseDir = realpath($baseDir);
    
    // Check if the path is within the allowed directory
    return $realPath && strpos($realPath, $realBaseDir) === 0;
}

// Function to validate JSON content
function validateJson($content) {
    json_decode($content);
    return json_last_error() === JSON_ERROR_NONE;
}

// Function to pretty print JSON
function prettyPrintJson($json) {
    $decoded = json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return $json;
}

// Function to safely save JSON file
function saveJsonFile($path, $content) {
    // Validate JSON format
    if (!validateJson($content)) {
        return [false, "Invalid JSON format: " . json_last_error_msg()];
    }
    
    // Create backup
    $backupPath = $path . '.bak';
    if (file_exists($path)) {
        copy($path, $backupPath);
    }
    
    // Format JSON for better readability
    $prettyJson = prettyPrintJson($content);
    
    // Write to file
    $result = file_put_contents($path, $prettyJson);
    if ($result === false) {
        return [false, "Failed to write to file"];
    }
    
    return [true, "File saved successfully"];
}

// Function to get all JSON files in a directory and its subdirectories
function getAllJsonFiles($dir) {
    $files = [];
    $jsonFiles = glob($dir . '*.json');
    
    // Add files from the main directory
    foreach ($jsonFiles as $file) {
        $files[] = $file;
    }
    
    // Get subdirectories
    $subdirs = array_filter(glob($dir . '*'), 'is_dir');
    foreach ($subdirs as $subdir) {
        $subFiles = glob($subdir . '/*.json');
        foreach ($subFiles as $file) {
            $files[] = $file;
        }
    }
    
    return $files;
}

// Prevent direct access to this file
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit;
}

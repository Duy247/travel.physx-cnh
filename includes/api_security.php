<?php
/**
 * API security file
 * Include this file at the beginning of any API PHP file to ensure it can't be accessed directly
 */

// Define the API access constant
define('API_ACCESS', true);

// Check if the script is being called from the main application
if (!defined('APP_ACCESS') && !defined('ADMIN_ACCESS')) {
    // Check if we are in an API directory
    $scriptPath = $_SERVER['SCRIPT_FILENAME'];
    $isApiFile = strpos($scriptPath, '/api/') !== false || strpos($scriptPath, '\\api\\') !== false;
    
    // Only allow direct access to API files
    if (!$isApiFile) {
        header('HTTP/1.0 403 Forbidden');
        echo 'Access denied';
        exit;
    }
}

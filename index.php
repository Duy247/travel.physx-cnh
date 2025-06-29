<?php
/**
 * Travel PhysX CNH
 * Root directory redirect to public folder
 * 
 * This file redirects requests from the root directory to the public directory
 * which is the proper entry point for the application.
 */

// Check if we're in the root directory and redirect to public
if (basename(__DIR__) !== 'public') {
    // Redirect to public directory
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
    
    // If public directory exists, redirect there
    if (is_dir(__DIR__ . '/public')) {
        $redirectUrl = $protocol . '://' . $host . $uri . '/public/';
        header('Location: ' . $redirectUrl, true, 301);
        exit();
    }
}

// If we reach here, include the public index.php
if (file_exists(__DIR__ . '/public/index.php')) {
    require_once __DIR__ . '/public/index.php';
} else {
    echo "<h1>Welcome to Travel PhysX CNH</h1>";
    echo "<p>Please ensure the public directory and index.php file exist.</p>";
}

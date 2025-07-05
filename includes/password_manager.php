<?php
// Password management file for the admin backend
require_once __DIR__ . '/admin_security.php';

// Check if the script is accessed directly
if (count(get_included_files()) <= 1) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
    exit;
}

/**
 * Check if a custom password is set in password.json
 * @return bool|string False if no custom password, otherwise returns the password
 */
function getCustomPassword() {
    $passwordFile = __DIR__ . '/../public/data/password.json';
    if (file_exists($passwordFile)) {
        $data = json_decode(file_get_contents($passwordFile), true);
        if (isset($data['password']) && !empty($data['password'])) {
            return $data['password'];
        }
    }
    return false;
}

/**
 * Save a custom password to password.json
 * @param string $password The password to save
 * @return array [success, message]
 */
function saveCustomPassword($password) {
    $passwordFile = __DIR__ . '/../public/data/password.json';
    $data = ['password' => $password];
    
    // Create directory if it doesn't exist
    $dir = dirname($passwordFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Save the file
    if (file_put_contents($passwordFile, json_encode($data, JSON_PRETTY_PRINT))) {
        return [true, 'Password saved successfully'];
    } else {
        return [false, 'Failed to save password'];
    }
}

/**
 * Check if the provided password is valid
 * @param string $password The password to check
 * @return bool True if valid, false otherwise
 */
function isValidPassword($password) {
    // Check for custom password first
    $customPassword = getCustomPassword();
    if ($customPassword !== false) {
        return $password === $customPassword;
    }
    
    // Fall back to date-based password
    return $password === getTodayPassword();
}

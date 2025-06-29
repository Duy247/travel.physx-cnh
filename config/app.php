<?php
/**
 * Application Configuration
 * Travel PhysX CNH
 */

return [
    'app' => [
        'name' => 'Travel PhysX CNH',
        'version' => '1.0.0',
        'timezone' => 'UTC',
        'debug' => false, // Set to true for development
    ],
    
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'travel_db',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'src' => dirname(__DIR__) . '/src',
        'config' => __DIR__,
    ],
];

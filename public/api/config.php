<?php
// API endpoint to serve configuration to frontend
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Load environment configuration
require_once __DIR__ . '/../../config/config.php';

// Only serve non-sensitive configuration to frontend
$frontendConfig = [
    'routing' => [
        'openrouteservice' => [
            'apiKey' => $_ENV['OPENROUTESERVICE_API_KEY'] ?? '',
            'maxRequests' => 2000, // Free tier limit per day
            'endpoint' => 'https://api.openrouteservice.org/v2/directions/driving-car'
        ]
    ],
    'app' => [
        'debug' => $_ENV['APP_DEBUG'] ?? false,
        'env' => $_ENV['APP_ENV'] ?? 'production'
    ]
];

echo json_encode($frontendConfig);

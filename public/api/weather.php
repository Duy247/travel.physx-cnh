<?php
// public/api/weather.php

// Prevent caching for API responses
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';

// Get location parameter
$location = $_GET['location'] ?? 'hue';

// Define coordinates for each location
$locations = [
    'hue' => ['lat' => 16.4637, 'lon' => 107.5909, 'name' => 'Huế'],
    'danang' => ['lat' => 16.0471, 'lon' => 108.2068, 'name' => 'Đà Nẵng'],
    'hoian' => ['lat' => 15.8801, 'lon' => 108.338, 'name' => 'Hội An'],
    'hanoi' => ['lat' => 21.0285, 'lon' => 105.8542, 'name' => 'Hà Nội']
];

// Validate location
if (!isset($locations[$location])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid location',
        'message' => 'Supported locations: ' . implode(', ', array_keys($locations))
    ]);
    exit;
}

// Check if API key is configured
if (!defined('OPENWEATHER_API_KEY') || empty(OPENWEATHER_API_KEY)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuration error',
        'message' => 'Weather API key not configured'
    ]);
    exit;
}

$lat = $locations[$location]['lat'];
$lon = $locations[$location]['lon'];
$locationName = $locations[$location]['name'];

// Build OpenWeatherMap API URL
$apiUrl = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&appid=" . OPENWEATHER_API_KEY;

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local development

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API request failed',
        'message' => 'Unable to fetch weather data: ' . $error
    ]);
    exit;
}

// Handle HTTP errors
if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode([
        'error' => 'Weather API error',
        'message' => 'HTTP ' . $httpCode,
        'response' => $response
    ]);
    exit;
}

// Decode and validate response
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Invalid response',
        'message' => 'Unable to parse weather data'
    ]);
    exit;
}

// Add location metadata to response
$data['location_info'] = [
    'key' => $location,
    'name' => $locationName,
    'coordinates' => [
        'lat' => $lat,
        'lon' => $lon
    ]
];

// Return the weather data
echo json_encode($data);
?>

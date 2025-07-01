<?php
// public/Map.php

// Prevent caching for mobile devices
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Generate cache busting timestamp
$cache_bust = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Travel Map - Hue, Danang & Hoian</title>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cache_bust; ?>">
    <link rel="stylesheet" href="css/map.css?v=<?php echo $cache_bust; ?>">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-omnivore@0.3.4/leaflet-omnivore.min.js"></script>
    <script src="https://unpkg.com/leaflet.locatecontrol/dist/L.Control.Locate.min.js"></script>
</head>
<body>
    <!-- Animated Background -->
    <div class="map-page-bg"></div>

    <!-- Back Button -->
    <a href="index.php" class="back-button" title="Back to Menu">
        <
    </a>


    <!-- Page Header -->
    <div class="map-header">
        <h1>Interactive Travel Map</h1>
        <h2>Hue - Danang - Hoian | Central Vietnam Adventure</h2>
    </div>



    <!-- Map Container -->
    <div class="map-container">
        <div id="map"></div>
        
        <!-- Loading Indicator -->
        <div class="map-loading" id="mapLoading">
            <div class="map-loading-spinner"></div>
            <div class="map-loading-text">Loading map data...</div>
        </div>
    </div>

    <!-- Refresh Button for People Category -->
    <div class="refresh-people-btn" id="refreshPeopleBtn" style="display: none; display: flex; justify-content: center; align-items: center;">
        <button onclick="window.travelMap.loadPeopleData()" title="Refresh People Locations" style="display: flex; justify-content: center; align-items: center;">
            <svg fill="#000000" height="32px" width="32px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 294.843 294.843" xml:space="preserve" style="display: block; margin: auto;">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <g>
                        <path d="M147.421,0c-3.313,0-6,2.687-6,6s2.687,6,6,6c74.671,0,135.421,60.75,135.421,135.421s-60.75,135.421-135.421,135.421 S12,222.093,12,147.421c0-50.804,28.042-96.902,73.183-120.305c2.942-1.525,4.09-5.146,2.565-8.088 c-1.525-2.942-5.147-4.09-8.088-2.565C30.524,41.937,0,92.118,0,147.421c0,81.289,66.133,147.421,147.421,147.421 s147.421-66.133,147.421-147.421S228.71,0,147.421,0z"></path>
                        <path d="M205.213,71.476c-16.726-12.747-36.71-19.484-57.792-19.484c-52.62,0-95.43,42.81-95.43,95.43s42.81,95.43,95.43,95.43 c25.49,0,49.455-9.926,67.479-27.951c2.343-2.343,2.343-6.142,0-8.485c-2.343-2.343-6.143-2.343-8.485,0 c-15.758,15.758-36.709,24.436-58.994,24.436c-46.003,0-83.43-37.426-83.43-83.43s37.426-83.43,83.43-83.43 c36.894,0,69.843,24.715,80.126,60.104c0.924,3.182,4.253,5.011,7.436,4.087c3.182-0.925,5.012-4.254,4.087-7.436 C233.422,101.308,221.398,83.809,205.213,71.476z"></path>
                        <path d="M217.773,129.262c-2.344-2.343-6.143-2.343-8.485,0c-2.343,2.343-2.343,6.142,0,8.485l22.57,22.571 c1.125,1.125,2.651,1.757,4.243,1.757s3.118-0.632,4.243-1.757l22.57-22.571c2.343-2.343,2.343-6.142,0-8.485 c-2.344-2.343-6.143-2.343-8.485,0l-18.328,18.328L217.773,129.262z"></path>
                    </g>
                </g>
            </svg>
        </button>
    </div>

    <!-- Floating Stats Panel -->
    <div class="map-stats" id="mapStats">
        <div class="map-stats-content">
            <div class="map-stats-item">
                <span class="map-stats-label">Active Category:</span>
                <span class="map-stats-value" id="activeCategory">All Locations</span>
            </div>
            <div class="map-stats-item">
                <span class="map-stats-label">Total Markers:</span>
                <span class="map-stats-value" id="markerCount">0</span>
            </div>
            <div class="map-stats-item">
                <span class="map-stats-label">Map Zoom:</span>
                <span class="map-stats-value" id="currentZoom">10</span>
            </div>
        </div>
    </div>

    <!-- Map Legend -->
    <div class="map-legend show" id="mapLegend">
        <div class="map-legend-title">Choose Category</div>
        <div class="map-legend-item active" data-kml="all">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);"></div>
            <span>All Locations</span>
        </div>
        <div class="map-legend-item" data-kml="breakfast">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);"></div>
            <span>Breakfast</span>
        </div>
        <div class="map-legend-item" data-kml="lunch-dinner">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"></div>
            <span>Lunch & Dinner</span>
        </div>
        <div class="map-legend-item" data-kml="snack-night">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);"></div>
            <span>Snack & Night</span>
        </div>
        <div class="map-legend-item" data-kml="coffee">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);"></div>
            <span>Coffee</span>
        </div>
        <div class="map-legend-item" data-kml="tour">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);"></div>
            <span>Tour Points</span>
        </div>
        <div class="map-legend-item" data-kml="fuel">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"></div>
            <span>Fuel</span>
        </div>
        <div class="map-legend-item" data-kml="people">
            <div class="map-legend-color" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);"></div>
            <span>People</span>
        </div>
    </div>

    <!-- Battery Status Indicator -->
    <div class="battery-status-indicator" id="batteryStatusIndicator">
        🔋 Optimized (30s)
    </div>

    <!-- Power Saving Toggle -->
    <div class="power-saving-toggle" id="powerSavingToggle">
        <span>Ultra Power Saver</span>
        <div class="power-saving-switch" id="powerSavingSwitch" title="Toggle Ultra Power Saving Mode (longer intervals)"></div>
    </div>
    
    <!-- GPS Accuracy Toggle -->
    <div class="power-saving-toggle" id="gpsAccuracyToggle" style="top: 200px;">
        <span>High GPS Accuracy</span>
        <div class="power-saving-switch active" id="gpsAccuracySwitch" title="Toggle GPS Accuracy (high accuracy uses more battery)"></div>
    </div>

    <!-- Map JavaScript -->
    <script src="js/map.js?v=<?php echo $cache_bust; ?>"></script>
    
    <!-- Cache Busting Script -->
    <script src="js/cache-buster.js?v=<?php echo $cache_bust; ?>"></script>
</body>
</html>

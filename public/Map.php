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
    </div>

    <!-- Map JavaScript -->
    <script src="js/map.js?v=<?php echo $cache_bust; ?>"></script>
    
    <!-- Cache Busting Script -->
    <script src="js/cache-buster.js?v=<?php echo $cache_bust; ?>"></script>
</body>
</html>

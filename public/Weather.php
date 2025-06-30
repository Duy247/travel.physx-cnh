<!DOCTYPE html>
<html>
<head>
    <title>Bản Đồ Thời Tiết</title>

    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" rel="stylesheet"/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0b;
            color: #e4e4e7;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(217, 70, 239, 0.06) 0%, transparent 50%),
                #0a0a0b;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Circular Back Button */
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: 
                linear-gradient(135deg, rgba(24, 24, 27, 0.95) 0%, rgba(39, 39, 42, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.3),
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: #f4f4f5;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            overflow: hidden;
        }

        .back-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .back-button:hover {
            transform: scale(1.08) translateY(-2px);
            background: 
                linear-gradient(135deg, rgba(99, 102, 241, 0.9) 0%, rgba(139, 92, 246, 0.8) 100%);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 12px 35px rgba(99, 102, 241, 0.4),
                0 6px 20px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .back-button:hover::before {
            opacity: 1;
        }

        .back-button:active {
            transform: scale(1.02);
        }

        /* Weather Controls Panel */
        .weather-controls {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: 
                linear-gradient(135deg, rgba(24, 24, 27, 0.95) 0%, rgba(39, 39, 42, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 4px 16px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            max-width: 90vw;
            width: auto;
            min-width: 320px;
        }

        .weather-controls::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(99, 102, 241, 0.3), 
                rgba(139, 92, 246, 0.3), 
                rgba(217, 70, 239, 0.3),
                rgba(99, 102, 241, 0.3)
            );
            border-radius: 22px;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
            opacity: 0.6;
        }

        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .control-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .control-row:last-child {
            margin-bottom: 0;
        }

        .control-row.main-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            flex-direction: row;
            gap: 1rem;
            width: 100%;
        }

        .control-row.secondary-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: nowrap;
            flex-direction: row;
        }

        /* Radio Button Styling */
        .radio-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            background: rgba(24, 24, 27, 0.6);
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            height: 60px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            color: #a1a1aa;
            transition: color 0.3s ease;
        }

        .radio-item:hover {
            color: #e4e4e7;
        }

        .radio-item input[type="radio"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .radio-item input[type="radio"]:checked {
            border-color: #6366f1;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .radio-item input[type="radio"]:checked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
        }

        /* Control Buttons Group */
        .control-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            background: rgba(24, 24, 27, 0.6);
            padding: 0rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: 60px;
        }

        /* Control Buttons */
        .control-btn {
            padding: 0.75rem 1rem;
            background: 
                linear-gradient(135deg, rgba(24, 24, 27, 0.8) 0%, rgba(39, 39, 42, 0.7) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #e4e4e7;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .control-btn:hover {
            background: 
                linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        /* Select Dropdown */
        .control-select {
            padding: 0.75rem 1rem;
            background: 
                linear-gradient(135deg, rgba(24, 24, 27, 0.8) 0%, rgba(39, 39, 42, 0.7) 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: #e4e4e7;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            min-width: 160px;
            flex-shrink: 0;
        }

        .control-select:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: 
                linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
        }

        .control-select option {
            background: #18181b;
            color: #e4e4e7;
            padding: 0.5rem;
        }

        /* Range Slider */
        .opacity-control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(24, 24, 27, 0.6);
            padding: 0.75rem 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 180px;
            flex-shrink: 0;
        }

        .opacity-control label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #a1a1aa;
            white-space: nowrap;
        }

        .opacity-slider {
            flex: 1;
            appearance: none;
            height: 6px;
            background: 
                linear-gradient(90deg, rgba(255, 255, 255, 0.1) 0%, rgba(99, 102, 241, 0.3) 100%);
            border-radius: 3px;
            outline: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .opacity-slider::-webkit-slider-thumb {
            appearance: none;
            width: 18px;
            height: 18px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
            transition: all 0.3s ease;
        }

        .opacity-slider::-webkit-slider-thumb:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.6);
        }

        .opacity-slider::-moz-range-thumb {
            width: 18px;
            height: 18px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.4);
        }

        /* Timestamp Display */
        .timestamp-display {
            position: fixed;
            top: 140px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999;
            background: 
                linear-gradient(135deg, rgba(24, 24, 27, 0.9) 0%, rgba(39, 39, 42, 0.8) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            color: #f4f4f5;
            font-size: 0.9rem;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        /* Map Container */
        .map-wrapper {
            position: fixed;
            top: 200px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 4px 16px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .map-wrapper::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(99, 102, 241, 0.2), 
                rgba(139, 92, 246, 0.2), 
                rgba(217, 70, 239, 0.2),
                rgba(99, 102, 241, 0.2)
            );
            border-radius: 22px;
            z-index: -1;
            animation: borderGlow 4s linear infinite;
            opacity: 0.4;
        }

        #mapid {
            width: 100%;
            height: 100%;
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .back-button {
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }

            .weather-controls {
                top: 10px;
                left: 10px;
                right: 10px;
                transform: none;
                padding: 1rem;
                max-width: none;
                min-width: auto;
            }

            .control-row.main-controls {
                flex-direction: row;
                gap: 1rem;
                align-items: center;
            }

            .control-row.secondary-controls {
                flex-direction: row;
                gap: 0.5rem;
                align-items: stretch;
            }

            .radio-group {
                justify-content: center;
            }

            .control-buttons {
                justify-content: center;
            }

            .control-select {
                min-width: auto;
                width: 50%;
            }

            .opacity-control {
                min-width: auto;
                width: 50%;
            }

            .timestamp-display {
                top: auto;
                bottom: 10px;
                left: 10px;
                right: 10px;
                transform: none;
                font-size: 0.8rem;
            }

            .map-wrapper {
                top: 180px;
                left: 10px;
                right: 10px;
                bottom: 70px;
            }
        }

        @media (max-width: 480px) {
            .back-button {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .weather-controls {
                padding: 0.75rem;
            }

            .radio-group {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .control-buttons {
                flex-direction: row;
                gap: 0.5rem;
                width: 60%;
            }

            .control-btn {
                padding: 0.2rem;
                font-size: 0.8rem;
                max-width: 40%;
            }

            .control-select {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .opacity-control {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Circular Back Button -->
    <a href="index.php" class="back-button">‹</a>

    <!-- Weather Controls Panel -->
    <div class="weather-controls">
        <!-- Main Control Row: Radio Group and Play Controls -->
        <div class="control-row main-controls">
            <div class="radio-group">
                <label class="radio-item">
                    <input type="radio" name="kind" checked="checked" onchange="setKind('radar')">
                    <span>Bản Đồ Mưa</span>
                </label>
                <label class="radio-item">
                    <input type="radio" name="kind" onchange="setKind('satellite')">
                    <span>Bản Đồ Mây</span>
                </label>
            </div>

            <div class="control-buttons">
                <input type="button" class="control-btn" onclick="stop(); showFrame(animationPosition - 1); return;" value="◀" />
                <input type="button" class="control-btn" onclick="playStop();" value="Play / Stop" />
                <input type="button" class="control-btn" onclick="stop(); showFrame(animationPosition + 1); return;" value="▶" />
            </div>
        </div>

        <!-- Color Scheme Selection -->
        <div class="control-row secondary-controls">
            <select id="colors" class="control-select" onchange="setColors(); return;">
                <option value="0">Black and White Values</option>
                <option value="1">Original</option>
                <option value="2" selected="selected">Universal Blue</option>
                <option value="3">TITAN</option>
                <option value="4">The Weather Channel</option>
                <option value="5">Meteored</option>
                <option value="6">NEXRAD Level-III</option>
                <option value="7">RAINBOW @ SELEX-SI</option>
                <option value="8">Dark Sky</option>
            </select>
            <div class="opacity-control">
                <label for="opacitySlider">Độ trong suốt:</label>
                <input type="range" id="opacitySlider" class="opacity-slider" min="0" max="100" value="100" oninput="setLayerOpacity(this.value)">
            </div>
        </div>
    </div>

    <div id="timestamp" class="timestamp-display">FRAME TIME</div>

    <div class="map-wrapper">
        <div id="mapid"></div>
    </div>

<script>
    var map = L.map('mapid').setView([20.793583496388354, 107.00525280160254], 9);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attributions: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
    }).addTo(map);

    /**
     * RainViewer radar animation part
     * @type {number[]}
     */
    var apiData = {};
    var mapFrames = [];
    var lastPastFramePosition = -1;
    var radarLayers = [];

    var optionKind = 'radar'; // can be 'radar' or 'satellite'

    var optionTileSize = 256; // can be 256 or 512.
    var optionColorScheme = 2; // from 0 to 8. Check the https://rainviewer.com/api/color-schemes.html for additional information
    var optionSmoothData = 1; // 0 - not smooth, 1 - smooth
    var optionSnowColors = 1; // 0 - do not show snow colors, 1 - show snow colors

    var animationPosition = 0;
    var animationTimer = false;

    var loadingTilesCount = 0;
    var loadedTilesCount = 0;

    var layerOpacity = 100; // Variable to store the layer opacity

    function startLoadingTile() {
        loadingTilesCount++;    
    }
    function finishLoadingTile() {
        // Delayed increase loaded count to prevent changing the layer before 
        // it will be replaced by next
        setTimeout(function() { loadedTilesCount++; }, 250);
    }
    function isTilesLoading() {
        return loadingTilesCount > loadedTilesCount;
    }

    /**
     * Load all the available maps frames from RainViewer API
     */
    var apiRequest = new XMLHttpRequest();
    apiRequest.open("GET", "https://api.rainviewer.com/public/weather-maps.json", true);
    apiRequest.onload = function(e) {
        // store the API response for re-use purposes in memory
        apiData = JSON.parse(apiRequest.response);
        initialize(apiData, optionKind);
    };
    apiRequest.send();

    /**
     * Initialize internal data from the API response and options
     */
     function initialize(api, kind) {
        // remove all already added tiled layers
        for (var i in radarLayers) {
            map.removeLayer(radarLayers[i]);
        }
        mapFrames = [];
        radarLayers = [];
        animationPosition = 0;

        if (!api) {
            return;
        }
        if (kind == 'satellite' && api.satellite && api.satellite.infrared) {
            mapFrames = api.satellite.infrared;

            lastPastFramePosition = api.satellite.infrared.length - 1;
        }
        else if (api.radar && api.radar.past) {
            mapFrames = api.radar.past;
            if (api.radar.nowcast) {
                mapFrames = mapFrames.concat(api.radar.nowcast);
            }

            // show the last "past" frame
            lastPastFramePosition = api.radar.past.length - 1;
        }

        // Ensure that only one layer is visible initially
        for (var i = 0; i < mapFrames.length; i++) {
            if (i == lastPastFramePosition) {
                addLayer(mapFrames[i]);
                radarLayers[mapFrames[i].path].setOpacity(layerOpacity / 100);
            } else {
                addLayer(mapFrames[i]);
                radarLayers[mapFrames[i].path].setOpacity(0);
            }
        }

        showFrame(lastPastFramePosition, true);
    }

    /**
     * Animation functions
     * @param path - Path to the XYZ tile
     */
    function addLayer(frame) {
        if (!radarLayers[frame.path]) {
            var colorScheme = optionKind == 'satellite' ? 0 : optionColorScheme;
            var smooth = optionKind == 'satellite' ? 0 : optionSmoothData;
            var snow = optionKind == 'satellite' ? 0 : optionSnowColors;

            var source = new L.TileLayer(apiData.host + frame.path + '/' + optionTileSize + '/{z}/{x}/{y}/' + colorScheme + '/' + smooth + '_' + snow + '.png', {
                tileSize: 256,
                opacity: layerOpacity / 100, // Set the opacity based on the stored value
                zIndex: frame.time
            });

            // Track layer loading state to not display the overlay 
            // before it will completelly loads
            source.on('loading', startLoadingTile);
            source.on('load', finishLoadingTile); 
            source.on('remove', finishLoadingTile);

            radarLayers[frame.path] = source;
        }
        if (!map.hasLayer(radarLayers[frame.path])) {
            map.addLayer(radarLayers[frame.path]);
        }
    }

    /**
     * Display particular frame of animation for the @position
     * If preloadOnly parameter is set to true, the frame layer only adds for the tiles preloading purpose
     * @param position
     * @param preloadOnly
     * @param force - display layer immediatelly
     */
    function changeRadarPosition(position, preloadOnly, force) {
        while (position >= mapFrames.length) {
            position -= mapFrames.length;
        }
        while (position < 0) {
            position += mapFrames.length;
        }

        var currentFrame = mapFrames[animationPosition];
        var nextFrame = mapFrames[position];

        addLayer(nextFrame);

        // Quit if this call is for preloading only by design
        // or some times still loading in background
        if (preloadOnly || (isTilesLoading() && !force)) {
            return;
        }

        animationPosition = position;

        if (radarLayers[currentFrame.path]) {
            radarLayers[currentFrame.path].setOpacity(0);
        }
        radarLayers[nextFrame.path].setOpacity(layerOpacity / 100); // Set the opacity based on the stored value


        var pastOrForecast = nextFrame.time > Date.now() / 1000 ? 'Dự Báo' : 'Đã Qua';

        document.getElementById("timestamp").innerHTML = pastOrForecast + ': ' + (new Date(nextFrame.time * 1000)).toString();
    }

    /**
     * Check avialability and show particular frame position from the timestamps list
     */
    function showFrame(nextPosition, force) {
        var preloadingDirection = nextPosition - animationPosition > 0 ? 1 : -1;

        changeRadarPosition(nextPosition, false, force);

        // preload next next frame (typically, +1 frame)
        // if don't do that, the animation will be blinking at the first loop
        changeRadarPosition(nextPosition + preloadingDirection, true);
    }

    /**
     * Stop the animation
     * Check if the animation timeout is set and clear it.
     */
    function stop() {
        if (animationTimer) {
            clearTimeout(animationTimer);
            animationTimer = false;
            return true;
        }
        return false;
    }

    function play() {
        showFrame(animationPosition + 1);

        // Main animation driver. Run this function every 500 ms
        animationTimer = setTimeout(play, 500);
    }

    function playStop() {
        if (!stop()) {
            play();
        }
    }

    /**
     * Change map options
     */
    function setKind(kind) {
        optionKind = kind;
        initialize(apiData, optionKind);
    }


    function setColors() {
        var e = document.getElementById('colors');
        optionColorScheme = e.options[e.selectedIndex].value;
        initialize(apiData, optionKind);
    }

    function setLayerOpacity(value) {
        layerOpacity = value; // Store the opacity value
        var frame = mapFrames[animationPosition];
        radarLayers[frame.path].setOpacity(value / 100);
    }

    /**
     * Handle arrow keys for navigation between next \ prev frames
     */
    document.onkeydown = function (e) {
        e = e || window.event;
        switch (e.which || e.keyCode) {
            case 37: // left
                stop();
                showFrame(animationPosition - 1, true);
                break;

            case 39: // right
                stop();
                showFrame(animationPosition + 1, true);
                break;

            default:
                return; // exit this handler for other keys
        }
        e.preventDefault();
        return false;
    }
</script>

</body>
</html>
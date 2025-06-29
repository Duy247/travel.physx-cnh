<?php
// public/index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel PhysX CNH</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="header-indicator" id="headerIndicator">Tap or swipe down for header</div>
    
    <header class="header" id="header">
        <h1>Hue - Danang - Hoian</h1>
        <h2>July 31 - August 3, 2025 • Central Vietnam</h2>
    </header>

    <div class="swipe-container">
        <div class="swipe-panel nav-panel">
            <div class="nav-section">
                <div class="countdown" id="countdown"></div>
                
                <nav class="nav-grid">
                    <a href="Map.php">Travel Map</a>
                    <a href="Weather.php">Weather</a>
                    <a href="Plan.php">Timeline</a>
                    <a href="Spending.php">Budget</a>
                    <a href="PackingList.php">Packing List</a>
                    <a href="PersonalPack.php">Personal Items</a>
                    <a href="Info.php">Emergency Info</a>
                    <a href="Gallery.php">Gallery</a>
                </nav>
                
            </div>
        </div>

        <div class="swipe-panel weather-panel">
            <div class="weather-section">
                <div class="location-selector">
                    <div class="location-buttons">
                        <button class="location-btn active" data-location="hue">Huế</button>
                        <button class="location-btn" data-location="danang">Đà Nẵng</button>
                        <button class="location-btn" data-location="hoian">Hội An</button>
                        <button class="location-btn" data-location="hanoi">Hanoi</button>
                    </div>
                </div>
                
                <div id="forecast-content"></div>
                
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer
        const countDownDate = new Date("July 31, 2025 00:00:00").getTime();
        const countdownEl = document.getElementById("countdown");
        
        const timer = setInterval(() => {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance < 0) {
                clearInterval(timer);
                countdownEl.textContent = "🎉 The adventure begins!";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            countdownEl.textContent = `Countdown: ${days} days, ${hours} hours, ${minutes} minutes`;
        }, 1000);

        // Enhanced swipe functionality
        const swipeContainer = document.querySelector('.swipe-container');
        const header = document.getElementById('header');
        const headerIndicator = document.getElementById('headerIndicator');
        let isScrolling = false;
        let headerTimeout;
        
        // Header management
        function showHeader() {
            header.classList.add('show');
            headerIndicator.classList.add('hide');
            
            // Auto-hide after 3 seconds
            clearTimeout(headerTimeout);
            headerTimeout = setTimeout(() => {
                hideHeader();
            }, 3000);
        }
        
        function hideHeader() {
            header.classList.remove('show');
            headerIndicator.classList.remove('hide');
            clearTimeout(headerTimeout);
        }
        
        // Touch handling for header reveal
        let touchStartY = 0;
        let touchStartX = 0;
        let touchEndY = 0;
        let isHeaderSwipe = false;
        let headerGestureActive = false;
        
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.changedTouches[0].screenY;
            touchStartX = e.changedTouches[0].screenX;
            // Only consider swipes that start from very top of screen
            isHeaderSwipe = touchStartY < 80;
            headerGestureActive = false;
        });
        
        document.addEventListener('touchmove', (e) => {
            if (!isHeaderSwipe || headerGestureActive) return;
            
            const currentY = e.changedTouches[0].screenY;
            const currentX = e.changedTouches[0].screenX;
            const diffY = currentY - touchStartY;
            const diffX = Math.abs(currentX - touchStartX);
            
            // Only trigger if it's more vertical than horizontal movement
            if (diffY > 40 && diffY > diffX * 2) {
                console.log('Header swipe detected!', diffY); // Debug log
                showHeader();
                headerGestureActive = true;
                isHeaderSwipe = false; // Prevent multiple triggers
                
                // Prevent this touch from affecting horizontal swipes
                e.preventDefault();
            }
        });
        
        document.addEventListener('touchend', (e) => {
            touchEndY = e.changedTouches[0].screenY;
            isHeaderSwipe = false;
            headerGestureActive = false;
        });
        
        // Hide header when clicking outside
        document.addEventListener('click', (e) => {
            if (!header.contains(e.target) && header.classList.contains('show') && !headerIndicator.contains(e.target)) {
                hideHeader();
            }
        });
        
        // Click on indicator to show header
        headerIndicator.addEventListener('click', (e) => {
            e.stopPropagation();
            showHeader();
        });
        
        // Keyboard shortcut (H key) to toggle header
        document.addEventListener('keydown', (e) => {
            if (e.key === 'h' || e.key === 'H') {
                if (header.classList.contains('show')) {
                    hideHeader();
                } else {
                    showHeader();
                }
            }
        });
        
        function snapToNearestPanel() {
            if (isScrolling || window.innerWidth >= 1200) return; // Skip on desktop
            
            const containerWidth = swipeContainer.clientWidth;
            const scrollLeft = swipeContainer.scrollLeft;
            const panelIndex = Math.round(scrollLeft / containerWidth);
            const targetScrollLeft = panelIndex * containerWidth;
            
            swipeContainer.scrollTo({
                left: targetScrollLeft,
                behavior: 'smooth'
            });
        }
        
        if (swipeContainer) {
            swipeContainer.addEventListener('scroll', () => {
                if (window.innerWidth >= 1200) return; // Skip on desktop
                isScrolling = true;
                clearTimeout(swipeContainer.scrollTimeout);
                swipeContainer.scrollTimeout = setTimeout(() => {
                    isScrolling = false;
                    snapToNearestPanel();
                }, 150);
            });
            
            // Touch-friendly navigation between panels
            let panelTouchStartX = 0;
            let panelTouchStartY = 0;
            let panelTouchEndX = 0;
            
            swipeContainer.addEventListener('touchstart', (e) => {
                if (window.innerWidth >= 1200) return; // Skip on desktop
                panelTouchStartX = e.changedTouches[0].screenX;
                panelTouchStartY = e.changedTouches[0].screenY;
            });
            
            swipeContainer.addEventListener('touchend', (e) => {
                if (window.innerWidth >= 1200) return; // Skip on desktop
                panelTouchEndX = e.changedTouches[0].screenX;
                
                // Don't handle horizontal swipes if they started from top (header area)
                if (panelTouchStartY < 80) return;
                
                handlePanelSwipe();
            });
            
            function handlePanelSwipe() {
                const swipeThreshold = 50;
                const diff = panelTouchStartX - panelTouchEndX;
                
                if (Math.abs(diff) > swipeThreshold) {
                    const containerWidth = swipeContainer.clientWidth;
                    const currentPanel = Math.round(swipeContainer.scrollLeft / containerWidth);
                    
                    if (diff > 0 && currentPanel < 1) {
                        // Swipe left - go to weather panel
                        swipeContainer.scrollTo({
                            left: containerWidth,
                            behavior: 'smooth'
                        });
                    } else if (diff < 0 && currentPanel > 0) {
                        // Swipe right - go to navigation panel
                        swipeContainer.scrollTo({
                            left: 0,
                            behavior: 'smooth'
                        });
                    }
                }
            }
        }

        // Weather API Configuration - now using PHP backend
        const locations = {
            hue: { name: "Huế" },
            danang: { name: "Đà Nẵng" },
            hoian: { name: "Hội An" },
            hanoi: { name: "Hà Nội" }
        };

        function fetchForecast(locationKey = 'hue') {
            const location = locations[locationKey];
            
            // Show loading state
            document.getElementById('forecast-content').innerHTML = 
                '<div class="forecast-card"><p>Loading weather data...</p></div>';

            // Call our PHP backend endpoint
            fetch(`api/weather.php?location=${locationKey}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.message || 'Unknown error');
                    }
                    renderForecast(data, location.name);
                })
                .catch(err => {
                    console.error("Weather error:", err);
                    document.getElementById('forecast-content').innerHTML = 
                        `<div class="forecast-card">
                            <p>Unable to load weather data.</p>
                            <p style="font-size: 0.8rem; color: #a1a1aa; margin-top: 0.5rem;">
                                ${err.message || 'Please try again later.'}
                            </p>
                        </div>`;
                });
        }

        function renderForecast(data, locationName) {
            const forecastContentEl = document.getElementById('forecast-content');
            let html = '<div class="forecast-container"><div class="forecast-grid">';
            
            let cardCount = 0;
            data.list.forEach((item, i) => {
                if (i % 8 === 0 && cardCount < 5) { // Limit to exactly 5 cards
                    const date = new Date(item.dt * 1000);
                    const day = date.toLocaleDateString('en-US', { 
                        weekday: 'short', // Shorter day names for compact design
                        month: 'short', 
                        day: 'numeric' 
                    });
                    const description = item.weather[0].description;
                    const temp = Math.round(item.main.temp);
                    
                    // Get weather CSS class based on description
                    const weatherClass = getWeatherClass(description);

                    html += `
                        <div class="forecast-card ${weatherClass}">
                            <div class="forecast-card-header">
                                <div>
                                    <div class="forecast-date">${day}</div>
                                    <div class="forecast-desc">${description}</div>
                                </div>
                                <div class="forecast-temp">${temp}°</div>
                            </div>
                        </div>
                    `;
                    cardCount++;
                }
            });
            
            html += '</div></div>';
            forecastContentEl.innerHTML = html;
        }

        // Helper function to get weather CSS class
        function getWeatherClass(description) {
            const desc = description.toLowerCase();
            
            if (desc.includes('clear')) return 'weather-clear-sky';
            if (desc.includes('few clouds')) return 'weather-few-clouds';
            if (desc.includes('scattered clouds')) return 'weather-scattered-clouds';
            if (desc.includes('broken clouds')) return 'weather-broken-clouds';
            if (desc.includes('overcast')) return 'weather-overcast-clouds';
            if (desc.includes('heavy rain')) return 'weather-heavy-rain';
            if (desc.includes('moderate rain')) return 'weather-moderate-rain';
            if (desc.includes('light rain') || desc.includes('rain')) return 'weather-light-rain';
            if (desc.includes('thunderstorm')) return 'weather-thunderstorm';
            if (desc.includes('mist') || desc.includes('haze') || desc.includes('fog')) return 'weather-fog';
            
            // Default fallback
            return 'weather-clear-sky';
        }

        // Initialize weather forecast
        fetchForecast();

        // Location button functionality
        document.querySelectorAll('.location-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.location-btn').forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Fetch forecast for selected location
                const locationKey = this.getAttribute('data-location');
                fetchForecast(locationKey);
            });
        });
    </script>
</body>
</html>

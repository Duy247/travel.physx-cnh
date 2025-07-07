<?php
// public/index.php

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
    <title>Travel PhysX CNH</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?php echo $cache_bust; ?>">
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cache_bust; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Righteous&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    
    <header class="header" id="header">
        <h1>Hue - Danang - Hoian</h1>
        <h2>July 31 - August 3, 2025 • Central Vietnam</h2>
    </header>

    <div class="swipe-container">
        <div class="swipe-panel nav-panel">
            <div class="nav-section">
                <!-- Navigation Panel Header -->
                <div class="nav-header">
                    <div class="nav-header-content">
                        <h1 class="nav-title">Travel With Friend</h1>
                        <p class="nav-subtitle">Hue - Danang - Hoian</p>
                    </div>
                </div>
                
                <div class="countdown" id="countdown"></div>
                
                <div class="nav-modern-container">
                    <div class="nav-scroll-area" id="navScrollArea">
                        <div class="nav-spacer-top"></div>
                        <a href="Map.php" class="nav-item" data-nav="map">
                            <span>Travel Map</span>
                        </a>
                        <a href="Weather.php" class="nav-item" data-nav="weather">
                            <span>Weather Map</span>
                        </a>
                        <a href="Plan.php" class="nav-item" data-nav="timeline">
                            <span>Timeline</span>
                        </a>
                        <a href="Spending.php" class="nav-item" data-nav="budget">
                            <span>Budget</span>
                        </a>
                        <a href="PackingList.php" class="nav-item" data-nav="packing">
                            <span>Packing List</span>
                        </a>
                        <a href="PersonalPack.php" class="nav-item" data-nav="personal">
                            <span>Personal Items</span>
                        </a>
                        <a href="Info.php" class="nav-item" data-nav="info">
                            <span>Emergency Info</span>
                        </a>
                        <a href="Gallery.php" class="nav-item" data-nav="gallery">
                            <span>Gallery</span>
                        </a>
                        <div class="nav-spacer-bottom"></div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="swipe-panel weather-panel">
            <div class="weather-section">
                <div class="location-selector">
                    <div class="location-buttons">
                        <button class="location-btn active" data-location="hue">Huế</button>
                        <button class="location-btn" data-location="danang">Đà Nẵng</button>
                        <button class="location-btn" data-location="hoian">Hội An</button>
                        <button class="location-btn" data-location="hanoi">Hà Nội</button>
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
                countdownEl.textContent = "Let's go, it's time!";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

            countdownEl.textContent = `Countdown`;
            countdownEl.innerHTML += `<br>${days} days, ${hours} hours, ${minutes} minutes`;
        }, 1000);

        // Enhanced swipe functionality
        const swipeContainer = document.querySelector('.swipe-container');
        const header = document.getElementById('header');
        let isScrolling = false;
        let headerTimeout;
        
        // Header management
        function showHeader() {
            header.classList.add('show');
            
            // Auto-hide after 3 seconds
            clearTimeout(headerTimeout);
            headerTimeout = setTimeout(() => {
                hideHeader();
            }, 3000);
        }
        
        function hideHeader() {
            header.classList.remove('show');
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
                //e.preventDefault();
            }
        });
        
        document.addEventListener('touchend', (e) => {
            touchEndY = e.changedTouches[0].screenY;
            isHeaderSwipe = false;
            headerGestureActive = false;
        });
        
        // Hide header when clicking outside
        document.addEventListener('click', (e) => {
            if (!header.contains(e.target) && header.classList.contains('show')) {
                hideHeader();
            }
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

            // Call our PHP backend endpoint with cache busting
            const cacheBust = new Date().getTime();
            fetch(`api/weather.php?location=${locationKey}&_=${cacheBust}`)
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

        // Enhanced Center-Focused Navigation System
        class CenterFocusedNavigation {
            constructor() {
                this.scrollArea = document.getElementById('navScrollArea');
                this.navItems = document.querySelectorAll('.nav-item');
                this.centerY = null;
                this.isInitialized = false;
                this.isScrolling = false;
                this.scrollTimeout = null;
                this.rafId = null;
                this.lastFocusedIndex = -1;
                this.focusThreshold = 50; // Minimum distance change to trigger focus update
                
                if (this.scrollArea && this.navItems.length > 0) {
                    this.init();
                }
            }

            init() {
                // Calculate center position with better precision
                this.updateCenterPosition();
                
                // Add optimized scroll event listener with passive scrolling
                this.scrollArea.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
                
                // Handle resize events
                window.addEventListener('resize', this.debounce(this.updateCenterPosition.bind(this), 250));
                
                // Initial setup - scroll to center the "Travel Map" item (data-nav="map")
                setTimeout(() => {
                    const mapItemIndex = Array.from(this.navItems).findIndex(item => 
                        item.getAttribute('data-nav') === 'map'
                    );
                    if (mapItemIndex !== -1) {
                        this.scrollToCenter(mapItemIndex, false); // Instant scroll for initial setup
                    } else {
                        this.scrollToCenter(0, false); // Fallback to first item
                    }
                }, 100);
                
                // Add enhanced click handlers for navigation items
                this.navItems.forEach((item, index) => {
                    if (item.href) { // Only add click handler to actual navigation items
                        item.addEventListener('click', (e) => {
                            //e.preventDefault();
                            this.handleItemClick(index, item.href);
                        });
                    }
                });
                
                this.isInitialized = true;
                // Use RAF for initial focus update
                this.requestFocusUpdate();
            }

            updateCenterPosition() {
                if (!this.scrollArea) return;
                const containerRect = this.scrollArea.getBoundingClientRect();
                this.centerY = containerRect.height / 2;
            }

            handleScroll() {
                if (!this.isInitialized) return;
                
                this.isScrolling = true;
                
                // Clear existing timeout
                if (this.scrollTimeout) {
                    clearTimeout(this.scrollTimeout);
                }
                
                // Use requestAnimationFrame for smooth updates
                this.requestFocusUpdate();
                
                // Set scroll end timeout
                this.scrollTimeout = setTimeout(() => {
                    this.isScrolling = false;
                    this.requestFocusUpdate(); // Final update when scrolling stops
                }, 100);
            }

            requestFocusUpdate() {
                if (this.rafId) {
                    cancelAnimationFrame(this.rafId);
                }
                
                this.rafId = requestAnimationFrame(() => {
                    this.updateCenterFocus();
                    this.rafId = null;
                });
            }

            updateCenterFocus() {
                if (!this.scrollArea || !this.isInitialized) return;
                
                const containerRect = this.scrollArea.getBoundingClientRect();
                const centerY = containerRect.top + this.centerY;
                
                let closestItem = null;
                let closestIndex = -1;
                let closestDistance = Infinity;
                
                // Calculate distances more precisely
                this.navItems.forEach((item, index) => {
                    const itemRect = item.getBoundingClientRect();
                    const itemCenterY = itemRect.top + (itemRect.height / 2);
                    const distance = Math.abs(centerY - itemCenterY);
                    
                    if (distance < closestDistance) {
                        closestDistance = distance;
                        closestItem = item;
                        closestIndex = index;
                    }
                });
                
                // Only update if there's a significant change or different item
                if (closestIndex !== this.lastFocusedIndex || closestDistance < this.focusThreshold) {
                    this.updateItemStates(closestIndex);
                    this.lastFocusedIndex = closestIndex;
                }
            }

            updateItemStates(centerIndex) {
                // Remove all existing focus states
                this.navItems.forEach((item, index) => {
                    item.classList.remove('center-focused');
                    
                    // Add center focus only to the centered item
                    if (index === centerIndex) {
                        item.classList.add('center-focused');
                    }
                });
            }

            scrollToCenter(index, smooth = true) {
                if (index < 0 || index >= this.navItems.length) return;
                
                const targetItem = this.navItems[index];
                if (!targetItem) return;
                
                // Get current scroll position and target item position
                const scrollTop = this.scrollArea.scrollTop;
                const containerHeight = this.scrollArea.clientHeight;
                
                // Calculate target item's position relative to the scroll container
                const itemOffsetTop = targetItem.offsetTop;
                const itemHeight = targetItem.offsetHeight;
                
                // Calculate the scroll position to center the item
                const targetScrollTop = itemOffsetTop - (containerHeight / 2) + (itemHeight / 2);
                
                // Ensure we don't scroll beyond bounds
                const maxScroll = this.scrollArea.scrollHeight - containerHeight;
                const finalScrollTop = Math.max(0, Math.min(targetScrollTop, maxScroll));
                
                // Scroll to position
                this.scrollArea.scrollTo({
                    top: finalScrollTop,
                    behavior: smooth ? 'smooth' : 'auto'
                });
                
                // Update focus immediately for better responsiveness
                if (!smooth) {
                    setTimeout(() => this.updateItemStates(index), 50);
                }
            }

            handleItemClick(index, href) {
                // Center the item first with smooth scrolling
                this.scrollToCenter(index, true);
                
                // Navigate after centering animation completes
                setTimeout(() => {
                    window.location.href = href;
                }, 400); // Slightly longer delay for smoother experience
            }

            // Public method to programmatically focus an item
            focusItem(index) {
                this.scrollToCenter(index, true);
            }

            // Utility function for debouncing
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // Cleanup method
            destroy() {
                if (this.rafId) {
                    cancelAnimationFrame(this.rafId);
                }
                if (this.scrollTimeout) {
                    clearTimeout(this.scrollTimeout);
                }
            }
        }

        // Initialize the enhanced center-focused navigation
        const centerNav = new CenterFocusedNavigation();

        // Enhanced keyboard navigation support
        document.addEventListener('keydown', (e) => {
            if (!centerNav.isInitialized) return;
            
            const currentFocused = document.querySelector('.nav-item.center-focused');
            if (!currentFocused) return;
            
            const currentIndex = Array.from(centerNav.navItems).indexOf(currentFocused);
            
            switch(e.key) {
                case 'ArrowUp':
                    //e.preventDefault();
                    if (currentIndex > 0) {
                        centerNav.focusItem(currentIndex - 1);
                    }
                    break;
                case 'ArrowDown':
                    //e.preventDefault();
                    if (currentIndex < centerNav.navItems.length - 1) {
                        centerNav.focusItem(currentIndex + 1);
                    }
                    break;
                case 'Enter':
                case ' ': // Add spacebar support
                    //e.preventDefault();
                    if (currentFocused.href) {
                        centerNav.handleItemClick(currentIndex, currentFocused.href);
                    }
                    break;
                case 'Home':
                    //e.preventDefault();
                    const mapItemIndex = Array.from(centerNav.navItems).findIndex(item => 
                        item.getAttribute('data-nav') === 'map'
                    );
                    if (mapItemIndex !== -1) {
                        centerNav.focusItem(mapItemIndex);
                    } else {
                        centerNav.focusItem(0); // Fallback to first item
                    }
                    break;
                case 'End':
                    //e.preventDefault();
                    centerNav.focusItem(centerNav.navItems.length - 1);
                    break;
            }
        });
    </script>
    
    <!-- Cache Busting Script -->
    <script src="js/cache-buster.js?v=<?php echo $cache_bust; ?>"></script>
</body>
</html>

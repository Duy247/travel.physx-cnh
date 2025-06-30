// Map.js - Interactive Travel Map Functionality with Enhanced UI and Real Road Navigation

// ROUTING CONFIGURATION - Loaded from server
let ROUTING_CONFIG = {
    openrouteservice: {
        apiKey: '', // Will be loaded from server
        maxRequests: 2000,
        endpoint: 'https://api.openrouteservice.org/v2/directions/driving-car'
    }
};

class TravelMap {
    constructor() {
        this.map = null;
        this.currentLayer = null;
        this.currentKmlType = 'all';
        this.markerCount = 0;
        this.kmlStyles = {}; // Store parsed KML styles
        this.kmlFiles = {
            'all': 'data/kml/Map.kml',
            'breakfast': 'data/kml/Breakfast.kml',
            'lunch-dinner': 'data/kml/LunchDinner.kml',
            'snack-night': 'data/kml/Junk.kml',
            'coffee': 'data/kml/Cafe.kml',
            'tour': 'data/kml/Touring.kml',
            'fuel': 'data/kml/Fuel.kml',
            'people': 'api/people.php' // Special endpoint for people data
        };
        this.categoryColors = {
            'breakfast': 'breakfast',
            'lunch-dinner': 'lunch-dinner',
            'snack-night': 'snack-night',
            'coffee': 'coffee',
            'tour': 'tour',
            'fuel': 'fuel',
            'people': 'people'
        };
        // Navigation system properties
        this.userLocation = null;
        this.navigationActive = false;
        this.navigationTarget = null;
        this.navigationWatchId = null;
        this.routeLayer = null;
        this.userMarker = null;
        this.locationUpdateInterval = null;
        this.init();
    }

    init() {
        // Load configuration first, then initialize map
        this.loadConfiguration().then(() => {
            this.initMap();
            this.addControls();
            this.setupEventListeners();
            this.loadDefaultKML();
            this.showUIElements();
        }).catch(error => {
            console.error('Failed to load configuration:', error);
            this.showError('Failed to load application configuration. Some features may not work properly.');
            // Continue with initialization even if config fails
            this.initMap();
            this.addControls();
            this.setupEventListeners();
            this.loadDefaultKML();
            this.showUIElements();
        });
    }

    async loadConfiguration() {
        try {
            const response = await fetch('api/config.php');
            if (!response.ok) {
                throw new Error(`Config API responded with status: ${response.status}`);
            }
            const config = await response.json();
            
            // Update global routing configuration
            if (config.routing && config.routing.openrouteservice) {
                ROUTING_CONFIG.openrouteservice = {
                    ...ROUTING_CONFIG.openrouteservice,
                    ...config.routing.openrouteservice
                };
            }
            
            console.log('Configuration loaded successfully');
        } catch (error) {
            console.error('Error loading configuration:', error);
            throw error;
        }
    }

    initMap() {
        // Initialize map centered on Central Vietnam
        this.map = L.map('map').setView([16.0471, 108.2068], 10);

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(this.map);

        // Map event listeners
        this.map.on('zoomend', () => {
            this.updateZoomLevel();
        });

        this.map.on('moveend', () => {
            this.updateMapStats();
        });
    }

    addControls() {
        // Add locate control
        const locateControl = L.control.locate({
            position: 'topright',
            drawCircle: true,
            flyTo: true,
            strings: {
                title: "Show me where I am",
                popup: "You are within {distance} {unit} from this point"
            },
            locateOptions: {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        }).addTo(this.map);

        // Add event listener to locate control for location sharing
        this.map.on('locationfound', (e) => {
            // Save current location when locate button is used
            this.saveUserLocation(e.latlng.lat, e.latlng.lng);
        });

        // Add scale control
        L.control.scale({
            position: 'bottomleft'
        }).addTo(this.map);
    }

    setupEventListeners() {
        // Setup legend item listeners for KML switching
        document.querySelectorAll('.map-legend-item').forEach((item, index) => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                const kmlType = item.getAttribute('data-kml');
                if (kmlType) {
                    await this.loadKML(kmlType);
                    this.setActiveLegendItem(item);
                    this.currentKmlType = kmlType;
                    this.updateActiveCategory();
                }
            });

            // Add hover sound effect (optional)
            item.addEventListener('mouseenter', () => {
                // Could add subtle audio feedback here
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            switch(e.key) {
                case '1': this.loadKMLByNumber(0); break;
                case '2': this.loadKMLByNumber(1); break;
                case '3': this.loadKMLByNumber(2); break;
                case '4': this.loadKMLByNumber(3); break;
                case '5': this.loadKMLByNumber(4); break;
                case '6': this.loadKMLByNumber(5); break;
                case '7': this.loadKMLByNumber(6); break;
                case 'l': this.toggleLegend(); break;
                case 's': this.toggleStats(); break;
                case 'Escape': window.history.back(); break;
            }
        });

        // Back button functionality
        const backButton = document.querySelector('.back-button');
        if (backButton) {
            backButton.addEventListener('click', () => {
                window.history.back();
            });
        }
    }

    loadKMLByNumber(index) {
        const legendItems = document.querySelectorAll('.map-legend-item');
        if (legendItems[index]) {
            legendItems[index].click();
        }
    }

    showUIElements() {
        // Show stats and legend immediately since legend is now interactive
        setTimeout(() => {
            const stats = document.getElementById('mapStats');
            const legend = document.getElementById('mapLegend');
            if (stats) stats.classList.add('show');
            if (legend) legend.classList.add('show');
        }, 500);
    }

    updateActiveCategory() {
        const categoryElement = document.getElementById('activeCategory');
        if (categoryElement) {
            const categoryNames = {
                'all': 'All Locations',
                'breakfast': 'Breakfast',
                'lunch-dinner': 'Lunch & Dinner',
                'snack-night': 'Snack & Night',
                'coffee': 'Coffee Shops',
                'tour': 'Tour Points',
                'fuel': 'Fuel Stations',
                'people': 'People Locations'
            };
            categoryElement.textContent = categoryNames[this.currentKmlType] || 'Unknown';
        }
    }

    updateZoomLevel() {
        const zoomElement = document.getElementById('currentZoom');
        if (zoomElement && this.map) {
            zoomElement.textContent = this.map.getZoom();
        }
    }

    updateMapStats() {
        this.updateZoomLevel();
        // Update marker count if needed
        if (this.currentLayer) {
            let count = 0;
            this.currentLayer.eachLayer(() => count++);
            this.markerCount = count;
            
            const markerElement = document.getElementById('markerCount');
            if (markerElement) {
                markerElement.textContent = count;
            }
        }
    }

    toggleStats() {
        const stats = document.getElementById('mapStats');
        if (stats) {
            stats.classList.toggle('show');
        }
    }

    toggleLegend() {
        const legend = document.getElementById('mapLegend');
        if (legend) {
            legend.classList.toggle('show');
        }
    }

    async loadKML(kmlType) {
        const kmlFile = this.kmlFiles[kmlType];
        if (!kmlFile) {
            console.error('KML file not found for type:', kmlType);
            return;
        }

        this.showLoading(true);

        try {
            // Show/hide refresh button based on category
            this.showRefreshButton(kmlType === 'people');

            // Handle special case for people data
            if (kmlType === 'people') {
                await this.loadPeopleData();
                return;
            }

            // Parse KML styles first
            await this.parseKMLStyles(kmlFile);

            // Remove existing layer
            if (this.currentLayer) {
                this.map.removeLayer(this.currentLayer);
            }

            // Fetch and parse KML manually
            const response = await fetch(kmlFile);
            const kmlText = await response.text();
            const parser = new DOMParser();
            const kmlDoc = parser.parseFromString(kmlText, 'text/xml');

            // Create a new layer group for our custom markers
            this.currentLayer = L.layerGroup();

            // Extract placemarks and create custom markers
            const placemarks = kmlDoc.querySelectorAll('Placemark');
            const bounds = L.latLngBounds();

            placemarks.forEach(placemark => {
                const name = placemark.querySelector('name')?.textContent || 'Unnamed Location';
                const description = placemark.querySelector('description')?.textContent || '';
                const styleUrl = placemark.querySelector('styleUrl')?.textContent || '';
                const coordinates = placemark.querySelector('Point coordinates')?.textContent;

                if (coordinates) {
                    const [lng, lat] = coordinates.trim().split(',').map(Number);
                    const latLng = L.latLng(lat, lng);
                    bounds.extend(latLng);

                    // Get color based on style
                    let iconColor = this.getIconColorFromStyle(styleUrl) || this.categoryColors[this.currentKmlType];
                    const categoryClass = iconColor || '';
                    const customColor = this.getCSSColorFromCategory(categoryClass);

                    // Get marker sizing with fixed 0.5 scale for accurate positioning
                    const markerSizing = this.getMarkerSizeForZoom();

                    // Create custom marker with optimized sizing
                    const marker = L.marker(latLng, {
                        icon: L.divIcon({
                            className: 'custom-div-icon',
                            html: `
                                <div class="marker-container ${categoryClass}">
                                    <div class="marker-label">${this.truncateName(name)}</div>
                                    <div class="custom-marker-pin" style="--marker-color: ${customColor.primary}; --marker-solid-color: ${customColor.solid}; --marker-shadow: ${customColor.shadow};">
                                        <div class="pin-head"></div>
                                        <div class="pin-point"></div>
                                    </div>
                                </div>
                            `,
                            iconSize: markerSizing.iconSize,
                            iconAnchor: markerSizing.iconAnchor,
                            popupAnchor: markerSizing.popupAnchor
                        })
                    });

                    // Add popup
                    let popupContent = `<h3>${name}</h3>`;
                    if (description) {
                        popupContent += `<p>${description}</p>`;
                    }
                    popupContent += `<p style="font-size: 0.8rem; color: #71717a; margin-top: 0.5rem;">

                    </p>`;
                    popupContent += `
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e4e4e7;">
                            <button onclick="window.travelMap.showBatteryWarning({name: '${name.replace(/'/g, "\\'")}', coordinates: L.latLng(${lat}, ${lng})})" 
                                    style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; transition: all 0.2s ease;">
                                Navigate Here
                            </button>
                        </div>
                    `;

                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'custom-popup'
                    });

                    // Add navigation click event
                    marker.on('dblclick', (e) => {
                        // Double-click to start navigation directly
                        e.originalEvent.stopPropagation();
                        
                        // Show battery warning modal before starting navigation
                        this.showBatteryWarning({
                            name: name,
                            coordinates: latLng,
                            description: description
                        });
                    });
                    
                    // Add hover tooltip for navigation hint
                    marker.on('mouseover', (e) => {
                        const markerElement = e.target._icon;
                        if (markerElement) {
                            markerElement.title = `${name} - Click for details, Double-click to navigate`;
                        }
                    });

                    // Add to layer
                    this.currentLayer.addLayer(marker);
                }
            });

            // Add layer to map
            this.currentLayer.addTo(this.map);

            // Fit bounds
            if (bounds.isValid()) {
                this.map.fitBounds(bounds, { padding: [20, 20] });
            }

            console.log('KML loaded successfully with custom markers:', kmlFile);
            this.showLoading(false);
            this.updateMapStats();

        } catch (error) {
            console.error('Error loading KML:', error);
            this.showError('Failed to load map data. Please try again.');
            this.showLoading(false);
        }
    }

    // Method to get optimized marker size with fixed scale for accurate positioning
    getMarkerSizeForZoom() {
        // Use fixed 0.5 scale for perfect positioning accuracy at all zoom levels
        const scale = 0.5;
        const baseWidth = 120;
        const baseHeight = 80;
        
        const scaledWidth = Math.round(baseWidth * scale);
        const scaledHeight = Math.round(baseHeight * scale);
        
        // The anchor point should be at the tip of the pin
        // For our design, the pin tip is at the bottom center
        const anchorX = Math.round(scaledWidth / 2);
        const anchorY = Math.round(scaledHeight * 0.875); // 87.5% down to hit the pin tip
        
        return {
            iconSize: [scaledWidth, scaledHeight],
            iconAnchor: [anchorX, anchorY],
            popupAnchor: [0, -anchorY]
        };
    }

    // Helper method to truncate long names for labels
    truncateName(name) {
        if (!name) return 'Unnamed Location';
        
        // Clean up name by removing common prefixes/suffixes
        let cleanName = name.trim();
        
        // Remove common Vietnamese business prefixes
        cleanName = cleanName.replace(/^(Nhà hàng|Quán|Cơm|Phở|Bún|Khách sạn|Hotel)\s+/i, '');
        
        // Truncate if still too long
        if (cleanName.length > 18) {
            return cleanName.substring(0, 15) + '...';
        }
        return cleanName;
    }

    // Helper method to extract color information from KML style
    getIconColorFromStyle(styleUrl) {
        // Map Google Earth colors to our category styles
        const colorMap = {
            'd32f2f': 'lunch-dinner',  // Red - lunch/dinner
            'dd4b39': 'lunch-dinner',  // Alternative red
            '66bb6a': 'breakfast',     // Green - breakfast  
            '4caf50': 'breakfast',     // Alternative green
            'ff9800': 'coffee',        // Orange - coffee
            'ff8f00': 'coffee',        // Alternative orange
            '9c27b0': 'snack-night',   // Purple - snack/night
            '8e24aa': 'snack-night',   // Alternative purple
            '2196f3': 'tour',          // Blue - tour
            '1976d2': 'tour',          // Alternative blue
            'ff5722': 'fuel',          // Deep orange - fuel
            'f4511e': 'fuel'           // Alternative deep orange
        };
        
        if (styleUrl) {
            // Clean the styleUrl (remove # prefix)
            const styleId = styleUrl.replace('#', '');
            
            // Check if we have parsed this style
            if (this.kmlStyles[styleId]) {
                const color = this.kmlStyles[styleId];
                if (colorMap[color]) {
                    return colorMap[color];
                }
            }
            
            // Fallback: Extract the color parameter from Google Earth icon URLs in styleUrl
            const colorMatch = styleUrl.match(/color=([a-fA-F0-9]{6})/);
            if (colorMatch) {
                const extractedColor = colorMatch[1].toLowerCase();
                if (colorMap[extractedColor]) {
                    return colorMap[extractedColor];
                }
            }
            
            // Fallback: check if any known color is in the styleUrl
            for (const [color, category] of Object.entries(colorMap)) {
                if (styleUrl.toLowerCase().includes(color)) {
                    return category;
                }
            }
        }
        
        // If no specific style found, determine from current KML type
        // This ensures markers still get appropriate colors even if style parsing fails
        if (this.currentKmlType && this.currentKmlType !== 'all') {
            return this.categoryColors[this.currentKmlType];
        }
        
        // Ultimate fallback based on file name patterns
        if (this.currentLayer && this.currentLayer._url) {
            const fileName = this.currentLayer._url.toLowerCase();
            if (fileName.includes('lunch') || fileName.includes('dinner')) return 'lunch-dinner';
            if (fileName.includes('breakfast')) return 'breakfast';
            if (fileName.includes('coffee') || fileName.includes('cafe')) return 'coffee';
            if (fileName.includes('junk') || fileName.includes('snack')) return 'snack-night';
            if (fileName.includes('tour')) return 'tour';
            if (fileName.includes('fuel')) return 'fuel';
        }
        
        return '';
    }

    // Convert category to CSS colors
    getCSSColorFromCategory(category) {
        const colorSchemes = {
            'breakfast': {
                primary: 'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)',
                solid: '#f59e0b',
                shadow: 'rgba(245, 158, 11, 0.4)'
            },
            'lunch-dinner': {
                primary: 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                solid: '#ef4444',
                shadow: 'rgba(239, 68, 68, 0.4)'
            },
            'snack-night': {
                primary: 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
                solid: '#8b5cf6',
                shadow: 'rgba(139, 92, 246, 0.4)'
            },
            'coffee': {
                primary: 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
                solid: '#06b6d4',
                shadow: 'rgba(6, 182, 212, 0.4)'
            },
            'tour': {
                primary: 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                solid: '#10b981',
                shadow: 'rgba(16, 185, 129, 0.4)'
            },
            'fuel': {
                primary: 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)',
                solid: '#f97316',
                shadow: 'rgba(249, 115, 22, 0.4)'
            },
            'people': {
                primary: 'linear-gradient(135deg, #ec4899 0%, #be185d 100%)',
                solid: '#ec4899',
                shadow: 'rgba(236, 72, 153, 0.4)'
            }
        };
        
        return colorSchemes[category] || {
            primary: 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
            solid: '#6366f1',
            shadow: 'rgba(99, 102, 241, 0.4)'
        };
    }

    async loadDefaultKML() {
        await this.loadKML('all');
        // Set first legend item as active
        const firstLegendItem = document.querySelector('.map-legend-item[data-kml="all"]');
        if (firstLegendItem) {
            this.setActiveLegendItem(firstLegendItem);
        }
        this.updateActiveCategory();
    }

    setActiveLegendItem(activeLegendItem) {
        // Remove active class from all legend items
        document.querySelectorAll('.map-legend-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked legend item
        activeLegendItem.classList.add('active');
        
        // Update current KML type
        this.currentKmlType = activeLegendItem.getAttribute('data-kml');
    }

    // Legacy method for backward compatibility
    setActiveButton(activeButton) {
        // This method is kept for any legacy calls but redirects to legend
        const kmlType = activeButton.getAttribute('data-kml');
        const legendItem = document.querySelector(`.map-legend-item[data-kml="${kmlType}"]`);
        if (legendItem) {
            this.setActiveLegendItem(legendItem);
        }
    }

    showLoading(show) {
        const loadingEl = document.getElementById('mapLoading');
        if (loadingEl) {
            if (show) {
                loadingEl.classList.add('show');
            } else {
                loadingEl.classList.remove('show');
            }
        }
    }

    showError(message) {
        // Create enhanced error toast
        const errorToast = document.createElement('div');
        errorToast.className = 'map-error-toast';
        errorToast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="font-size: 1.2rem;">⚠️</div>
                <div>
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">Error</div>
                    <div style="font-size: 0.85rem; opacity: 0.9;">${message}</div>
                </div>
            </div>
        `;
        
        document.body.appendChild(errorToast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (errorToast.parentNode) {
                errorToast.parentNode.removeChild(errorToast);
            }
        }, 5000);
    }

    // Parse KML file to extract style information
    async parseKMLStyles(kmlFile) {
        try {
            const response = await fetch(kmlFile);
            const kmlText = await response.text();
            const parser = new DOMParser();
            const kmlDoc = parser.parseFromString(kmlText, 'text/xml');
            
            // Parse CascadingStyle elements
            const cascadingStyles = kmlDoc.querySelectorAll('gx\\:CascadingStyle, CascadingStyle');
            cascadingStyles.forEach(style => {
                const id = style.getAttribute('kml:id') || style.getAttribute('id');
                const iconElement = style.querySelector('IconStyle Icon href');
                if (id && iconElement) {
                    const href = iconElement.textContent;
                    const colorMatch = href.match(/color=([a-fA-F0-9]{6})/);
                    if (colorMatch) {
                        this.kmlStyles[id] = colorMatch[1].toLowerCase();
                    }
                }
            });
            
            // Parse regular Style elements
            const styles = kmlDoc.querySelectorAll('Style');
            styles.forEach(style => {
                const id = style.getAttribute('id');
                const iconElement = style.querySelector('IconStyle Icon href');
                if (id && iconElement) {
                    const href = iconElement.textContent;
                    const colorMatch = href.match(/color=([a-fA-F0-9]{6})/);
                    if (colorMatch) {
                        this.kmlStyles[id] = colorMatch[1].toLowerCase();
                    }
                }
            });
            
            // Parse StyleMap elements
            const styleMaps = kmlDoc.querySelectorAll('StyleMap');
            styleMaps.forEach(styleMap => {
                const id = styleMap.getAttribute('id');
                // Find the normal style reference
                const pairs = styleMap.querySelectorAll('Pair');
                pairs.forEach(pair => {
                    const key = pair.querySelector('key');
                    const styleUrl = pair.querySelector('styleUrl');
                    if (key && styleUrl && key.textContent === 'normal') {
                        const normalStyleUrl = styleUrl.textContent.replace('#', '');
                        if (this.kmlStyles[normalStyleUrl]) {
                            this.kmlStyles[id] = this.kmlStyles[normalStyleUrl];
                        }
                    }
                });
            });
            
            console.log('Parsed KML styles:', this.kmlStyles);
        } catch (error) {
            console.error('Error parsing KML styles:', error);
        }
    }

    // Public method to programmatically load KML
    async loadKMLByType(kmlType) {
        await this.loadKML(kmlType);
        // Find and activate corresponding legend item
        const legendItem = document.querySelector(`.map-legend-item[data-kml="${kmlType}"]`);
        if (legendItem) {
            this.setActiveLegendItem(legendItem);
        }
    }

    // Public method to get map instance
    getMap() {
        return this.map;
    }

    // Public method to get current stats
    getStats() {
        return {
            activeCategory: this.currentKmlType,
            markerCount: this.markerCount,
            currentZoom: this.map ? this.map.getZoom() : 0,
            mapCenter: this.map ? this.map.getCenter() : null
        };
    }

    // Navigation System Methods
    
    showBatteryWarning(destination) {
        const overlay = document.createElement('div');
        overlay.className = 'battery-warning-overlay';
        overlay.innerHTML = `
            <div class="battery-warning-modal">
                <div class="battery-warning-icon">🔋⚠️</div>
                <h3 class="battery-warning-title">Battery Usage Warning</h3>
                <p class="battery-warning-text">
                    GPS navigation with live tracking will significantly drain your device's battery. 
                    Location updates occur every 10 seconds for accurate routing to <strong>${destination.name}</strong>.
                    <br><br>
                    Consider keeping your device plugged in during navigation.
                </p>
                <div class="battery-warning-actions">
                    <button class="battery-warning-btn cancel">Cancel</button>
                    <button class="battery-warning-btn proceed">Start Navigation</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        setTimeout(() => overlay.classList.add('show'), 10);
        
        // Handle button clicks
        overlay.querySelector('.cancel').addEventListener('click', () => {
            this.closeBatteryWarning(overlay);
        });
        
        overlay.querySelector('.proceed').addEventListener('click', () => {
            this.closeBatteryWarning(overlay);
            this.startNavigation(destination.name, destination.coordinates);
        });
        
        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                this.closeBatteryWarning(overlay);
            }
        });
        
        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                this.closeBatteryWarning(overlay);
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }
    
    closeBatteryWarning(overlay) {
        overlay.classList.remove('show');
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
    }
    
    async startNavigation(destination, coordinates) {
        try {
            // Ensure configuration is loaded before starting navigation
            if (!ROUTING_CONFIG.openrouteservice.apiKey) {
                console.log('Configuration not ready, loading...');
                try {
                    await this.loadConfiguration();
                } catch (configError) {
                    console.warn('Failed to load configuration for navigation:', configError);
                    this.showError('Configuration not available. Navigation will use fallback routing.');
                }
            }
            
            // Stop any existing navigation first
            if (this.navigationActive) {
                this.stopNavigation();
            }
            
            // Check if geolocation is supported
            if (!navigator.geolocation) {
                this.showError('Geolocation is not supported by this browser.');
                return;
            }
            
            // Get initial user location
            const position = await this.getCurrentPosition();
            this.userLocation = L.latLng(position.coords.latitude, position.coords.longitude);
            
            // Set navigation target
            this.navigationTarget = {
                name: destination,
                coordinates: coordinates
            };
            
            // Add user marker
            this.addUserMarker();
            
            // Start continuous location tracking
            this.startLocationTracking();
            
            // Calculate initial route
            await this.calculateRoute();
            
            // Show navigation panel
            this.showNavigationPanel();
            
            // Show navigation status
            this.showNavigationStatus('Navigation Active');
            
            this.navigationActive = true;
            
        } catch (error) {
            console.error('Navigation start error:', error);
            this.showError('Failed to start navigation. Please check your location settings.');
        }
    }
    
    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            });
        });
    }
    
    addUserMarker() {
        if (this.userMarker) {
            this.map.removeLayer(this.userMarker);
        }
        
        this.userMarker = L.marker(this.userLocation, {
            icon: L.divIcon({
                className: 'user-location-marker',
                html: `
                    <div style="
                        width: 20px; 
                        height: 20px; 
                        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
                        border: 3px solid white; 
                        border-radius: 50%; 
                        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
                        animation: pulse 2s infinite;
                    "></div>
                `,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            })
        }).addTo(this.map);
        
        this.userMarker.bindPopup('Your Current Location', {
            className: 'custom-popup'
        });
    }
    
    startLocationTracking() {
        // Update location every 10 seconds to save API quota
        this.locationUpdateInterval = setInterval(async () => {
            if (!this.navigationActive) return;
            
            try {
                const position = await this.getCurrentPosition();
                const newLocation = L.latLng(position.coords.latitude, position.coords.longitude);
                
                // Update user location
                this.userLocation = newLocation;
                
                // Update user marker
                if (this.userMarker) {
                    this.userMarker.setLatLng(newLocation);
                }
                
                // Recalculate route
                await this.calculateRoute();
                
                console.log('Location updated:', newLocation);
                
            } catch (error) {
                console.error('Location update error:', error);
            }
        }, 10000); // 10 seconds to save API quota
    }
    
    async calculateRoute() {
        if (!this.userLocation || !this.navigationTarget) return;
        
        try {
            // Remove existing route
            if (this.routeLayer) {
                this.map.removeLayer(this.routeLayer);
            }
            
            // Use OpenRouteService for real road-based routing
            const startCoords = [this.userLocation.lng, this.userLocation.lat];
            const endCoords = [this.navigationTarget.coordinates.lng, this.navigationTarget.coordinates.lat];
            
            // OpenRouteService API endpoint (free tier: 2000 requests/day)
            const config = ROUTING_CONFIG.openrouteservice;
            
            // Check if API key is available
            if (!config.apiKey) {
                console.warn('OpenRouteService API key not configured, using fallback route');
                throw new Error('API key not configured');
            }
            
            const url = `${config.endpoint}?api_key=${config.apiKey}&start=${startCoords[0]},${startCoords[1]}&end=${endCoords[0]},${endCoords[1]}`;
            
            try {
                console.log('Fetching route from OpenRouteService...');
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.features && data.features.length > 0) {
                    // Extract route coordinates
                    const routeCoords = data.features[0].geometry.coordinates;
                    const latLngRoute = routeCoords.map(coord => [coord[1], coord[0]]); // Convert to Leaflet format [lat, lng]
                    
                    // Create polyline for the route
                    this.routeLayer = L.polyline(latLngRoute, {
                        color: '#3b82f6',
                        weight: 5,
                        opacity: 0.8,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }).addTo(this.map);
                    
                    // Get route properties
                    const routeProps = data.features[0].properties;
                    const distanceKm = (routeProps.segments[0].distance / 1000).toFixed(2);
                    const durationMin = Math.round(routeProps.segments[0].duration / 60);
                    
                    // Update navigation panel with real route data
                    this.updateNavigationPanel(distanceKm, durationMin);
                    
                    console.log(`Route calculated: ${distanceKm}km, ${durationMin} minutes`);
                    
                } else {
                    throw new Error('No route found');
                }
                
            } catch (apiError) {
                console.warn('OpenRouteService API failed, falling back to straight line:', apiError);
                
                // Fallback to straight line if API fails
                const route = [this.userLocation, this.navigationTarget.coordinates];
                
                this.routeLayer = L.polyline(route, {
                    color: '#f59e0b', // Orange color to indicate fallback
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '10, 5',
                    lineCap: 'round',
                    lineJoin: 'round'
                }).addTo(this.map);
                
                // Calculate straight-line distance
                const distance = this.userLocation.distanceTo(this.navigationTarget.coordinates);
                const distanceKm = (distance / 1000).toFixed(2);
                
                // Update navigation panel with fallback info
                this.updateNavigationPanel(distanceKm, null, true);
            }
            
        } catch (error) {
            console.error('Route calculation error:', error);
            this.showError('Failed to calculate route. Please try again.');
        }
    }
    
    showNavigationPanel() {
        let panel = document.getElementById('navigationPanel');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'navigationPanel';
            panel.className = 'navigation-panel minimized';
            panel.innerHTML = `
                <div class="navigation-header">
                    <div class="navigation-title">Navigation</div>
                    <div class="navigation-controls-inline">
                        <button class="navigation-close" title="Stop Navigation">✕</button>
                    </div>
                </div>
                <div class="navigation-content">
                    <div class="navigation-info">
                        <div class="navigation-destination">To: ${this.navigationTarget.name}</div>
                        <div class="navigation-stats">
                            <div class="nav-stat-row">
                                <span>Distance: <span id="navDistance">Calculating...</span></span>
                                <span id="navDuration" style="display: none;">ETA: <span></span></span>
                            </div>
                            <div class="nav-stat-row">
                                <span id="navRouteType">Calculating route...</span>
                            </div>
                            <div class="nav-stat-row">
                                <span>Status: <span id="navStatus">Active</span></span>
                            </div>
                        </div>
                    </div>
                    <div class="navigation-controls">
                        <button class="navigation-btn stop">Stop Navigation</button>
                    </div>
                </div>
            `;
            document.body.appendChild(panel);
            
            // Add event listeners
            panel.querySelector('.navigation-close').addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent panel toggle when clicking close
                this.stopNavigation();
            });
            
            panel.querySelector('.stop').addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent panel toggle when clicking stop
                this.stopNavigation();
            });

            // Add panel tap to toggle functionality
            panel.addEventListener('click', (e) => {
                // Only toggle if clicking on the panel itself, not on buttons
                if (e.target === panel || e.target.closest('.navigation-header') || e.target.closest('.navigation-info')) {
                    panel.classList.toggle('minimized');
                }
            });

            // Add click outside to minimize functionality
            this.setupNavigationPanelOutsideClick(panel);
        }
        
        setTimeout(() => panel.classList.add('show'), 10);
    }

    // Setup click outside to minimize navigation panel
    setupNavigationPanelOutsideClick(panel) {
        const handleOutsideClick = (e) => {
            // Check if click is outside the panel
            if (!panel.contains(e.target) && !panel.classList.contains('minimized')) {
                panel.classList.add('minimized');
            }
        };

        // Add event listener to document
        document.addEventListener('click', handleOutsideClick);

        // Store reference to remove listener when navigation stops
        panel._outsideClickHandler = handleOutsideClick;
    }
    
    updateNavigationPanel(distance, duration = null, isFallback = false) {
        const distanceElement = document.getElementById('navDistance');
        const durationElement = document.getElementById('navDuration');
        const routeTypeElement = document.getElementById('navRouteType');
        
        if (distanceElement) {
            distanceElement.textContent = distance + ' km';
        }
        
        if (durationElement) {
            if (duration) {
                durationElement.textContent = duration + ' min';
                durationElement.style.display = 'inline';
            } else {
                durationElement.style.display = 'none';
            }
        }
        
        if (routeTypeElement) {
            if (isFallback) {
                routeTypeElement.textContent = 'Direct path (estimated)';
                routeTypeElement.style.color = '#f59e0b';
            } else {
                routeTypeElement.textContent = 'Road route';
                routeTypeElement.style.color = '#10b981';
            }
        }
    }
    
    showNavigationStatus(message) {
        let status = document.getElementById('navigationStatus');
        if (!status) {
            status = document.createElement('div');
            status.id = 'navigationStatus';
            status.className = 'navigation-status tracking';
            document.body.appendChild(status);
        }
        
        status.textContent = message;
        status.classList.add('show');
    }
    
    stopNavigation() {
        this.navigationActive = false;
        
        // Clear location tracking
        if (this.locationUpdateInterval) {
            clearInterval(this.locationUpdateInterval);
            this.locationUpdateInterval = null;
        }
        
        // Remove route layer
        if (this.routeLayer) {
            this.map.removeLayer(this.routeLayer);
            this.routeLayer = null;
        }
        
        // Remove user marker
        if (this.userMarker) {
            this.map.removeLayer(this.userMarker);
            this.userMarker = null;
        }
        
        // Hide navigation panel and clean up event listeners
        const panel = document.getElementById('navigationPanel');
        if (panel) {
            // Remove outside click event listener
            if (panel._outsideClickHandler) {
                document.removeEventListener('click', panel._outsideClickHandler);
                panel._outsideClickHandler = null;
            }
            
            panel.classList.remove('show');
            setTimeout(() => {
                if (panel.parentNode) {
                    panel.parentNode.removeChild(panel);
                }
            }, 300);
        }
        
        // Hide navigation status
        const status = document.getElementById('navigationStatus');
        if (status) {
            status.classList.remove('show');
            setTimeout(() => {
                if (status.parentNode) {
                    status.parentNode.removeChild(status);
                }
            }, 300);
        }
        
        this.navigationTarget = null;
        this.userLocation = null;
        
        console.log('Navigation stopped');
    }

    // Load people location data from the API
    async loadPeopleData() {
        try {
            // Remove existing layer
            if (this.currentLayer) {
                this.map.removeLayer(this.currentLayer);
            }

            // Fetch people data from API
            const response = await fetch('api/people.php');
            if (!response.ok) {
                throw new Error('Failed to fetch people data');
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.error || 'Failed to load people data');
            }

            // Create a new layer group for people markers
            this.currentLayer = L.layerGroup();
            const bounds = L.latLngBounds();
            const peopleData = result.data;

            // Create markers for each person
            for (const [deviceName, location] of Object.entries(peopleData)) {
                const latLng = L.latLng(location.lat, location.lng);
                bounds.extend(latLng);

                // Calculate time since last update
                const lastUpdate = new Date(location.time * 1000);
                const timeDiff = Date.now() - lastUpdate;
                const hoursAgo = Math.floor(timeDiff / (1000 * 60 * 60));
                const minutesAgo = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                
                let timeText;
                if (hoursAgo > 0) {
                    timeText = `${hoursAgo}h ${minutesAgo}m ago`;
                } else if (minutesAgo > 0) {
                    timeText = `${minutesAgo}m ago`;
                } else {
                    timeText = 'Just now';
                }

                // Determine display name (use alternate name if available, otherwise device name)
                const displayName = location.alternateName || deviceName;

                // Get people category colors
                const customColor = this.getCSSColorFromCategory('people');
                const markerSizing = this.getMarkerSizeForZoom();

                // Create custom people marker
                const peopleIcon = L.divIcon({
                    className: 'custom-map-marker people-marker',
                    html: `
                        <div class="marker-container">
                            <div class="marker-pin" style="background: ${customColor.primary}; box-shadow: 0 4px 12px ${customColor.shadow};">
                                <div class="marker-icon">👤</div>
                            </div>
                            <div class="marker-label" style="background: ${customColor.primary}; box-shadow: 0 2px 8px ${customColor.shadow};">
                                <span class="marker-text">${this.truncateName(displayName)}</span>
                            </div>
                        </div>
                    `,
                    iconSize: markerSizing.iconSize,
                    iconAnchor: markerSizing.iconAnchor,
                    popupAnchor: markerSizing.popupAnchor
                });

                // Create marker
                const marker = L.marker(latLng, { icon: peopleIcon });

                // Create popup with device info
                const popupContent = `
                    <div class="custom-popup-content">
                        <div class="popup-header">
                            <h3 class="popup-title">👤 ${displayName}</h3>
                            <div class="popup-category people">People Location</div>
                        </div>
                        <div class="popup-info">
                            <div class="popup-detail">
                                <strong>Device:</strong> ${deviceName}
                            </div>
                            ${location.alternateName ? `
                            <div class="popup-detail">
                                <strong>Display Name:</strong> ${location.alternateName}
                            </div>
                            ` : ''}
                            <div class="popup-detail">
                                <strong>Last Updated:</strong> ${timeText}
                            </div>
                            <div class="popup-detail">
                                <strong>Coordinates:</strong> ${location.lat.toFixed(6)}, ${location.lng.toFixed(6)}
                            </div>
                            <div class="popup-detail">
                                <strong>Timestamp:</strong> ${location.timestamp}
                            </div>
                        </div>
                        <div class="popup-actions">
                            <button class="popup-btn navigate" onclick="travelMap.showBatteryWarning({name: '${displayName}', coordinates: L.latLng(${location.lat}, ${location.lng})})">
                                Navigate Here
                            </button>
                            <button class="popup-btn set-name" onclick="travelMap.showSetNameDialog('${deviceName.replace(/'/g, "\\'")}', '${(location.alternateName || '').replace(/'/g, "\\'")}')">
                                Set Name
                            </button>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent, {
                    className: 'custom-popup people-popup',
                    maxWidth: 350,
                    minWidth: 250
                });

                // Add marker to layer group
                this.currentLayer.addLayer(marker);
            }

            // Add layer to map
            this.currentLayer.addTo(this.map);

            // Fit bounds if we have any people locations
            if (bounds.isValid()) {
                this.map.fitBounds(bounds, { padding: [20, 20] });
            }

            console.log('People data loaded successfully:', Object.keys(peopleData).length, 'people');
            this.showLoading(false);
            this.updateMapStats();

            // Auto-save current user location if GPS is enabled
            this.promptForLocationSharing();

        } catch (error) {
            console.error('Error loading people data:', error);
            this.showError('Failed to load people locations. Please try again.');
            this.showLoading(false);
        }
    }

    // Show/hide refresh button based on category
    showRefreshButton(show) {
        const refreshBtn = document.getElementById('refreshPeopleBtn');
        if (refreshBtn) {
            refreshBtn.style.display = show ? 'block' : 'none';
        }
    }

    // Prompt user to share their location when viewing people category
    promptForLocationSharing() {
        if (!navigator.geolocation) {
            return; // Geolocation not supported
        }

        // Check if user has already shared location recently (within last 10 minutes)
        const lastShared = localStorage.getItem('lastLocationShared');
        if (lastShared && Date.now() - parseInt(lastShared) < 10 * 60 * 1000) {
            return; // Recently shared, don't prompt again
        }

        // Get current location and save it
        navigator.geolocation.getCurrentPosition(
            (position) => {
                this.saveUserLocation(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.log('Location sharing declined or failed:', error.message);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    }

    // Save user location to the people database
    async saveUserLocation(lat, lng, alternateName = null) {
        try {
            // Get device name automatically from browser/device info
            let deviceName = await this.getDeviceName();
            
            // If we couldn't detect device name, ask user or use stored name
            if (!deviceName) {
                deviceName = localStorage.getItem('deviceName');
                if (!deviceName) {
                    deviceName = prompt('Enter your name for location sharing (or leave empty for auto-generated):');
                    if (deviceName) {
                        localStorage.setItem('deviceName', deviceName);
                    }
                    // If still no name, let server generate one
                }
            }

            // Prepare request data
            const requestData = {
                lat: lat,
                lng: lng,
                deviceName: deviceName
            };
            
            // Include alternate name if provided
            if (alternateName) {
                requestData.alternateName = alternateName;
            }

            // Save location to server
            const response = await fetch('api/people.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });

            const result = await response.json();
            if (result.success) {
                console.log('Location saved successfully for:', result.deviceName);
                console.log('Original detected name:', deviceName);
                localStorage.setItem('lastLocationShared', Date.now().toString());
                
                // Store the server-confirmed device name for future use
                if (result.deviceName !== deviceName) {
                    localStorage.setItem('deviceName', result.deviceName);
                    console.log('Device name updated to:', result.deviceName);
                }
                
                // Refresh people data to show updated location
                if (this.currentKmlType === 'people') {
                    setTimeout(() => this.loadPeopleData(), 1000);
                }
            } else {
                console.error('Failed to save location:', result.error);
            }

        } catch (error) {
            console.error('Error saving user location:', error);
        }
    }

    // Debug function to test device name detection
    async testDeviceNameDetection() {
        const deviceName = await this.getDeviceName();
        console.log('Detected device name:', deviceName);
        console.log('User Agent:', navigator.userAgent);
        console.log('Platform:', navigator.platform);
        
        if (navigator.userAgentData) {
            try {
                const brands = navigator.userAgentData.brands;
                const mobile = navigator.userAgentData.mobile;
                console.log('User Agent Data - Brands:', brands);
                console.log('User Agent Data - Mobile:', mobile);
                
                const highEntropy = await navigator.userAgentData.getHighEntropyValues([
                    'model', 'platform', 'platformVersion', 'architecture', 'bitness'
                ]);
                console.log('High Entropy Values:', highEntropy);
            } catch (e) {
                console.log('High entropy values not available');
            }
        }
        
        return deviceName;
    }

    // Get device name from browser/device information
    async getDeviceName() {
        try {
            let deviceInfo = [];
            
            // Method 1: Try to get device name from navigator
            if (navigator.userAgentData && navigator.userAgentData.getHighEntropyValues) {
                try {
                    const uaData = await navigator.userAgentData.getHighEntropyValues([
                        'model', 'platform', 'platformVersion'
                    ]);
                    
                    if (uaData.model) {
                        deviceInfo.push(uaData.model);
                    } else if (uaData.platform) {
                        deviceInfo.push(uaData.platform);
                    }
                } catch (e) {
                    console.log('High entropy values not available:', e.message);
                }
            }
            
            // Method 2: Parse User Agent for device info
            if (deviceInfo.length === 0) {
                const userAgent = navigator.userAgent;
                
                // Try to extract device model from user agent
                let deviceModel = null;
                
                // Android devices
                if (userAgent.includes('Android')) {
                    const androidMatch = userAgent.match(/Android.*?;\s*([^)]+)/);
                    if (androidMatch && androidMatch[1]) {
                        deviceModel = androidMatch[1].trim();
                        // Clean up common patterns
                        deviceModel = deviceModel.replace(/Build\/.*$/, '').trim();
                        deviceModel = deviceModel.replace(/\s+wv$/, '').trim();
                    }
                }
                
                // iOS devices
                else if (userAgent.includes('iPhone')) {
                    deviceModel = 'iPhone';
                    // Try to determine iPhone model
                    const iosMatch = userAgent.match(/iPhone OS ([^;]+)/);
                    if (iosMatch) {
                        deviceModel += ' (iOS ' + iosMatch[1].replace(/_/g, '.') + ')';
                    }
                }
                else if (userAgent.includes('iPad')) {
                    deviceModel = 'iPad';
                    const iosMatch = userAgent.match(/OS ([^;]+)/);
                    if (iosMatch) {
                        deviceModel += ' (iOS ' + iosMatch[1].replace(/_/g, '.') + ')';
                    }
                }
                
                // Windows devices
                else if (userAgent.includes('Windows NT')) {
                    const windowsMatch = userAgent.match(/Windows NT ([^;)]+)/);
                    if (windowsMatch) {
                        deviceModel = 'Windows ' + this.getWindowsVersion(windowsMatch[1]);
                    } else {
                        deviceModel = 'Windows PC';
                    }
                }
                
                // Mac devices
                else if (userAgent.includes('Macintosh')) {
                    deviceModel = 'Mac';
                    if (userAgent.includes('Intel')) {
                        deviceModel += ' (Intel)';
                    } else if (userAgent.includes('PPC')) {
                        deviceModel += ' (PowerPC)';
                    }
                }
                
                // Linux
                else if (userAgent.includes('Linux')) {
                    deviceModel = 'Linux PC';
                }
                
                if (deviceModel) {
                    deviceInfo.push(deviceModel);
                }
            }
            
            // Method 3: Get browser info as fallback
            if (deviceInfo.length === 0) {
                const browserInfo = this.getBrowserInfo();
                if (browserInfo) {
                    deviceInfo.push(browserInfo + ' User');
                }
            }
            
            // Method 4: Try to get network/connection info for additional context
            if (navigator.connection) {
                const connection = navigator.connection;
                if (connection.type === 'cellular') {
                    deviceInfo.push('(Mobile)');
                } else if (connection.type === 'wifi') {
                    deviceInfo.push('(WiFi)');
                }
            }
            
            // Create final device name
            let finalDeviceName = deviceInfo.join(' ').trim();
            
            // Clean up and limit length
            if (finalDeviceName) {
                finalDeviceName = finalDeviceName
                    .replace(/\s+/g, ' ')
                    .replace(/[^\w\s\-\(\)\.]/g, '')
                    .substring(0, 50)
                    .trim();
                
                // Add random number to make it unique
                finalDeviceName += ' #' + Math.floor(Math.random() * 1000);
                
                return finalDeviceName;
            }
            
            return null; // No device name detected
            
        } catch (error) {
            console.log('Error detecting device name:', error);
            return null;
        }
    }
    
    // Helper function to convert Windows NT version to readable name
    getWindowsVersion(version) {
        const versions = {
            '10.0': '10/11',
            '6.3': '8.1',
            '6.2': '8',
            '6.1': '7',
            '6.0': 'Vista',
            '5.2': 'XP x64',
            '5.1': 'XP'
        };
        return versions[version] || version;
    }
    
    // Helper function to get browser information
    getBrowserInfo() {
        const userAgent = navigator.userAgent;
        
        if (userAgent.includes('Chrome') && !userAgent.includes('Edg')) {
            return 'Chrome';
        } else if (userAgent.includes('Firefox')) {
            return 'Firefox';
        } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
            return 'Safari';
        } else if (userAgent.includes('Edg')) {
            return 'Edge';
        } else if (userAgent.includes('Opera') || userAgent.includes('OPR')) {
            return 'Opera';
        } else {
            return 'Browser';
        }
    }

    // Show dialog for setting alternate name
    showSetNameDialog(deviceName, currentAlternateName = '') {
        // Remove any existing dialog
        const existingDialog = document.querySelector('.set-name-overlay');
        if (existingDialog) {
            existingDialog.remove();
        }

        // Create dialog HTML using existing CSS classes
        const dialogHTML = `
            <div class="set-name-overlay">
                <div class="set-name-modal">
                    <div class="set-name-header">
                        <h3>Set Display Name</h3>
                        <button class="set-name-close" onclick="travelMap.closeSetNameDialog()">&times;</button>
                    </div>
                    <div class="set-name-content">
                        <p>Device: <strong>${deviceName}</strong></p>
                        <div class="set-name-field">
                            <label for="alternateName">Display Name (optional):</label>
                            <input type="text" id="alternateName" value="${currentAlternateName}" 
                                   placeholder="Enter your preferred display name" maxlength="50">
                            <small>This name will be shown instead of your device name</small>
                        </div>
                    </div>
                    <div class="set-name-actions">
                        <button class="set-name-btn cancel" onclick="travelMap.closeSetNameDialog()">Cancel</button>
                        <button class="set-name-btn clear" onclick="travelMap.clearAlternateName('${deviceName}')" 
                                ${!currentAlternateName ? 'disabled' : ''}>Clear Name</button>
                        <button class="set-name-btn save" onclick="travelMap.saveAlternateName('${deviceName}')">Save</button>
                    </div>
                </div>
            </div>
        `;

        // Add dialog to DOM
        document.body.insertAdjacentHTML('beforeend', dialogHTML);

        // Show dialog with animation
        setTimeout(() => {
            const overlay = document.querySelector('.set-name-overlay');
            if (overlay) {
                overlay.classList.add('show');
            }
        }, 10);

        // Focus input field
        setTimeout(() => {
            const input = document.getElementById('alternateName');
            if (input) {
                input.focus();
                input.select();
            }
        }, 100);

        // Handle Enter key
        const input = document.getElementById('alternateName');
        if (input) {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.saveAlternateName(deviceName);
                }
            });
        }

        // Handle Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeSetNameDialog();
            }
        }, { once: true });
    }

    // Close the set name dialog
    closeSetNameDialog() {
        const overlay = document.querySelector('.set-name-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            setTimeout(() => overlay.remove(), 300);
        }
    }

    // Save alternate name
    async saveAlternateName(deviceName) {
        const input = document.getElementById('alternateName');
        if (!input) return;

        const alternateName = input.value.trim();
        
        try {
            const success = await this.updateAlternateName(deviceName, alternateName);
            if (success) {
                this.closeSetNameDialog();
                this.showSuccessMessage(alternateName ? 'Name updated successfully!' : 'Name cleared successfully!');
                
                // Refresh people data to show updated name
                if (this.currentKmlType === 'people') {
                    setTimeout(() => this.loadPeopleData(), 500);
                }
            }
        } catch (error) {
            console.error('Error saving alternate name:', error);
            this.showError('Failed to update name. Please try again.');
        }
    }

    // Clear alternate name
    async clearAlternateName(deviceName) {
        try {
            const success = await this.updateAlternateName(deviceName, '');
            if (success) {
                this.closeSetNameDialog();
                this.showSuccessMessage('Name cleared successfully!');
                
                // Refresh people data to show updated name
                if (this.currentKmlType === 'people') {
                    setTimeout(() => this.loadPeopleData(), 500);
                }
            }
        } catch (error) {
            console.error('Error clearing alternate name:', error);
            this.showError('Failed to clear name. Please try again.');
        }
    }

    // Update alternate name on server
    async updateAlternateName(deviceName, alternateName) {
        try {
            const response = await fetch('api/people.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    deviceName: deviceName,
                    alternateName: alternateName
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error updating alternate name:', error);
            throw error;
        }
    }

    // Show success message
    showSuccessMessage(message) {
        // Remove any existing success message
        const existingToast = document.querySelector('.map-success-toast');
        if (existingToast) {
            existingToast.remove();
        }

        // Create success toast using existing CSS class
        const toastHTML = `
            <div class="map-success-toast">
                <span>✓ ${message}</span>
            </div>
        `;

        // Add toast to DOM
        document.body.insertAdjacentHTML('beforeend', toastHTML);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            const toast = document.querySelector('.map-success-toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }
}

// Initialize map when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.travelMap = new TravelMap();
    
    // Add global debug function for testing device name detection
    window.testDeviceName = async () => {
        if (window.travelMap) {
            return await window.travelMap.testDeviceNameDetection();
        }
        console.log('Travel map not initialized yet');
    };
    
    console.log('Travel Map initialized. You can test device name detection by calling: testDeviceName()');
});

// Export for potential module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TravelMap;
}

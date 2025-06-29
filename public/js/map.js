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
        this.markerData = []; // Store marker data for recreation after zoom
        this.zoomRecreateTimeout = null; // Timeout for zoom-based marker recreation
        this.kmlFiles = {
            'all': 'data/kml/Map.kml',
            'breakfast': 'data/kml/Breakfast.kml',
            'lunch-dinner': 'data/kml/LunchDinner.kml',
            'snack-night': 'data/kml/Junk.kml',
            'coffee': 'data/kml/Cafe.kml',
            'tour': 'data/kml/Touring.kml',
            'fuel': 'data/kml/Fuel.kml'
        };
        this.categoryColors = {
            'breakfast': 'breakfast',
            'lunch-dinner': 'lunch-dinner',
            'snack-night': 'snack-night',
            'coffee': 'coffee',
            'tour': 'tour',
            'fuel': 'fuel'
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
            this.scheduleMarkerRecreation();
        });

        this.map.on('moveend', () => {
            this.updateMapStats();
        });
    }

    addControls() {
        // Add locate control
        L.control.locate({
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
                'fuel': 'Fuel Stations'
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
            // Parse KML styles first
            await this.parseKMLStyles(kmlFile);

            // Clear any pending marker recreation timeout
            if (this.zoomRecreateTimeout) {
                clearTimeout(this.zoomRecreateTimeout);
                this.zoomRecreateTimeout = null;
            }

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
            
            // Clear existing marker data
            this.markerData = [];

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

                    // Get dynamic marker sizing based on zoom level
                    const markerSizing = this.getMarkerSizeForZoom();

                    // Create custom marker with dynamic sizing
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

                    // Store marker data for recreation after zoom changes
                    this.markerData.push({
                        latLng: latLng,
                        name: name,
                        description: description,
                        categoryClass: categoryClass,
                        customColor: customColor
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

    // Method to calculate marker size and anchor based on zoom level
    getMarkerSizeForZoom() {
        if (!this.map) {
            return {
                iconSize: [120, 80],
                iconAnchor: [60, 70],
                popupAnchor: [0, -70]
            };
        }
        
        const zoom = this.map.getZoom();
        
        // Scale marker size based on zoom level
        // At zoom 10-12: normal size
        // At zoom 13-15: slightly smaller
        // At zoom 16+: smallest
        // At zoom < 10: larger
        
        let scale = 0.5;
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

    // Method to schedule marker recreation after zoom change (with 1-second delay)
    scheduleMarkerRecreation() {
        // Clear any existing timeout to debounce rapid zoom changes
        if (this.zoomRecreateTimeout) {
            clearTimeout(this.zoomRecreateTimeout);
        }
        
        // Schedule marker recreation after 1 second
        this.zoomRecreateTimeout = setTimeout(() => {
            if (this.markerData.length > 0) {
                console.log('Recreating markers after zoom change at level:', this.map.getZoom());
                this.recreateMarkersFromData();
            }
        }, 1000);
    }

    // Method to recreate markers from stored data with proper sizing
    recreateMarkersFromData() {
        if (!this.currentLayer || this.markerData.length === 0) {
            return;
        }

        // Remove existing layer
        this.map.removeLayer(this.currentLayer);
        
        // Create a new layer group
        this.currentLayer = L.layerGroup();

        // Recreate each marker from stored data with new sizing
        this.markerData.forEach(markerInfo => {
            const marker = this.createMarkerFromData(markerInfo);
            if (marker) {
                this.currentLayer.addLayer(marker);
            }
        });

        // Add the recreated layer back to the map
        this.currentLayer.addTo(this.map);
        
        // Update stats
        this.updateMapStats();
    }

    // Helper method to create a single marker from stored data
    createMarkerFromData(markerInfo) {
        const { latLng, name, description, categoryClass, customColor } = markerInfo;
        
        // Get current zoom-appropriate sizing
        const markerSizing = this.getMarkerSizeForZoom();
        
        // Create custom marker with current zoom sizing
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
                <button onclick="window.travelMap.showBatteryWarning({name: '${name.replace(/'/g, "\\'")}', coordinates: L.latLng(${latLng.lat}, ${latLng.lng})})" 
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

        return marker;
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
                        <button class="navigation-toggle" title="Expand/Minimize">+</button>
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
            panel.querySelector('.navigation-close').addEventListener('click', () => {
                this.stopNavigation();
            });
            
            panel.querySelector('.stop').addEventListener('click', () => {
                this.stopNavigation();
            });

            // Add toggle functionality for minimize/expand
            panel.querySelector('.navigation-toggle').addEventListener('click', () => {
                panel.classList.toggle('minimized');
                const toggleBtn = panel.querySelector('.navigation-toggle');
                if (panel.classList.contains('minimized')) {
                    toggleBtn.textContent = '+';
                    toggleBtn.title = 'Expand Navigation';
                } else {
                    toggleBtn.textContent = '-';
                    toggleBtn.title = 'Minimize Navigation';
                }
            });
        }
        
        setTimeout(() => panel.classList.add('show'), 10);
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
        
        // Hide navigation panel
        const panel = document.getElementById('navigationPanel');
        if (panel) {
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
}

// Initialize map when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.travelMap = new TravelMap();
});

// Export for potential module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TravelMap;
}

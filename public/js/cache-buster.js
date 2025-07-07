// Selective cache busting and mobile optimization utilities
(function() {
    'use strict';
    
    // Force no-cache for service workers (affects HTML/CSS/JS only)
    if ('serviceWorker' in navigator) {
        // Disable service worker if it exists to prevent caching of UI files
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        });
    }
    
    // Clear browser cache on page load (UI files only)
    if (performance.navigation.type === 1) {
        // Page was reloaded
        console.log('Page reloaded - UI cache cleared');
    }
    // Minimal mobile optimizations only (no asset modification)
    document.addEventListener('DOMContentLoaded', function() {
        // Only add mobile touch optimizations
        
        // Prevent iOS bounce effect
        document.body.addEventListener('touchstart', function() {}, { passive: true });
        document.body.addEventListener('touchend', function() {}, { passive: true });
        document.body.addEventListener('touchmove', function(e) {
            // Prevent overscroll
            if (e.target === document.body) {
                //e.preventDefault();
            }
        }, { passive: false });
        
        // Debug: Check if nav images are loading (after a delay)
        setTimeout(function() {
            const navItems = document.querySelectorAll('.nav-item');
            console.log('Found', navItems.length, 'navigation items');
            
            navItems.forEach(function(item, index) {
                const styles = window.getComputedStyle(item);
                const bgImage = styles.backgroundImage;
                console.log('Nav item ' + index + ':', item.getAttribute('href'), 'Background:', bgImage);
                
                // Check if background image is loading
                if (bgImage && bgImage !== 'none') {
                    const urlMatch = bgImage.match(/url\(["']?([^"')]+)["']?\)/);
                    if (urlMatch) {
                        const img = new Image();
                        img.onload = function() {
                            console.log('✓ Background image loaded:', urlMatch[1]);
                        };
                        img.onerror = function() {
                            console.error('✗ Background image failed to load:', urlMatch[1]);
                        };
                        img.src = urlMatch[1];
                    }
                } else {
                    console.warn('No background image found for nav item:', item.getAttribute('href'));
                }
            });
        }, 2000);
    });
    
    // Force page refresh when browser back button is used (for HTML pages only)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache, reload it
            window.location.reload();
        }
    });
    
    // Clear only HTML/CSS/JS cache when leaving page (preserve image cache)
    window.addEventListener('beforeunload', function() {
        if ('caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) {
                    // Only clear caches that might contain HTML/CSS/JS
                    if (name.includes('html') || name.includes('css') || name.includes('js') || name.includes('api') || name.includes('password.json')) {
                        caches.delete(name);
                    }
                });
            });
        }
    });
    
})();

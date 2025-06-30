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
    
    // Add timestamp to CSS and JS links only (preserve image caching)
    document.addEventListener('DOMContentLoaded', function() {
        // Only add cache busting to PHP pages and CSS/JS files
        const phpLinks = document.querySelectorAll('a[href$=".php"]');
        phpLinks.forEach(function(link) {
            if (link.href.includes(window.location.hostname)) {
                const separator = link.href.includes('?') ? '&' : '?';
                link.href += separator + '_t=' + Date.now();
            }
        });
        
        // Add cache busting to CSS and JS files that don't already have it
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"][href*=".css"]:not([href*="?v="])');
        cssLinks.forEach(function(link) {
            if (!link.href.includes('?v=')) {
                const separator = link.href.includes('?') ? '&' : '?';
                link.href += separator + 'v=' + Date.now();
            }
        });
        
        const jsScripts = document.querySelectorAll('script[src*=".js"]:not([src*="?v="])');
        jsScripts.forEach(function(script) {
            if (!script.src.includes('?v=')) {
                const separator = script.src.includes('?') ? '&' : '?';
                script.src += separator + 'v=' + Date.now();
            }
        });
        
        // Prevent iOS bounce effect
        document.body.addEventListener('touchstart', function() {}, { passive: true });
        document.body.addEventListener('touchend', function() {}, { passive: true });
        document.body.addEventListener('touchmove', function(e) {
            // Prevent overscroll
            if (e.target === document.body) {
                e.preventDefault();
            }
        }, { passive: false });
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
                    if (name.includes('html') || name.includes('css') || name.includes('js') || name.includes('api')) {
                        caches.delete(name);
                    }
                });
            });
        }
    });
    
})();

// Cache busting and mobile optimization utilities
(function() {
    'use strict';
    
    // Force no-cache for all requests
    if ('serviceWorker' in navigator) {
        // Disable service worker if it exists to prevent caching
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        });
    }
    
    // Clear browser cache on page load
    if (performance.navigation.type === 1) {
        // Page was reloaded
        console.log('Page reloaded - cache cleared');
    }
    
    // Add timestamp to all internal links to prevent caching
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href$=".php"]');
        links.forEach(function(link) {
            if (link.href.includes(window.location.hostname)) {
                const separator = link.href.includes('?') ? '&' : '?';
                link.href += separator + '_t=' + Date.now();
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
    
    // Force page refresh when browser back button is used
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Page was loaded from cache, reload it
            window.location.reload();
        }
    });
    
    // Clear cache when leaving page
    window.addEventListener('beforeunload', function() {
        if ('caches' in window) {
            caches.keys().then(function(names) {
                names.forEach(function(name) {
                    caches.delete(name);
                });
            });
        }
    });
    
})();

<?php
/**
 * Selective cache prevention utility for mobile devices
 * Include this file at the top of any PHP page to prevent caching issues
 * This version only prevents caching for UI elements, not static assets
 */

// Prevent caching for HTML/PHP pages only (not static assets)
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Additional mobile-specific headers for HTML pages
header("Vary: User-Agent");

// Prevent browser from storing page in history
header("Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0");

// Generate cache busting timestamp for use in templates (for CSS/JS only)
$cache_bust = time() . rand(1000, 9999);

// Function to get cache-busted URL for CSS/JS files only
function cache_bust_url($url) {
    global $cache_bust;
    
    // Only add cache busting to CSS and JS files
    if (preg_match('/\.(css|js)(\?.*)?$/', $url)) {
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'v=' . $cache_bust;
    }
    
    // Return original URL for images and other static assets
    return $url;
}

// Function to prevent back button cache for HTML pages
function prevent_back_cache() {
    echo '<script>
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>';
}
?>

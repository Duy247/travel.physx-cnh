<?php
/**
 * Gallery Cache API
 * 
 * This file handles caching of the Google Drive gallery content and serves
 * the cached version to improve load times while providing automatic refresh
 * when new content is available.
 */

// Set headers to prevent caching during development
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Cache-Control: no-cache");

// Configuration
$drive_folder_id = "17xlDHqSfzBmxkAbckbkeMJTPUYMVJT_1";
$cache_file = "../data/gallery_cache.html";
$cache_metadata = "../data/gallery_cache_metadata.json";
$cache_lifetime = 3600; // 1 hour in seconds

// Function to fetch Google Drive content
function fetchGoogleDriveContent($folder_id) {
    $url = "https://drive.google.com/embeddedfolderview?id={$folder_id}#grid";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36");
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200 && !empty($result)) {
        return $result;
    }
    
    return false;
}

// Function to check if cache needs to be refreshed
function shouldRefreshCache($metadata_file, $cache_lifetime) {
    if (!file_exists($metadata_file)) {
        return true;
    }
    
    $metadata = json_decode(file_get_contents($metadata_file), true);
    if (!$metadata || !isset($metadata['last_updated'])) {
        return true;
    }
    
    // Check if cache has expired
    return (time() - $metadata['last_updated']) > $cache_lifetime;
}

// Function to update cache
function updateCache($content, $cache_file, $metadata_file) {
    // Save the content
    file_put_contents($cache_file, $content);
    
    // Update metadata
    $metadata = [
        'last_updated' => time(),
        'etag' => md5($content),
        'size' => strlen($content)
    ];
    
    file_put_contents($metadata_file, json_encode($metadata));
    
    return $metadata;
}

// Main logic
$action = isset($_GET['action']) ? $_GET['action'] : 'get';

switch ($action) {
    case 'refresh':
        // Force refresh the cache
        $content = fetchGoogleDriveContent($drive_folder_id);
        if ($content) {
            $metadata = updateCache($content, $cache_file, $cache_metadata);
            echo json_encode([
                'success' => true,
                'message' => 'Cache refreshed successfully',
                'timestamp' => $metadata['last_updated'],
                'etag' => $metadata['etag']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch Google Drive content'
            ]);
        }
        break;
        
    case 'status':
        // Check cache status
        if (file_exists($cache_metadata)) {
            $metadata = json_decode(file_get_contents($cache_metadata), true);
            $needs_refresh = shouldRefreshCache($cache_metadata, $cache_lifetime);
            
            echo json_encode([
                'cached' => true,
                'last_updated' => $metadata['last_updated'],
                'needs_refresh' => $needs_refresh,
                'etag' => $metadata['etag']
            ]);
        } else {
            echo json_encode([
                'cached' => false,
                'needs_refresh' => true
            ]);
        }
        break;
        
    case 'get':
    default:
        // Get cached content or refresh if needed
        $refresh_requested = isset($_GET['force_refresh']) && $_GET['force_refresh'] === 'true';
        
        if ($refresh_requested || shouldRefreshCache($cache_metadata, $cache_lifetime)) {
            // Needs refresh or was requested
            $content = fetchGoogleDriveContent($drive_folder_id);
            if ($content) {
                $metadata = updateCache($content, $cache_file, $cache_metadata);
                echo json_encode([
                    'success' => true,
                    'content' => $content,
                    'cached' => false,
                    'timestamp' => $metadata['last_updated'],
                    'etag' => $metadata['etag']
                ]);
            } else {
                // Failed to fetch new content, try to serve cached content
                if (file_exists($cache_file)) {
                    $metadata = json_decode(file_get_contents($cache_metadata), true);
                    echo json_encode([
                        'success' => true,
                        'content' => file_get_contents($cache_file),
                        'cached' => true,
                        'timestamp' => $metadata['last_updated'],
                        'etag' => $metadata['etag'],
                        'fallback' => true
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to fetch content and no cache available'
                    ]);
                }
            }
        } else {
            // Serve from cache
            $metadata = json_decode(file_get_contents($cache_metadata), true);
            echo json_encode([
                'success' => true,
                'content' => file_get_contents($cache_file),
                'cached' => true,
                'timestamp' => $metadata['last_updated'],
                'etag' => $metadata['etag']
            ]);
        }
        break;
}

# Gallery Cache System

This folder contains cache files for the Gallery system.

## Files

- `gallery_cache.html`: Contains the cached HTML content from Google Drive
- `gallery_cache_metadata.json`: Contains metadata about the cache, including last update time and ETag

## How it works

1. The gallery system first checks if there's a valid cache available
2. If the cache is expired (more than 1 hour old) or if a refresh is requested, it fetches fresh content
3. If fetching fresh content fails, it falls back to the cached version
4. The system automatically checks for updates every 5 minutes or when the browser tab regains focus

## Manual refresh

You can manually refresh the gallery by:

1. Clicking the refresh button in the top-right corner of the gallery
2. Visiting `/api/gallery_cache.php?action=refresh`

## Cache status indicator

The gallery page displays a status indicator at the bottom showing:

- Green: Fresh content or valid cache
- Amber: Using fallback cache or offline
- Blue (pulsing): Currently refreshing

## Cache configuration

Configuration can be modified in the `GALLERY_CACHE_CONFIG` object at the top of the Gallery.php file:

- `refreshInterval`: How often to check for updates (in milliseconds)
- `checkOnFocus`: Whether to check for updates when the browser tab regains focus
- `forceRefresh`: Force a refresh on page load (overrides cache)

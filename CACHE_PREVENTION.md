# Selective Cache Prevention for Mobile Devices

This document explains the selective cache prevention measures implemented to fix UI issues on mobile devices while maintaining performance for static assets.

## Implemented Solutions

### 1. Selective Server-Side Cache Prevention
- **PHP Headers**: All PHP files include cache prevention headers for HTML content only
- **UI Files**: HTML, PHP, CSS, and JS files have no-cache headers
- **Static Assets**: Images, fonts, and other static files maintain long-term caching
- **Headers Applied**:
  - UI Files: `Cache-Control: no-cache, no-store, must-revalidate`
  - Images: `Cache-Control: public, max-age=31536000` (1 year)
  - Fonts: `Cache-Control: public, max-age=31536000` (1 year)

### 2. Smart Client-Side Cache Busting
- **CSS/JS Only**: Cache busting parameters only applied to stylesheets and scripts
- **Image Preservation**: Images retain their cache for optimal performance
- **API Calls**: Weather API calls include cache-busting parameters
- **Selective URLs**: Only PHP, CSS, and JS files get timestamp parameters

### 3. Browser Configuration
- **Meta Tags**: Added comprehensive cache prevention meta tags:
  - `http-equiv="Cache-Control"`
  - `http-equiv="Pragma"`
  - `http-equiv="Expires"`

### 4. Mobile-Specific Optimizations
- **Viewport Meta Tag**: Enhanced with `maximum-scale=1.0, user-scalable=no`
- **Apple Web App**: Added `apple-mobile-web-app-capable` for iOS
- **Overscroll Prevention**: Disabled pull-to-refresh behaviors

### 5. Apache Configuration (.htaccess)
- **Global Cache Disabling**: Server-level cache prevention for all files
- **ETag Removal**: Disabled file ETags to prevent cache validation
- **Header Injection**: Automatic cache prevention headers for all responses

### 6. JavaScript Cache Management
- **Service Worker Cleanup**: Automatically unregisters any service workers
- **Page Refresh Logic**: Forces reload when returning from cache
- **Link Modification**: Adds timestamps to internal navigation links

## File Type Cache Policy

### ❌ No Cache (Mobile UI Fix)
- **HTML files** (.html, .htm)
- **PHP files** (.php) 
- **CSS files** (.css)
- **JavaScript files** (.js)
- **API responses** (JSON)

### ✅ Long-term Cache (Performance)
- **Images** (.jpg, .jpeg, .png, .gif, .webp, .svg, .ico) - 1 year
- **Fonts** (.woff, .woff2, .ttf, .eot) - 1 year
- **Static data** (.json, .xml, .kml, .pdf) - 1 day

### Benefits
- **UI Freshness**: Mobile users always get latest UI updates
- **Performance**: Images and fonts load fast from cache
- **Bandwidth**: Reduced data usage for static assets
- **Speed**: Faster page loads with cached images

## Files Modified

### Core PHP Files
- `public/index.php` - Main page with cache prevention
- `public/Weather.php` - Weather page with cache headers
- `public/Map.php` - Map page with asset cache busting
- `public/api/weather.php` - API with cache prevention

### New Files Created
- `public/Plan.php` - Timeline page with cache prevention
- `public/Spending.php` - Budget page with cache prevention  
- `public/PackingList.php` - Packing list with cache prevention
- `public/PersonalPack.php` - Personal items with cache prevention
- `public/Info.php` - Emergency info with cache prevention
- `public/Gallery.php` - Gallery page with cache prevention
- `public/js/cache-buster.js` - JavaScript cache management
- `public/includes/cache-prevention.php` - Reusable cache prevention utility

### Configuration Files
- `public/.htaccess` - Apache cache prevention rules
- `public/css/style.css` - Mobile optimization styles

## Usage

### For Developers
1. Include cache prevention headers in all new PHP files
2. Use the `cache_bust_url()` function for asset URLs
3. Include the cache-buster.js script on all pages
4. Test on actual mobile devices to verify cache prevention

### For Users
- Clear browser cache and hard refresh (Ctrl+Shift+R)
- On mobile, close and reopen the browser app
- The system will automatically prevent caching going forward

## Testing Cache Prevention

### Browser Testing
1. Open developer tools
2. Check Network tab for cache status
3. Look for "no-cache" headers in responses
4. Verify timestamps in asset URLs

### Mobile Testing
1. Test on various mobile browsers (Safari, Chrome, Firefox)
2. Check that UI updates appear immediately
3. Verify navigation between pages works correctly
4. Test back button behavior

## Troubleshooting

### If Cache Issues Persist
1. Check server configuration for cache headers
2. Verify .htaccess file is being processed
3. Clear browser data completely
4. Test in private/incognito mode

### Performance Considerations
- Cache prevention may slightly increase load times
- Monitor server resources as assets are always reloaded
- Consider implementing selective caching for static assets if needed

## Maintenance

### Regular Tasks
- Monitor cache prevention effectiveness
- Update cache-busting timestamps regularly
- Test on new mobile devices and browsers
- Review server logs for cache-related issues

### Future Improvements
- Implement smarter cache invalidation strategies
- Add version control for assets
- Consider implementing PWA features with proper cache management
- Add automated cache testing

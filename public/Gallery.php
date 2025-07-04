<?php
// public/Gallery.php

// Prevent caching for mobile devices
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Generate cache busting timestamp
$cache_bust = time();

// Include cache prevention file
include_once '../public/includes/cache-prevention.php';

// Make sure we have index.php as home instead of Main.html
$home_link = "index.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thư Viện Ảnh</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <script src="js/cache-buster.js?v=<?php echo $cache_bust; ?>"></script>
    <script>
    // Gallery cache configuration
    const GALLERY_CACHE_CONFIG = {
        refreshInterval: 5 * 60 * 1000, // 5 minutes in milliseconds
        cacheApi: 'api/gallery_cache.php',
        checkOnFocus: true,
        forceRefresh: false
    };
    </script>
    <style>
        /* Gallery-specific styles with glassmorphism */
        .gallery-page {
            min-height: 100vh;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        
        .gallery-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(217, 70, 239, 0.06) 0%, transparent 50%),
             #0a0a0b;
            animation: float 6s ease-in-out infinite;
        }

        .page-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            color: #f4f4f5;
            margin-bottom: 1.5rem;
            text-align: center;
            text-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
        }
        
        .gallery-container {
            width: 100%;
            max-width: 1200px;
            height: 70vh;
            position: relative;
            border-radius: 20px;
            overflow: visible; /* Changed from hidden to visible */
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 4px 16px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .gallery-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(99, 102, 241, 0.3), 
                rgba(139, 92, 246, 0.3), 
                rgba(217, 70, 239, 0.3),
                rgba(99, 102, 241, 0.3)
            );
            border-radius: 22px;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
            opacity: 0.6;
        }
        
        .gallery {
            width: 100%;
            height: 100%;
            border: none;
            overflow: auto !important; /* Force scrolling to be enabled */
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            
        }
        
        .back-button {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.8rem 2rem;
            background: rgba(99, 102, 241, 0.2);
            color: #e4e4e7;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 1rem;
            border-radius: 10px;
            border: 1px solid rgba(139, 92, 246, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }
        
        .back-button:hover, .back-button:focus {
            background: rgba(139, 92, 246, 0.3);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
            transform: translateY(-2px);
        }
        
        /* Loading indicator */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 11, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transition: opacity 0.5s ease;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(139, 92, 246, 0.3);
            border-top: 4px solid rgba(99, 102, 241, 1);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes borderGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        /* Cache status indicator */
        .cache-status {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #a1a1aa;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .cache-status:hover {
            opacity: 1;
        }
        
        .cache-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        
        .cache-indicator.fresh {
            background-color: #10b981; /* Green for fresh content */
            box-shadow: 0 0 5px #10b981;
        }
        
        .cache-indicator.stale {
            background-color: #f59e0b; /* Amber for slightly stale */
            box-shadow: 0 0 5px #f59e0b;
        }
        
        .cache-indicator.refreshing {
            background-color: #3b82f6; /* Blue for refreshing */
            box-shadow: 0 0 5px #3b82f6;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
        
        /* Gallery content styling */
        .gallery-content {
            width: 100%;
            height: 100%;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Gallery content will inherit Google Drive styling but we may need some overrides */
        .gallery-content iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Refresh button */
        .refresh-button {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.2);
            color: #e4e4e7;
            border: 1px solid rgba(139, 92, 246, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0.7;
            transition: all 0.2s ease;
            z-index: 2;
        }
        
        .refresh-button:hover {
            opacity: 1;
            background: rgba(139, 92, 246, 0.3);
        }
        
        .refresh-button i {
            font-size: 14px;
        }
        
        .refresh-button.spinning i {
            animation: spin 1s linear infinite;
        }
        
        /* Mobile optimization */
        @media (max-width: 768px) {
            .gallery-container {
                height: 65vh;
            }
            
            .page-title {
                margin-top: 1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="mobile-optimized">
    <div class="gallery-bg"></div>
    
    <div class="gallery-page">
        <h1 class="page-title">Thư Viện Ảnh</h1>   
        
        <div class="gallery-container">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
            </div>
            <div id="galleryCachedContent" class="gallery-content"></div>
        </div>
        <!-- Status indicator for cache freshness -->
        <div id="cacheStatus" class="cache-status">
            <span class="cache-indicator fresh" title="Đang sử dụng phiên bản mới nhất"></span>
            <span class="cache-text">Đã tải xong</span>
        </div>
        
        <a href="<?php echo $home_link; ?>" class="back-button hardware-accelerated">
            <i class="fas fa-arrow-left" style="margin-right: 0.5em;"></i>
        </a>
        
    </div>
    
    <script>
        // Gallery caching and loading system
        class GalleryCacheManager {
            constructor(config) {
                this.config = config;
                this.contentElement = document.getElementById('galleryCachedContent');
                this.loadingOverlay = document.getElementById('loadingOverlay');
                this.cacheStatus = document.getElementById('cacheStatus');
                this.cacheIndicator = this.cacheStatus.querySelector('.cache-indicator');
                this.cacheText = this.cacheStatus.querySelector('.cache-text');
                this.refreshTimer = null;
                this.currentETag = null;
                this.isRefreshing = false;
                
                // Add refresh button to gallery container
                const galleryContainer = document.querySelector('.gallery-container');
                this.refreshButton = document.createElement('button');
                this.refreshButton.className = 'refresh-button';
                this.refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i>';
                this.refreshButton.title = 'Làm mới thư viện';
                this.refreshButton.addEventListener('click', () => this.refreshContent(true));
                galleryContainer.appendChild(this.refreshButton);
                
                // Initialize
                this.init();
            }
            
            init() {
                // Initial load
                this.loadContent();
                
                // Set up periodic check
                this.startRefreshTimer();
                
                // Check for updates when tab gets focus
                if (this.config.checkOnFocus) {
                    window.addEventListener('focus', () => this.checkForUpdates());
                }
                
                // Handle offline/online status
                window.addEventListener('online', () => {
                    this.updateCacheStatus('online');
                    this.refreshContent(true);
                });
                
                window.addEventListener('offline', () => {
                    this.updateCacheStatus('offline');
                });
            }
            
            startRefreshTimer() {
                // Clear any existing timer
                if (this.refreshTimer) {
                    clearInterval(this.refreshTimer);
                }
                
                // Start new timer
                this.refreshTimer = setInterval(() => {
                    this.checkForUpdates();
                }, this.config.refreshInterval);
            }
            
            checkForUpdates() {
                if (this.isRefreshing || !navigator.onLine) return;
                
                fetch(`${this.config.cacheApi}?action=status&t=${Date.now()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.needs_refresh || (this.currentETag && this.currentETag !== data.etag)) {
                            this.refreshContent();
                        }
                    })
                    .catch(err => {
                        console.error('Failed to check for updates:', err);
                    });
            }
            
            loadContent() {
                this.showLoading();
                
                fetch(`${this.config.cacheApi}?action=get&force_refresh=${this.config.forceRefresh}&t=${Date.now()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.renderContent(data.content);
                            this.currentETag = data.etag;
                            
                            // Update status
                            const fromCache = data.cached || false;
                            const status = data.fallback ? 'fallback' : (fromCache ? 'cached' : 'fresh');
                            this.updateCacheStatus(status, data.timestamp);
                        } else {
                            this.showError('Không thể tải thư viện ảnh');
                        }
                    })
                    .catch(err => {
                        console.error('Failed to load gallery:', err);
                        this.showError('Lỗi kết nối');
                    })
                    .finally(() => {
                        this.hideLoading();
                    });
            }
            
            refreshContent(userInitiated = false) {
                if (this.isRefreshing) return;
                
                this.isRefreshing = true;
                this.updateCacheStatus('refreshing');
                
                if (userInitiated) {
                    // Visual feedback for user-initiated refresh
                    this.refreshButton.classList.add('spinning');
                }
                
                fetch(`${this.config.cacheApi}?action=refresh&t=${Date.now()}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Re-load the content with the new cache
                            this.loadContent();
                        } else {
                            this.updateCacheStatus('error');
                        }
                    })
                    .catch(err => {
                        console.error('Failed to refresh gallery:', err);
                        this.updateCacheStatus('error');
                    })
                    .finally(() => {
                        this.isRefreshing = false;
                        this.refreshButton.classList.remove('spinning');
                    });
            }
            
            renderContent(content) {
                // We need to sanitize and extract just the relevant parts from Google Drive content
                const parser = new DOMParser();
                const doc = parser.parseFromString(content, 'text/html');
                
                // Find all image elements and grid items
                const gridItems = doc.querySelectorAll('.grid-item') || [];
                
                if (gridItems.length > 0) {
                    // Create our own grid container
                    let gridContainer = document.createElement('div');
                    gridContainer.className = 'drive-grid';
                    gridContainer.style.cssText = 'display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 10px;';
                    
                    // Add each item to our grid
                    gridItems.forEach(item => {
                        gridContainer.appendChild(item.cloneNode(true));
                    });
                    
                    this.contentElement.innerHTML = '';
                    this.contentElement.appendChild(gridContainer);
                    
                    // Handle images or other interactive elements
                    this.setupGridInteraction();
                } else {
                    // Fallback to iframe if we can't parse the content properly
                    this.contentElement.innerHTML = `
                        <div style="width:100%; height:100%; overflow:hidden;">
                            <iframe src="https://drive.google.com/embeddedfolderview?id=17xlDHqSfzBmxkAbckbkeMJTPUYMVJT_1#grid"
                                    frameborder="0"
                                    style="width:100%; height:100%; border:none; zoom:0.97; -ms-zoom:0.97; -webkit-transform:scale(0.97); -webkit-transform-origin:0 0; overflow:hidden; scrollbar-width:none;"></iframe>
                        </div>
                    `;
                }
            }
            
            setupGridInteraction() {
                // Add click handlers or other interactive elements if needed
                const items = this.contentElement.querySelectorAll('.drive-grid .grid-item');
                items.forEach(item => {
                    const link = item.querySelector('a');
                    if (link) {
                        // Make sure links open properly
                        link.target = '_blank';
                        link.rel = 'noopener noreferrer';
                    }
                });
            }
            
            showLoading() {
                if (this.loadingOverlay) {
                    this.loadingOverlay.style.display = 'flex';
                    setTimeout(() => {
                        this.loadingOverlay.style.opacity = '1';
                    }, 10);
                }
            }
            
            hideLoading() {
                if (this.loadingOverlay) {
                    this.loadingOverlay.style.opacity = '0';
                    setTimeout(() => {
                        this.loadingOverlay.style.display = 'none';
                    }, 500);
                }
            }
            
            showError(message) {
                this.contentElement.innerHTML = `
                    <div style="padding: 2rem; text-align: center;">
                        <h2 style="color: #f4f4f5; margin-bottom: 1rem;">${message}</h2>
                        <p style="color: #a1a1aa; margin-bottom: 1.5rem;">Vui lòng kiểm tra kết nối mạng của bạn và thử lại sau.</p>
                        <button onclick="galleryManager.refreshContent(true)" 
                                style="padding: 0.5rem 1.5rem; background: rgba(99, 102, 241, 0.2); color: #e4e4e7; 
                                border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; cursor: pointer;">
                            Tải lại
                        </button>
                    </div>
                `;
            }
            
            updateCacheStatus(status, timestamp = null) {
                switch(status) {
                    case 'fresh':
                        this.cacheIndicator.className = 'cache-indicator fresh';
                        this.cacheText.textContent = 'Cập nhật mới nhất';
                        break;
                        
                    case 'cached':
                        this.cacheIndicator.className = 'cache-indicator fresh';
                        if (timestamp) {
                            const date = new Date(timestamp * 1000);
                            const timeAgo = this.getTimeAgo(date);
                            this.cacheText.textContent = `Đã cập nhật ${timeAgo}`;
                        } else {
                            this.cacheText.textContent = 'Đã lưu trữ';
                        }
                        break;
                        
                    case 'stale':
                        this.cacheIndicator.className = 'cache-indicator stale';
                        this.cacheText.textContent = 'Đang kiểm tra cập nhật';
                        break;
                        
                    case 'fallback':
                        this.cacheIndicator.className = 'cache-indicator stale';
                        this.cacheText.textContent = 'Phiên bản dự phòng';
                        break;
                        
                    case 'refreshing':
                        this.cacheIndicator.className = 'cache-indicator refreshing';
                        this.cacheText.textContent = 'Đang làm mới';
                        break;
                        
                    case 'offline':
                        this.cacheIndicator.className = 'cache-indicator stale';
                        this.cacheText.textContent = 'Đang ngoại tuyến';
                        break;
                        
                    case 'online':
                        this.cacheIndicator.className = 'cache-indicator fresh';
                        this.cacheText.textContent = 'Đã kết nối';
                        break;
                        
                    case 'error':
                        this.cacheIndicator.className = 'cache-indicator stale';
                        this.cacheText.textContent = 'Lỗi cập nhật';
                        break;
                }
            }
            
            getTimeAgo(date) {
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // Seconds
                
                if (diff < 60) return 'vừa xong';
                if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
                if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
                if (diff < 2592000) return `${Math.floor(diff / 86400)} ngày trước`;
                
                // Format date for older content
                return `${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear()}`;
            }
        }
        
        // Initialize gallery manager when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            window.galleryManager = new GalleryCacheManager(GALLERY_CACHE_CONFIG);
        });
    </script>
</body>
</html>
<?php
// public/Info.php

// Prevent caching for mobile devices
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Generate cache busting timestamp
$cache_bust = time();

// Define available locations and their display names
$locations = [
    'hanoi' => 'Hà Nội',
    'hue' => 'Huế',
    'danang' => 'Đà Nẵng',
    'hoian' => 'Hội An'
];

// Get location from URL or set default to Cat Ba
$currentLocation = isset($_GET['location']) ? $_GET['location'] : 'catba';

// Validate location
if (!array_key_exists($currentLocation, $locations)) {
    $currentLocation = 'catba';
}

// Load contact data from JSON file based on location
$jsonPath = 'data/contacts-' . $currentLocation . '.json';
$contactsJson = file_exists($jsonPath) ? file_get_contents($jsonPath) : '{}';
$contacts = json_decode($contactsJson, true);

// Function to check if data exists
function hasData($data) {
    return !empty($data) && is_array($data) && count($data) > 0;
}

// Function to get location name display
function getLocationName($key, $locations) {
    return isset($locations[$key]) ? $locations[$key] : $key;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Thông Tin Liên Lạc</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cache_bust; ?>">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?php echo $cache_bust; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .contact-page-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(217, 70, 239, 0.06) 0%, transparent 50%),
                #0a0a0b;
            animation: float 6s ease-in-out infinite;
        }
        
        .header {
            position: relative;
        }
        
        .contacts-container {
            max-width: 800px;
            margin: 2rem auto 2rem;
            padding: 0 1rem;
        }
        
        .location-selector {
            margin-bottom: 1.5rem;
            background: rgba(24, 24, 27, 0.7);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .location-label {
            font-size: 0.9rem;
            color: #a1a1aa;
            margin-bottom: 0.5rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .location-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .location-option {
            padding: 0.6rem 1.2rem;
            background: rgba(15, 15, 15, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            color: #e4e4e7;
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .location-option:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }
        
        .location-option.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
            transform: translateY(-2px);
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            justify-content: center;
        }
        
        .filter-option {
            padding: 0.5rem 1rem;
            background: rgba(24, 24, 27, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #a1a1aa;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            outline: none;
            font-family: inherit;
            appearance: none;
        }
        
        .filter-option.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            color: white;
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
        }
        
        .contact-book {
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 4px 16px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            max-height: 60vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #6366f1 #27272a;
        }
        
        .contact-book::-webkit-scrollbar {
            width: 6px;
        }
        
        .contact-book::-webkit-scrollbar-track {
            background: #27272a;
            border-radius: 10px;
        }
        
        .contact-book::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 10px;
        }
        
        .contact-group {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .contact-group.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .contact-item {
            background: rgba(15, 15, 15, 0.6);
            margin-bottom: 1rem;
            border-radius: 14px;
            padding: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .contact-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #6366f1, #8b5cf6, #d946ef);
            opacity: 0.7;
        }
        
        .contact-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.2);
        }
        
        .contact-item h3 {
            margin: 0 0 0.5rem;
            font-size: 1.1rem;
            color: #f4f4f5;
            letter-spacing: -0.01em;
            font-weight: 600;
        }
        
        .contact-item p {
            margin: 0.3rem 0;
            font-size: 0.95rem;
            color: #a1a1aa;
            display: flex;
            align-items: center;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
            z-index: 1000;
            overflow: hidden;
        }
        
        .back-button svg {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }
        
        .back-button:hover {
            transform: scale(1.1);
            box-shadow: 
                0 6px 16px rgba(99, 102, 241, 0.4),
                0 3px 8px rgba(139, 92, 246, 0.3);
        }
        
        .back-button:hover svg {
            transform: translateX(-3px);
        }
        
        .back-button::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 100%
            );
            transform: rotate(45deg);
            z-index: 1;
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .back-button:hover::before {
            animation: shine 1.5s ease;
        }
        
        /* Emergency contact styling */
        .contact-group.emergency .contact-item {
            border-left: none;
            background: rgba(239, 68, 68, 0.1);
        }
        
        .contact-group.emergency .contact-item::before {
            background: linear-gradient(to bottom, #ef4444, #b91c1c);
        }
        
        /* Icon styling for contacts */
        .icon {
            display: inline-block;
            margin-right: 8px;
            width: 18px;
            height: 18px;
            vertical-align: middle;
            fill: currentColor;
            opacity: 0.8;
        }
        
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-container {
            width: 90%;
            max-width: 500px;
            background: rgba(24, 24, 27, 0.95);
            backdrop-filter: blur(16px);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 10px 24px rgba(0, 0, 0, 0.3);
            transform: translateY(20px);
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-overlay.active .modal-container {
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-header h3 {
            margin: 0;
            color: #f4f4f5;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .modal-close {
            background: transparent;
            border: none;
            color: #a1a1aa;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .modal-close:hover {
            color: #f4f4f5;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .modal-close svg {
            width: 20px;
            height: 20px;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .location-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
        }
        
        .location-card {
            background: rgba(15, 15, 15, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.25rem 0.75rem;
            text-align: center;
            color: #e4e4e7;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .location-card:hover {
            background: rgba(99, 102, 241, 0.2);
            transform: translateY(-3px);
        }
        
        .location-card.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
        }
        
        .location-icon {
            width: 32px;
            height: 32px;
            stroke-width: 1.5;
        }
        
        .location-card span {
            font-weight: 500;
            font-size: 1rem;
        }
        
        /* Additional runtime styles */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Special hover states for contact items */
        .contact-item p:has(svg) {
            transition: all 0.2s ease;
        }
        
        .contact-item p:has(svg):hover {
            color: #8b5cf6;
            transform: translateX(5px);
        }
        
        /* Add smooth scrolling for the document */
        html {
            scroll-behavior: smooth;
        }
        
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            .contact-group.active {
                animation: none;
            }
            
            .back-link::before {
                animation: none;
            }
            
            html {
                scroll-behavior: auto;
            }
        }
        
        /* Location modal button */
        .location-modal-button {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
            z-index: 1000;
            overflow: hidden;
        }
        
        .location-modal-button svg {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }
        
        .location-modal-button:hover {
            transform: scale(1.1);
            box-shadow: 
                0 6px 16px rgba(99, 102, 241, 0.4),
                0 3px 8px rgba(139, 92, 246, 0.3);
        }
        
        .location-modal-button:hover svg {
            transform: scale(1.1);
        }
        
        .location-modal-button::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 100%
            );
            transform: rotate(45deg);
            z-index: 1;
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .location-modal-button:hover::before {
            animation: shine 1.5s ease;
        }
        
        /* Enhance modal header with gradient background */
        .modal-header {
            background: linear-gradient(to right, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
        }
        
        /* Current location indicator in modal title */
        .location-card.active::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 14px;
            color: white;
        }
        
        /* Add pulsing animation to location button icon */
        .location-pulse {
            animation: pulse 2s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Position the location button in the bottom right */
        .location-fixed-button {
            right: 20px !important;
            left: auto !important;
            bottom: 20px !important;
            width: 56px !important;
            height: 56px !important;
            z-index: 1001 !important;
        }
    </style>
</head>
<body class="no-select mobile-optimized">
    <div class="contact-page-bg"></div>
    
    <a href="index.php" class="back-button" aria-label="Back to home">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
    </a>
    
    <!-- Location modal button -->
    <button class="location-modal-button location-fixed-button" aria-label="Change location" id="openLocationModal">
        <svg class="location-pulse" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
        </svg>
    </button>
    
    <!-- Location selection modal -->
    <div class="modal-overlay" id="locationModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Chọn Địa Điểm</h3>
                <button class="modal-close" id="closeLocationModal" aria-label="Close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">            <div class="location-options-grid">
                <?php foreach ($locations as $key => $name): ?>
                <a href="?location=<?php echo $key; ?>" class="location-card <?php echo ($currentLocation == $key) ? 'active' : ''; ?>">
                    <span><?php echo $name; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            </div>
        </div>
    </div>
    
    <header class="header">
        <h1>Thông Tin Liên Lạc</h1>
        <h2>Danh bạ & liên hệ quan trọng - <?php echo getLocationName($currentLocation, $locations); ?></h2>
    </header>
    
    <div class="contacts-container">
        <!-- Contact type filter -->
        <div class="filter-options">
            <button class="filter-option" data-group="all">Tất cả</button>
            <button class="filter-option" data-group="friends">Các Fen</button>
            <button class="filter-option" data-group="hotels">Khách sạn</button>    
            <button class="filter-option" data-group="restaurants">Ăn trưa, tối</button>  
            <button class="filter-option" data-group="emergency">Khẩn cấp</button>    
        </div>
        
        <div class="contact-book">
            <?php if (hasData($contacts['hotels'])): ?>
            <div class="contact-group hotels" id="hotels-group">
                <?php foreach ($contacts['hotels'] as $hotel): ?>
                <div class="contact-item">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <?php if (!empty($hotel['address'])): ?>
                    <p>
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?php echo htmlspecialchars($hotel['address']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($hotel['phone'])): ?>
                    <p class="phone-number" data-phone="<?php echo htmlspecialchars($hotel['phone']); ?>">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <?php echo htmlspecialchars($hotel['phone']); ?>
                    </p>
                    <?php endif; ?>         
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (hasData($contacts['friends'])): ?>
            <div class="contact-group friends" id="friends-group">
                <?php foreach ($contacts['friends'] as $friend): ?>
                <div class="contact-item">
                    <h3><?php echo htmlspecialchars($friend['name']); ?></h3>
                    <?php if (!empty($friend['phone'])): ?>
                    <p class="phone-number" data-phone="<?php echo htmlspecialchars($friend['phone']); ?>">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <?php echo htmlspecialchars($friend['phone']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (hasData($contacts['restaurants'])): ?>
            <div class="contact-group restaurants" id="restaurants-group">
                <?php foreach ($contacts['restaurants'] as $restaurant): ?>
                <div class="contact-item">
                    <h3><?php echo htmlspecialchars($restaurant['name']); ?></h3>
                    <?php if (!empty($restaurant['address'])): ?>
                    <p>
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?php echo htmlspecialchars($restaurant['address']); ?>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['phone'])): ?>
                    <p class="phone-number" data-phone="<?php echo htmlspecialchars($restaurant['phone']); ?>">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <?php echo htmlspecialchars($restaurant['phone']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (hasData($contacts['emergency'])): ?>
            <div class="contact-group emergency" id="emergency-group">
                <?php foreach ($contacts['emergency'] as $emergency): ?>
                <div class="contact-item">
                    <h3><?php echo htmlspecialchars($emergency['name']); ?></h3>
                    <?php if (!empty($emergency['phone'])): ?>
                    <p class="phone-number" data-phone="<?php echo htmlspecialchars($emergency['phone']); ?>">
                        <?php if (strpos($emergency['name'], 'Cứu hộ') !== false): ?>
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?php elseif (strpos($emergency['name'], 'y tế') !== false): ?>
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11h-4a3 3 0 0 0-3 3v4a3 3 0 0 0 3 3h1a3 3 0 0 0 3-3v-7z"></path>
                            <path d="M11 11H7a3 3 0 0 0-3 3v4a3 3 0 0 0 3 3h4a3 3 0 0 0 3-3v-4a3 3 0 0 0-3-3z"></path>
                            <path d="M15.73 5a2 2 0 0 0-3.46 0A7 7 0 0 1 8 8.5a7 7 0 0 1-2.55-1.58 2 2 0 0 0-3.2.25A6.97 6.97 0 0 0 2 12h15c0-2.38-.97-4.5-2.27-6z"></path>
                        </svg>
                        <?php else: ?>
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($emergency['phone']); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Show message if no contacts are found -->
            <?php if (empty($contacts)): ?>
            <div class="no-contacts">
                <p>Không tìm thấy thông tin liên lạc nào cho <?php echo getLocationName($currentLocation, $locations); ?>.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Show "all" by default - fix for filter functionality
            const showAllContacts = () => {
                document.querySelectorAll('.contact-group').forEach(group => {
                    group.classList.add('active');
                });
            };
            
            // Call this function on page load
            showAllContacts();
            
            // Modal functionality
            const locationModal = document.getElementById('locationModal');
            const openLocationModal = document.getElementById('openLocationModal');
            const closeLocationModal = document.getElementById('closeLocationModal');
            
            // Open modal
            openLocationModal.addEventListener('click', () => {
                locationModal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
            
            // Close modal
            closeLocationModal.addEventListener('click', () => {
                locationModal.classList.remove('active');
                document.body.style.overflow = ''; // Re-enable scrolling
            });
            
            // Close modal when clicking outside
            locationModal.addEventListener('click', (e) => {
                if (e.target === locationModal) {
                    locationModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            
            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && locationModal.classList.contains('active')) {
                    locationModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
            
            // Improved filter functionality
            const filterOptions = document.querySelectorAll('.filter-option');
            
            filterOptions.forEach(option => {
                option.addEventListener('click', () => {
                    const selectedGroup = option.getAttribute('data-group');
                    
                    // Update active filter buttons
                    filterOptions.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');
                    
                    // Handle contact group visibility with better animation
                    const contactGroups = document.querySelectorAll('.contact-group');
                    
                    if (selectedGroup === 'all') {
                        // Show all groups
                        contactGroups.forEach(group => {
                            group.style.display = 'block';
                            setTimeout(() => {
                                group.classList.add('active');
                            }, 10);
                        });
                    } else {
                        // Hide all groups first
                        contactGroups.forEach(group => {
                            group.classList.remove('active');
                            // Use setTimeout to let animation finish before hiding completely
                            setTimeout(() => {
                                if (!group.classList.contains(selectedGroup)) {
                                    group.style.display = 'none';
                                }
                            }, 300);
                        });
                        
                        // Show the selected group
                        const targetGroup = document.getElementById(selectedGroup + '-group');
                        if (targetGroup) {
                            targetGroup.style.display = 'block';
                            // Small delay to ensure display change is applied before animation
                            setTimeout(() => {
                                targetGroup.classList.add('active');
                            }, 20);
                        }
                    }
                });
            });
            
            // Add click-to-call functionality for phone numbers
            const phoneNumbers = document.querySelectorAll('.phone-number');
            phoneNumbers.forEach(phone => {
                phone.style.cursor = 'pointer';
                phone.title = 'Nhấn để gọi';
                phone.addEventListener('click', function() {
                    const number = this.getAttribute('data-phone');
                    if (number) {
                        window.location.href = `tel:${number}`;
                    }
                });
            });
            
            // Add touch ripple effect to contacts
            const contactItems = document.querySelectorAll('.contact-item');
            contactItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Don't trigger ripple when clicking on phone number directly
                    if (e.target.closest('.phone-number')) return;
                    
                    const ripple = document.createElement('div');
                    ripple.classList.add('ripple');
                    this.appendChild(ripple);
                    
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    
                    ripple.style.width = ripple.style.height = `${size}px`;
                    ripple.style.left = `${e.clientX - rect.left - size / 2}px`;
                    ripple.style.top = `${e.clientY - rect.top - size / 2}px`;
                    
                    ripple.classList.add('active');
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Modal functionality for location selection
            const openModalButton = document.getElementById('openLocationModal');
            const closeModalButton = document.getElementById('closeLocationModal');
            const modalOverlay = document.getElementById('locationModal');
            
            openModalButton.addEventListener('click', () => {
                modalOverlay.style.display = 'flex';
                setTimeout(() => {
                    modalOverlay.classList.add('active');
                }, 10);
            });
            
            closeModalButton.addEventListener('click', () => {
                modalOverlay.classList.remove('active');
                setTimeout(() => {
                    modalOverlay.style.display = 'none';
                }, 300);
            });
            
            // Close modal when clicking outside of it
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    closeModalButton.click();
                }
            });
        });
    </script>
    
    <style>
        /* Additional runtime styles */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Special hover states for contact items */
        .contact-item p:has(svg) {
            transition: all 0.2s ease;
        }
        
        .contact-item p:has(svg):hover {
            color: #8b5cf6;
            transform: translateX(5px);
        }
        
        /* Add smooth scrolling for the document */
        html {
            scroll-behavior: smooth;
        }
        
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            .contact-group.active {
                animation: none;
            }
            
            .back-link::before {
                animation: none;
            }
            
            html {
                scroll-behavior: auto;
            }
        }
        
        /* Location modal button */
        .location-modal-button {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 
                0 4px 12px rgba(99, 102, 241, 0.3),
                0 2px 6px rgba(139, 92, 246, 0.2);
            z-index: 1000;
            overflow: hidden;
        }
        
        .location-modal-button svg {
            width: 24px;
            height: 24px;
            transition: transform 0.3s ease;
        }
        
        .location-modal-button:hover {
            transform: scale(1.1);
            box-shadow: 
                0 6px 16px rgba(99, 102, 241, 0.4),
                0 3px 8px rgba(139, 92, 246, 0.3);
        }
        
        .location-modal-button:hover svg {
            transform: scale(1.1);
        }
        
        .location-modal-button::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.1) 100%
            );
            transform: rotate(45deg);
            z-index: 1;
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .location-modal-button:hover::before {
            animation: shine 1.5s ease;
        }
        
        /* Enhance modal header with gradient background */
        .modal-header {
            background: linear-gradient(to right, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
        }
        
        /* Current location indicator in modal title */
        .location-card.active::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 14px;
            color: white;
        }
        
        /* Add pulsing animation to location button icon */
        .location-pulse {
            animation: pulse 2s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Position the location button in the bottom right */
        .location-fixed-button {
            right: 20px !important;
            left: auto !important;
            bottom: 20px !important;
            width: 56px !important;
            height: 56px !important;
            z-index: 1001 !important;
        }
    </style>
</body>
</html>
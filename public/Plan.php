<?php
// public/Plan.php

// Prevent caching for mobile devices
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Generate cache busting timestamp
$cache_bust = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Travel Schedule - Hue, Danang & Hoian</title>
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cache_bust; ?>">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            padding-top: 2rem;
        }
        
        .plan-page-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: backgroundShift 30s ease-in-out infinite;
        }
        
        @keyframes backgroundShift {
            0%, 100% { opacity: 0.8; }
            50% { opacity: 1; }
        }
        
        .back-button {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: rgba(15, 23, 42, 0.95);
            color: #e4e4e7;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .back-button:hover {
            background: rgba(30, 41, 59, 0.95);
            color: #8b5cf6;
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(139, 92, 246, 0.2);
        }
        
        .plan-header {
            text-align: center;
            padding: 2rem 1rem;
            margin-bottom: 2rem;
        }
        
        .plan-header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .plan-header h2 {
            font-size: 1.125rem;
            color: #a1a1aa;
            font-weight: 400;
            margin-bottom: 1rem;
        }
        
        .current-time {
            display: inline-block;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }
        
        .schedule-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }
        
        .table-container {
            background: rgba(15, 23, 42, 0.95);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        .schedule-table thead {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .schedule-table th {
            padding: 1rem 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #f4f4f5;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .schedule-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .schedule-table tbody tr:hover {
            background: rgba(139, 92, 246, 0.1);
            transform: translateX(4px);
        }
        
        .schedule-table td {
            padding: 0.875rem 0.75rem;
            color: #e4e4e7;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .schedule-table .date-cell {
            font-weight: 600;
            color: #8b5cf6;
            white-space: nowrap;
        }
        
        .schedule-table .location-cell {
            font-weight: 500;
            color: #06b6d4;
        }
        
        .schedule-table .map-cell {
            color: #10b981;
            font-weight: 500;
        }
        
        .schedule-table .time-cell {
            font-weight: 600;
            color: #f59e0b;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        
        .schedule-table .task-cell {
            line-height: 1.5;
            color: #d4d4d8;
        }
        
        .highlight {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(6, 182, 212, 0.2) 100%) !important;
            border-left: 4px solid #10b981 !important;
            transform: translateX(8px) !important;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2) !important;
        }
        
        .highlight .date-cell,
        .highlight .location-cell,
        .highlight .time-cell,
        .highlight .task-cell {
            color: #f4f4f5 !important;
            font-weight: 600 !important;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #a1a1aa;
            font-size: 1.1rem;
        }
        
        .loading::after {
            content: '...';
            animation: dots 1.5s infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: ''; }
            40% { content: '.'; }
            60% { content: '..'; }
            80%, 100% { content: '...'; }
        }
        
        @media (max-width: 768px) {
            .plan-header {
                padding: 1rem;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 0.5rem 0.375rem;
                font-size: 0.8rem;
            }
            
            .schedule-table th {
                font-size: 0.75rem;
            }
            
            .table-container {
                margin: 0 0.5rem;
                border-radius: 0.75rem;
            }
            
            .current-time {
                font-size: 0.875rem;
                padding: 0.375rem 0.75rem;
            }
        }
        
        /* Column widths */
        .schedule-table th:nth-child(1),
        .schedule-table td:nth-child(1) { width: 18%; }
        .schedule-table th:nth-child(2),
        .schedule-table td:nth-child(2) { width: 12%; }
        .schedule-table th:nth-child(3),
        .schedule-table td:nth-child(3) { width: 15%; }
        .schedule-table th:nth-child(4),
        .schedule-table td:nth-child(4) { width: 15%; }
        .schedule-table th:nth-child(5),
        .schedule-table td:nth-child(5) { width: 40%; }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="plan-page-bg"></div>

    <!-- Back Button -->
    <a href="index.php" class="back-button" title="Back to Menu">
        <
    </a>

    <!-- Page Header -->
    <div class="plan-header">
        <h1>Travel Schedule</h1>
        <h2>Hue - Danang - Hoian </h2>
        <div class="current-time" id="currentTime">Loading...</div>
    </div>

    <!-- Schedule Container -->
    <div class="schedule-container">
        <div class="table-container">
            <div class="loading" id="loading">Loading schedule data</div>
            <table class="schedule-table" id="scheduleTable" style="display: none;">
                <thead>
                    <tr>
                        <th>Days</th>
                        <th>Location</th>
                        <th>Map</th>
                        <th>Time</th>
                        <th>Tasks</th>
                    </tr>
                </thead>
                <tbody id="scheduleTableBody">
                    <!-- Data will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cache Busting Script -->
    <script src="js/cache-buster.js?v=<?php echo $cache_bust; ?>"></script>
    
    <script>
        let scheduleData = [];
        
        // Load schedule data from JSON
        async function loadScheduleData() {
            try {
                const response = await fetch('data/schedule.json?v=<?php echo $cache_bust; ?>');
                if (!response.ok) {
                    throw new Error('Failed to load schedule data');
                }
                scheduleData = await response.json();
                renderScheduleTable();
                document.getElementById('loading').style.display = 'none';
                document.getElementById('scheduleTable').style.display = 'table';
            } catch (error) {
                console.error('Error loading schedule data:', error);
                document.getElementById('loading').innerHTML = 'Failed to load schedule data. Please refresh the page.';
            }
        }
        
        // Render schedule table
        function renderScheduleTable() {
            const tbody = document.getElementById('scheduleTableBody');
            tbody.innerHTML = '';
            
            scheduleData.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="date-cell">${item.date}</td>
                    <td class="location-cell">${item.location}</td>
                    <td class="map-cell">${item.map}</td>
                    <td class="time-cell">${item.time}</td>
                    <td class="task-cell">${item.task}</td>
                `;
                tbody.appendChild(row);
            });
        }
        
        // Update current time display
        function updateCurrentTime() {
            const now = new Date();
            const vietnamTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Ho_Chi_Minh' }));
            const currentDate = vietnamTime.getDate().toString().padStart(2, '0') + '/' + (vietnamTime.getMonth() + 1).toString().padStart(2, '0');
            const currentTime = vietnamTime.getHours().toString().padStart(2, '0') + ':' + vietnamTime.getMinutes().toString().padStart(2, '0');
            
            document.getElementById('currentTime').textContent = `${currentDate} ${currentTime}`;
        }

        // Highlight current event
        function highlightCurrentEvent() {
            if (scheduleData.length === 0) return;
            
            updateCurrentTime();

            const now = new Date();
            const vietnamTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Ho_Chi_Minh' }));
            const currentDate = vietnamTime.getDate().toString().padStart(2, '0') + '/' + (vietnamTime.getMonth() + 1).toString().padStart(2, '0');
            const currentTime = vietnamTime.getHours().toString().padStart(2, '0') + ':' + vietnamTime.getMinutes().toString().padStart(2, '0');

            const rows = document.querySelectorAll('#scheduleTable tbody tr');
            let currentDay = '';
            let lastMatchingRow = null;

            rows.forEach((row, index) => {
                const item = scheduleData[index];
                
                // Remove existing highlight
                row.classList.remove('highlight');
                
                if (item.date.trim() !== '') {
                    currentDay = item.date.trim();
                }

                if (currentDay === currentDate) {
                    const eventTime = item.time.trim();
                    if (eventTime <= currentTime) {
                        lastMatchingRow = row;
                    }
                }
            });

            if (lastMatchingRow) {
                lastMatchingRow.classList.add('highlight');
                // Scroll to highlighted row
                setTimeout(() => {
                    const container = document.querySelector('.table-container');
                    const rowTop = lastMatchingRow.offsetTop - container.offsetTop;
                    const containerHeight = container.clientHeight;
                    const rowHeight = lastMatchingRow.offsetHeight;
                    
                    if (rowTop < container.scrollTop || rowTop + rowHeight > container.scrollTop + containerHeight) {
                        container.scrollTo({
                            top: Math.max(0, rowTop - containerHeight / 2),
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            }
        }

        // Initialize page
        async function initializePage() {
            await loadScheduleData();
            highlightCurrentEvent();
            
            // Update time every minute
            setInterval(updateCurrentTime, 60000);
            
            // Update highlights every 30 seconds
            setInterval(highlightCurrentEvent, 30000);
        }

        // Start when page loads
        window.addEventListener('load', initializePage);
    </script>
</body>
</html>

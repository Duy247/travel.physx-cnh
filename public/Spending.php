<?php
// public/Spending.php

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?php echo $cache_bust; ?>">
    <title>Quản Lý Chi Tiêu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 400;
            line-height: 1.6;
            color: #e4e4e7;
            background: #0a0a0b;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Animated Background */
        .spending-page-bg {
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

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .header h1,
        .header h2 {
            position: relative;
            z-index: 1;
        }

        .header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            color: #f4f4f5;
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .header h2 {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Expense Form */
        .expense-form-container {
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 4px 16px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .form-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 1rem;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: #f4f4f5;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            background: rgba(39, 39, 42, 0.9);
        }

        .form-input::placeholder {
            color: #a1a1aa;
        }

        .formatted-amount {
            margin-top: 0.5rem;
            color: #6366f1;
            font-size: 0.9rem;
            font-weight: 500;
            min-height: 1.2rem;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Floating Add Button */
        .floating-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Floating Filter Button */
        .floating-filter-btn {
            position: fixed;
            bottom: 8rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .floating-filter-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
        }

        .floating-filter-btn:active {
            transform: translateY(-1px) scale(1.02);
        }

        .floating-add-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.5);
        }

        /* Tabs */
        .tabs-container {
            background: rgba(24, 24, 27, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .tabs-header {
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tab-button {
            flex: 1;
            padding: 1rem 2rem;
            background: transparent;
            color: #a1a1aa;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-family: 'Montserrat', sans-serif;
        }

        .tab-button.active {
            color: #f4f4f5;
            background: rgba(99, 102, 241, 0.1);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .tab-button:hover:not(.active) {
            color: #e4e4e7;
            background: rgba(255, 255, 255, 0.05);
        }

        .tabs-content {
            position: relative;
            overflow: hidden;
        }

        .tab-panel {
            padding: 2rem;
            display: none;
            min-height: 400px;
        }

        .tab-panel.active {
            display: block;
        }

        /* Swipe container for mobile */
        .swipe-tabs-container {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .swipe-tabs-container::-webkit-scrollbar {
            display: none;
        }

        .swipe-tab-panel {
            flex: 0 0 100%; /* Changed from 100% to 33.333% for 3 tabs */
            scroll-snap-align: start;
            padding: 2rem;
            min-height: 400px;
        }

        .swipe-tab-panel.no-pading {
            flex: 0 0 100%; /* Changed from 100% to 33.333% for 3 tabs */
            scroll-snap-align: start;
            padding: 0rem;
            min-height: 400px;
        }

        /* Filter Section */
        .filter-container {
            background: rgba(39, 39, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            color: #f4f4f5;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-select,
        .filter-input {
            padding: 0.75rem;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #f4f4f5;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        /* Fix date input styling */
        .filter-input[type="date"] {
            color-scheme: dark;
            position: relative;
        }

        .filter-input[type="date"]::-webkit-calendar-picker-indicator {
            background-color:#7b5ff4;
            padding: 4px;
            border-radius: 3px;
            cursor: pointer;
        }

        .filter-input[type="date"]::-webkit-inner-spin-button,
        .filter-input[type="date"]::-webkit-clear-button {
            display: none;
            -webkit-appearance: none;
        }

        .clear-filters-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            align-self: flex-end;
        }

        .clear-filters-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Expenses List */
        .expense-list {
            max-height: 500px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .expense-list::-webkit-scrollbar {
            width: 6px;
        }

        .expense-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .expense-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .expense-item {
            background: rgba(39, 39, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .expense-item:hover {
            background: rgba(39, 39, 42, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .expense-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .expense-text {
            color: #f4f4f5;
            font-weight: 500;
            flex: 1;
        }

        .expense-amount {
            color: #10b981;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .expense-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #a1a1aa;
            font-size: 0.85rem;
        }

        .expense-payer {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .expense-actions {
            display: flex;
            gap: 0.5rem;
            margin-left: 1rem;
        }

        .btn-edit,
        .btn-delete {
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .btn-edit:hover {
            background: rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }

        /* Summary Section */
        .summary-grid {
            display: grid;
            gap: 1rem;
        }

        .summary-item {
            background: rgba(39, 39, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-payer {
            color: #f4f4f5;
            font-weight: 500;
        }

        .summary-amount {
            color: #10b981;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .balance-item {
            background: rgba(39, 39, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            display: grid;
        }

        .balance-header {
            color: #f4f4f5;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .balance-average {
            color: #8b5cf6;
            font-weight: 600;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            text-align: center;
        }

        .balance-transaction {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .balance-transaction:last-child {
            margin-bottom: 0;
        }

        .transaction-payer {
            color: #f87171;
        }

        .transaction-receiver {
            color: #10b981;
        }

        .transaction-amount {
            font-weight: 600;
            color: #f4f4f5;
        }

        /* Back Link */
        .back-link {
            position: fixed;
            top: 2rem;
            left: 2rem;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.2);
            z-index: 101;
            transition: all 0.3s ease;
        }
        .back-link:hover {
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            transform: translateY(-2px) scale(1.07);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }
        @media (max-width: 768px) {
            .back-link {
            top: 1rem;
            left: 1rem;
            width: 40px;
            height: 40px;
            font-size: 1rem;
            }
        }

        .back-link:hover {
            background: rgba(39, 39, 42, 0.8);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        /* Add Expense Modal */
        .add-expense-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .add-expense-modal-content {
            background: rgba(24, 24, 27, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Edit Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: rgba(24, 24, 27, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-secondary {
            flex: 1;
            padding: 0.75rem 1.5rem;
            background: rgba(39, 39, 42, 0.8);
            color: #f4f4f5;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(82, 82, 91, 0.8);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .expense-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .expense-actions {
                margin-left: 0;
                margin-top: 0.5rem;
            }

            .tabs-header {
                display: none;
            }

            .tabs-content {
                display: block;
            }

            .tab-panel {
                display: none;
            }

            .swipe-tabs-container {
                display: flex;
                max-height: 700px;
            }

            .floating-add-btn {
                bottom: 1rem;
                right: 1rem;
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .floating-filter-btn {
                bottom: 6rem;
                right: 1rem;
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
        }

        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success Message */
        .success-message {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }

        /* Error Message */
        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="spending-page-bg"></div>
    
    <div class="header">
        <h1><i class="fas fa-wallet"></i> Quản Lý Chi Tiêu</h1>
        <h2>Theo dõi và quản lý chi tiêu du lịch</h2>
    </div>

    <div class="container">
        <!-- Filter Section -->
        <div class="filter-container" style="display: none;">
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="filter-payer" class="filter-label">
                        <i class="fas fa-user"></i> Người chi trả
                    </label>
                    <?php
                        // Read members from data/member.json
                        $membersFile = __DIR__ . '/data/member.json';
                        $members = [];
                        if (file_exists($membersFile)) {
                            $json = file_get_contents($membersFile);
                            $members = json_decode($json, true);
                            if (!is_array($members)) $members = [];
                        }
                    ?>
                    <select id="filter-payer" class="filter-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-date-from" class="filter-label">
                        <i class="fas fa-calendar-alt"></i> Từ ngày
                    </label>
                    <input type="date" id="filter-date-from" class="filter-input">
                </div>
                <div class="filter-group">
                    <label for="filter-date-to" class="filter-label">
                        <i class="fas fa-calendar-alt"></i> Đến ngày
                    </label>
                    <input type="date" id="filter-date-to" class="filter-input">
                </div>
                <div class="filter-group">
                    <button class="clear-filters-btn" id="clear-filters">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs Container -->
        <div class="tabs-container">
            <!-- Desktop Tabs Header -->
            <div class="tabs-header">
                <button class="tab-button active" data-tab="expenses">
                    <i class="fas fa-list"></i> Danh Sách Chi Tiêu
                </button>
                <button class="tab-button" data-tab="summary">
                    <i class="fas fa-chart-pie"></i> Thống Kê
                </button>
                <button class="tab-button" data-tab="balance">
                    <i class="fas fa-exchange-alt"></i> Cân Bằng
                </button>
            </div>

            <!-- Desktop Tabs Content -->
            <div class="tabs-content">
                <div id="summary-tab" class="tab-panel">
                    <div id="payer-summary" class="summary-grid"></div>
                </div>
                <div id="balance-tab" class="tab-panel">
                    <div id="balance-summary" class="balance-grid"></div>
                </div>
            </div>

            <!-- Mobile Swipe Tabs -->
            <div class="swipe-tabs-container">
                <div class="swipe-tab-panel">
                    <h3 style="color: #f4f4f5; margin-bottom: 1rem; font-family: 'Montserrat', sans-serif;">
                        <i class="fas fa-list"></i> Chi Tiêu
                    </h3>
                    <div id="mobile-expense-list" class="expense-list"></div>
                </div>
                <div class="swipe-tab-panel">
                    <h3 style="color: #f4f4f5; margin-bottom: 1rem; font-family: 'Montserrat', sans-serif;">
                        <i class="fas fa-chart-pie"></i> Thống Kê
                    </h3>
                    <div id="mobile-payer-summary" class="summary-grid"></div>
                </div>
                <div class="swipe-tab-panel no-pading">
                    <div id="mobile-balance-summary" class="balance-grid"></div>
                </div>
            </div>
        </div>

        <!-- Back Link -->
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>

    <!-- Floating Filter Button -->
    <button class="floating-filter-btn" id="floating-filter-btn">
        <i class="fas fa-filter"></i>
    </button>

    <!-- Floating Add Button -->
    <button class="floating-add-btn" id="floating-add-btn">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Add Expense Modal -->
    <div id="add-expense-modal" class="add-expense-modal">
        <div class="add-expense-modal-content">
            <h3 class="form-title"><i class="fas fa-plus-circle"></i> Thêm Chi Tiêu Mới</h3>
            <div id="message-container"></div>
            <form id="expense-form">
                <div class="form-grid">
                    <div class="form-group">
                        <input type="text" id="content" class="form-input" placeholder="Nội dung chi tiêu" required>
                    </div>
                    <div class="form-group">
                        <input type="number" id="amount" class="form-input" placeholder="Số tiền (VND)" required step="1">
                        <div id="formatted-amount" class="formatted-amount"></div>
                    </div>
                    <div class="form-group">
                        <?php
                            // Read members from data/member.json
                            $membersFile = __DIR__ . '/data/member.json';
                            $members = [];
                            if (file_exists($membersFile)) {
                                $json = file_get_contents($membersFile);
                                // Try to decode as JSON, fallback to empty array if fails
                                $members = json_decode($json, true);
                                if (!is_array($members)) $members = [];
                            }
                        ?>
                        <select id="payer" class="form-select" required>
                            <option value="">Chọn người chi trả</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="checkbox" id="balance-tag" style="width:1.2em;height:1.2em;"> <span>Đánh dấu là chi tiêu cân bằng (không tính vào thống kê)</span>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-add">Hủy</button>
                    <button type="submit" class="btn-primary" id="add-expense-btn">
                        <i class="fas fa-plus"></i> Thêm Chi Tiêu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title"><i class="fas fa-edit"></i> Chỉnh Sửa Chi Tiêu</h3>
            <form id="edit-expense-form">
                <div class="form-grid">
                    <div class="form-group">
                        <input type="text" id="edit-content" class="form-input" placeholder="Nội dung chi tiêu" required>
                    </div>
                    <div class="form-group">
                        <input type="number" id="edit-amount" class="form-input" placeholder="Số tiền (VND)" required step="1">
                        <div id="edit-formatted-amount" class="formatted-amount"></div>
                    </div>
                    <div class="form-group">
                        <?php
                            // Read members from data/member.json
                            $membersFile = __DIR__ . '/data/member.json';
                            $members = [];
                            if (file_exists($membersFile)) {
                                $json = file_get_contents($membersFile);
                                $members = json_decode($json, true);
                                if (!is_array($members)) $members = [];
                            }
                        ?>
                        <select id="edit-payer" class="form-select" required>
                            <option value="">Chọn người chi trả</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:0.5rem;">
                        <input type="checkbox" id="edit-balance-tag" style="width:1.2em;height:1.2em;"> <span>Đánh dấu là chi tiêu cân bằng (không tính vào thống kê)</span>
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="cancel-edit">Hủy</button>
                    <button type="submit" class="btn-primary" id="save-edit">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global variables
        let expenses = [];
        let currentEditId = null;

        // API Configuration
        const API_BASE = 'api/expenses.php';

        // Utility functions
        function formatCurrency(amount) {
            return parseInt(amount).toLocaleString('vi-VN') + ' VND';
        }

        function showMessage(message, type = 'success') {
            // Show message in main container, not modal
            let container = document.querySelector('.container');
            
            // Remove existing messages
            const existingMessages = container.querySelectorAll('.success-message, .error-message');
            existingMessages.forEach(msg => msg.remove());
            
            const messageDiv = document.createElement('div');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '100px';
            messageDiv.style.left = '50%';
            messageDiv.style.transform = 'translateX(-50%)';
            messageDiv.style.zIndex = '1001';
            messageDiv.style.minWidth = '300px';
            messageDiv.style.boxShadow = '0 8px 32px rgba(0, 0, 0, 0.3)';
            
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }

        function setLoading(element, isLoading) {
            if (isLoading) {
                element.classList.add('loading');
                element.disabled = true;
            } else {
                element.classList.remove('loading');
                element.disabled = false;
            }
        }

        // API functions
        async function fetchExpenses() {
            try {
                // Add cache prevention by appending a timestamp
                const timestamp = new Date().getTime();
                const response = await fetch(`${API_BASE}?t=${timestamp}`);
                
                if (!response.ok) throw new Error('Failed to fetch expenses');
                expenses = await response.json();
                displayExpenses();
                displaySummary();
                displayBalance();
                return true;
            } catch (error) {
                console.error('Error fetching expenses:', error);
                showMessage('Lỗi khi tải danh sách chi tiêu', 'error');
                return false;
            }
        }

        async function addExpense(expenseData) {
            try {
                const response = await fetch(API_BASE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(expenseData)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Failed to add expense: ${response.status} ${errorText}`);
                }
                
                await fetchExpenses(); // Reload all expenses from the API
                showMessage('Thêm chi tiêu thành công!');
                
                // Reset form and close modal
                document.getElementById('expense-form').reset();
                document.getElementById('formatted-amount').textContent = '';
                closeAddModal();
                
                return true;
            } catch (error) {
                console.error('Error adding expense:', error);
                showMessage('Lỗi khi thêm chi tiêu', 'error');
                return false;
            }
        }

        async function updateExpense(id, expenseData) {
            try {
                const response = await fetch(API_BASE, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id, ...expenseData })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Failed to update expense: ${response.status} ${errorText}`);
                }
                
                await fetchExpenses(); // Reload all expenses from the API
                showMessage('Cập nhật chi tiêu thành công!');
                closeEditModal();
                
                return true;
            } catch (error) {
                console.error('Error updating expense:', error);
                showMessage('Lỗi khi cập nhật chi tiêu', 'error');
                return false;
            }
        }

        async function deleteExpense(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa chi tiêu này không?')) {
                return;
            }

            try {
                const response = await fetch(API_BASE, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id })
                });

                // Handle 200-299 status range as success even if there's no content
                if (response.status >= 200 && response.status < 300) {
                    await fetchExpenses(); // Reload all expenses from the API
                    showMessage('Xóa chi tiêu thành công!');
                    return true;
                } else {
                    const errorText = await response.text();
                    throw new Error(`Server responded with status: ${response.status} ${errorText}`);
                }
            } catch (error) {
                console.error('Error deleting expense:', error);
                
                // Check if data was actually deleted despite the error
                const originalCount = expenses.length;
                const wasDeleted = await fetchExpenses() && 
                                    expenses.findIndex(exp => exp.id === id) === -1 &&
                                    expenses.length < originalCount;
                
                if (wasDeleted) {
                    // The record was actually deleted despite the error
                    showMessage('Chi tiêu đã được xóa thành công!');
                    return true;
                } else {
                    showMessage('Lỗi khi xóa chi tiêu', 'error');
                    return false;
                }
            }
        }

        // Display functions
        function displayExpenses() {
            const mobileExpenseList = document.getElementById('mobile-expense-list');
            const selectedPayer = document.getElementById('filter-payer').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            
            let filteredExpenses = expenses;
            
            // Filter by payer
            if (selectedPayer) {
                filteredExpenses = filteredExpenses.filter(exp => exp.payer === selectedPayer);
            }
            
            // Filter by date range
            if (dateFrom) {
                filteredExpenses = filteredExpenses.filter(exp => {
                    // Adjust to GMT+7
                    const expenseDateObj = new Date(exp.date);
                    expenseDateObj.setHours(expenseDateObj.getHours() + 7);
                    const expenseDate = expenseDateObj.toISOString().split('T')[0];
                    return expenseDate >= dateFrom;
                });
            }
            
            if (dateTo) {
                filteredExpenses = filteredExpenses.filter(exp => {
                    // Adjust to GMT+7
                    const expenseDateObj = new Date(exp.date);
                    expenseDateObj.setHours(expenseDateObj.getHours() + 7);
                    const expenseDate = expenseDateObj.toISOString().split('T')[0];
                    return expenseDate <= dateTo;
                });
            }

            const expenseHTML = generateExpenseHTML(filteredExpenses, selectedPayer, dateFrom, dateTo);
            
            mobileExpenseList.innerHTML = expenseHTML;
        }

        function generateExpenseHTML(filteredExpenses, selectedPayer, dateFrom, dateTo) {
            if (filteredExpenses.length === 0) {
                return `
                    <div style="text-align: center; color: #a1a1aa; padding: 3rem;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Chưa có chi tiêu nào${selectedPayer ? ' cho người này' : ''}${dateFrom || dateTo ? ' trong khoảng thời gian này' : ''}</p>
                    </div>
                `;
            }

            // Sort expenses by date (newest first)
            filteredExpenses.sort((a, b) => new Date(b.date) - new Date(a.date));

            return filteredExpenses.map(expense => `
                <div class="expense-item">
                    <div class="expense-content">
                        <div class="expense-text">${expense.content}</div>
                        <div class="expense-amount">${formatCurrency(expense.amount)}</div>
                    </div>
                    <div class="expense-meta">
                        <div>
                            <span class="expense-payer">${expense.payer}</span>
                            <br>
                            <span style="margin-left: 0.5rem; font-size: 0.75rem;">
                                ${(() => {
                                    const d = new Date(expense.date);
                                    d.setHours(d.getHours() + 7);
                                    return d.toLocaleDateString('en-GB', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    }) + ' ' + d.toLocaleTimeString('vi-VN', {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                })()}
                            </span>
                        </div>
                        <div class="expense-actions">
                            <button class="btn-edit" onclick="openEditModal('${expense.id}')">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-delete" onclick="deleteExpense('${expense.id}')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displaySummary() {
            const summaryContainer = document.getElementById('payer-summary');
            const mobileSummaryContainer = document.getElementById('mobile-payer-summary');
            const selectedPayer = document.getElementById('filter-payer').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            
            let expensesToSummarize = expenses;
            
            // Apply same filters as expenses
            if (selectedPayer) {
                expensesToSummarize = expensesToSummarize.filter(exp => exp.payer === selectedPayer);
            }
            
            if (dateFrom) {
                expensesToSummarize = expensesToSummarize.filter(exp => {
                    const expenseDate = new Date(exp.date).toISOString().split('T')[0];
                    return expenseDate >= dateFrom;
                });
            }
            
            if (dateTo) {
                expensesToSummarize = expensesToSummarize.filter(exp => {
                    const expenseDate = new Date(exp.date).toISOString().split('T')[0];
                    return expenseDate <= dateTo;
                });
            }

            const summaryHTML = generateSummaryHTML(expensesToSummarize);
            
            summaryContainer.innerHTML = summaryHTML;
            mobileSummaryContainer.innerHTML = summaryHTML;
        }

        function generateSummaryHTML(expensesToSummarize) {
            // Exclude 'balance' expenses from summary
            const payerSums = {};
            let totalAmount = 0;
            let trueTotalAmount = 0;

            expensesToSummarize.forEach(expense => {
                const amount = parseFloat(expense.amount);
                totalAmount += amount;
                if (!expense.tag || expense.tag !== 'balance') {
                    trueTotalAmount += amount;
                    if (payerSums[expense.payer]) {
                        payerSums[expense.payer] += amount;
                    } else {
                        payerSums[expense.payer] = amount;
                    }
                }
            });

            if (Object.keys(payerSums).length === 0) {
                return `
                    <div style="text-align: center; color: #a1a1aa; padding: 3rem;">
                        <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Chưa có dữ liệu thống kê</p>
                    </div>
                `;
            }

            // Sort by amount (highest first)
            const sortedPayers = Object.entries(payerSums).sort(([,a], [,b]) => b - a);

            let summaryHTML = '';

            // Add total summary (true total, excluding 'balance')
            if (sortedPayers.length > 1) {
                summaryHTML += `
                    <div class="summary-item" style="background: rgba(99, 102, 241, 0.1); border-color: rgba(99, 102, 241, 0.3);">
                        <div class="summary-payer" style="color: #8b5cf6; font-weight: 600;">
                            <i class="fas fa-calculator"></i> Tổng Cộng (loại trừ cân bằng)
                        </div>
                        <div class="summary-amount" style="color: #8b5cf6;">${formatCurrency(trueTotalAmount)}</div>
                    </div>
                `;
            }

            // Add individual summaries
            summaryHTML += sortedPayers.map(([payer, amount]) => `
                <div class="summary-item">
                    <div class="summary-payer">
                        <i class="fas fa-user"></i> ${payer}
                        <span style="font-size: 0.8rem; opacity: 0.7; margin-left: 0.5rem;">
                            (${((amount / trueTotalAmount) * 100).toFixed(1)}%)
                        </span>
                    </div>
                    <div class="summary-amount">${formatCurrency(amount)}</div>
                </div>
            `).join('');

            return summaryHTML;
        }

        // Balance functions
        function displayBalance() {
            const balanceContainer = document.getElementById('balance-summary');
            const mobileBalanceContainer = document.getElementById('mobile-balance-summary');
            const selectedPayer = document.getElementById('filter-payer').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            
            let expensesToBalance = expenses;
            
            // Apply same filters as expenses
            if (selectedPayer) {
                expensesToBalance = expensesToBalance.filter(exp => exp.payer === selectedPayer);
            }
            
            if (dateFrom) {
                expensesToBalance = expensesToBalance.filter(exp => {
                    const expenseDate = new Date(exp.date).toISOString().split('T')[0];
                    return expenseDate >= dateFrom;
                });
            }
            
            if (dateTo) {
                expensesToBalance = expensesToBalance.filter(exp => {
                    const expenseDate = new Date(exp.date).toISOString().split('T')[0];
                    return expenseDate <= dateTo;
                });
            }

            const balanceHTML = generateBalanceHTML(expensesToBalance);
            
            balanceContainer.innerHTML = balanceHTML;
            mobileBalanceContainer.innerHTML = balanceHTML;
        }

        function generateBalanceHTML(expensesToBalance) {
            if (expensesToBalance.length === 0) {
                return `
                    <div style="text-align: center; color: #a1a1aa; padding: 3rem;">
                        <i class="fas fa-exchange-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Chưa có dữ liệu để cân bằng chi tiêu</p>
                    </div>
                `;
            }

            // Calculate the total amount spent by each person
            const payerAmounts = {};
            let totalAmount = 0;
            let trueTotalAmount = 0;

            expensesToBalance.forEach(expense => {
                const amount = parseFloat(expense.amount);
                totalAmount += amount;
                if (!expense.tag || expense.tag !== 'balance') {
                    trueTotalAmount += amount;
                    if (payerAmounts[expense.payer]) {
                        payerAmounts[expense.payer] += amount;
                    } else {
                        payerAmounts[expense.payer] = amount;
                    }
                }
            });

            // Count unique payers only
            const uniquePayers = Object.keys(payerAmounts);
            const peopleCount = uniquePayers.length;

            // Calculate the average amount per person (all expenses)
            const averageAmount = totalAmount / peopleCount;
            // Calculate the true average amount per person (excluding 'balance')
            const trueAverageAmount = trueTotalAmount / peopleCount;

            // Calculate how much each person should pay or receive (excluding 'balance')
            const balances = {};
            for (const payer of uniquePayers) {
                balances[payer] = payerAmounts[payer] - averageAmount;
            }

            // Create transactions to balance everyone out
            const transactions = [];
            const debtors = Object.keys(balances).filter(person => balances[person] < 0);
            const creditors = Object.keys(balances).filter(person => balances[person] > 0);

            // For simplicity, we'll pair debtors with creditors
            while (debtors.length > 0 && creditors.length > 0) {
                const debtor = debtors[0];
                const creditor = creditors[0];

                const debtAmount = Math.abs(balances[debtor]);
                const creditAmount = balances[creditor];

                const transferAmount = Math.min(debtAmount, creditAmount);

                transactions.push({
                    from: debtor,
                    to: creditor,
                    amount: Math.round(transferAmount) // Round to nearest integer
                });

                balances[debtor] += transferAmount;
                balances[creditor] -= transferAmount;

                if (Math.abs(balances[debtor]) < 1) { // Small threshold to handle floating point errors
                    debtors.shift();
                }

                if (Math.abs(balances[creditor]) < 1) {
                    creditors.shift();
                }
            }

            // Create HTML for balance display
            let balanceHTML = `
                <div class="balance-item">
                    <div class="balance-header">
                        <span><i class="fas fa-calculator"></i> Thông Tin Cân Bằng</span>
                    </div>
                    <div class="balance-average">
                        <span>Chi tiêu trung bình (tất cả): ${formatCurrency(Math.round(averageAmount))}</span><br>
                        <span>Chi tiêu trung bình (loại trừ cân bằng): ${formatCurrency(Math.round(trueAverageAmount))}</span>
                    </div>
                    <div style="margin-bottom: 1rem;">
            `;

            balanceHTML += `
                    </div>
                    <div class="balance-header">
                        <span><i class="fas fa-exchange-alt"></i> Các Giao Dịch Cần Thực Hiện</span>
                    </div>
            `;

            // Show needed transactions
            if (transactions.length === 0) {
                balanceHTML += `
                    <div style="text-align: center; color: #a1a1aa; padding: 1rem;">
                        Chi tiêu đã được cân bằng
                    </div>
                `;
            } else {
                transactions.forEach(transaction => {
                    if (transaction.amount > 0) {
                        balanceHTML += `
                            <div class="balance-transaction">
                                <span class="transaction-payer">${transaction.from}</span>
                                <span>→</span>
                                <span class="transaction-receiver">${transaction.to}</span>
                                <span class="transaction-amount">${formatCurrency(transaction.amount)}</span>
                            </div>
                        `;
                    }
                });
            }

            balanceHTML += `</div>`;
            return balanceHTML;
        }

        // Modal functions
        function openAddModal() {
            document.getElementById('add-expense-modal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('add-expense-modal').style.display = 'none';
            document.getElementById('expense-form').reset();
            document.getElementById('formatted-amount').textContent = '';
            document.getElementById('message-container').innerHTML = '';
        }

        function openEditModal(expenseId) {
            const expense = expenses.find(exp => exp.id === expenseId);
            if (!expense) return;

            currentEditId = expenseId;
            document.getElementById('edit-content').value = expense.content;
            document.getElementById('edit-amount').value = expense.amount;
            document.getElementById('edit-payer').value = expense.payer;
            document.getElementById('edit-balance-tag').checked = expense.tag === 'balance';
            updateFormattedAmount('edit-amount', 'edit-formatted-amount');
            document.getElementById('edit-modal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
            document.getElementById('edit-expense-form').reset();
            document.getElementById('edit-formatted-amount').textContent = '';
            currentEditId = null;
        }

        // Tab functions
        function switchTab(tabName) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

            // Add active class to selected tab
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        // Clear filters
        function clearFilters() {
            document.getElementById('filter-payer').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            displayExpenses();
            displaySummary();
            displayBalance();
        }

        function updateFormattedAmount(inputId, displayId) {
            const input = document.getElementById(inputId);
            const display = document.getElementById(displayId);
            const value = input.value;
            
            if (value) {
                display.textContent = formatCurrency(value);
            } else {
                display.textContent = '';
            }
        }

        // Refresh data periodically
        function setupAutoRefresh(intervalMinutes = 5) {
            // Convert minutes to milliseconds
            const interval = intervalMinutes * 60 * 1000;
            
            // Set up interval to refresh data
            const refreshTimer = setInterval(async () => {
                console.log(`Auto-refreshing data (${new Date().toLocaleTimeString()})`);
                await fetchExpenses();
            }, interval);
            
            // Clear interval when page is hidden/closed
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    clearInterval(refreshTimer);
                } else {
                    // Immediately refresh when page becomes visible again
                    fetchExpenses();
                    
                    // Restart the timer
                    clearInterval(refreshTimer);
                    setupAutoRefresh(intervalMinutes);
                }
            });
            
            return refreshTimer;
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Load expenses on page load
            fetchExpenses();
            
            // Set up auto refresh every 5 minutes
            setupAutoRefresh(5);

            // Set default date filter to last 30 days
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            document.getElementById('filter-date-to').value = today.toISOString().split('T')[0];
            document.getElementById('filter-date-from').value = thirtyDaysAgo.toISOString().split('T')[0];

            // Floating add button
            document.getElementById('floating-add-btn').addEventListener('click', openAddModal);

            // Floating filter button
            document.getElementById('floating-filter-btn').addEventListener('click', function() {
                const filterContainer = document.querySelector('.filter-container');
                if (filterContainer.style.display === 'none') {
                    filterContainer.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-times"></i>';
                    this.style.background = 'linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #f87171 100%)';
                    this.style.boxShadow = '0 8px 25px rgba(239, 68, 68, 0.4)';
                } else {
                    filterContainer.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-filter"></i>';
                    this.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%)';
                    this.style.boxShadow = '0 8px 25px rgba(16, 185, 129, 0.4)';
                }
            });

            // Add expense form
            document.getElementById('expense-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const addBtn = document.getElementById('add-expense-btn');
                setLoading(addBtn, true);
                const expenseData = {
                    content: document.getElementById('content').value.trim(),
                    amount: parseFloat(document.getElementById('amount').value),
                    payer: document.getElementById('payer').value
                };
                if (document.getElementById('balance-tag').checked) {
                    expenseData.tag = 'balance';
                }
                await addExpense(expenseData);
                setLoading(addBtn, false);
            });

            // Edit expense form
            document.getElementById('edit-expense-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!currentEditId) return;
                const saveBtn = document.getElementById('save-edit');
                setLoading(saveBtn, true);
                const expenseData = {
                    content: document.getElementById('edit-content').value.trim(),
                    amount: parseFloat(document.getElementById('edit-amount').value),
                    payer: document.getElementById('edit-payer').value
                };
                if (document.getElementById('edit-balance-tag').checked) {
                    expenseData.tag = 'balance';
                }
                await updateExpense(currentEditId, expenseData);
                setLoading(saveBtn, false);
            });

            // Filter changes
            document.getElementById('filter-payer').addEventListener('change', function() {
                displayExpenses();
                displaySummary();
                displayBalance();
            });

            document.getElementById('filter-date-from').addEventListener('change', function() {
                displayExpenses();
                displaySummary();
                displayBalance();
            });

            document.getElementById('filter-date-to').addEventListener('change', function() {
                displayExpenses();
                displaySummary();
                displayBalance();
            });

            // Clear filters
            document.getElementById('clear-filters').addEventListener('click', clearFilters);

            // Make date inputs fully clickable
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.addEventListener('click', function() {
                    this.showPicker && this.showPicker();
                });
            });

            // Tab switching
            document.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });

            // Amount formatting
            document.getElementById('amount').addEventListener('input', function() {
                updateFormattedAmount('amount', 'formatted-amount');
            });

            document.getElementById('edit-amount').addEventListener('input', function() {
                updateFormattedAmount('edit-amount', 'edit-formatted-amount');
            });

            // Modal close events
            document.getElementById('cancel-add').addEventListener('click', closeAddModal);
            document.getElementById('cancel-edit').addEventListener('click', closeEditModal);
            
            document.getElementById('add-expense-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddModal();
                }
            });

            document.getElementById('edit-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEditModal();
                }
            });

            // Escape key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAddModal();
                    closeEditModal();
                }
            });

            // Mobile swipe detection for tabs with debouncing
            let startX = 0;
            let currentX = 0;
            let isSwiping = false;
            let isAnimating = false; // Flag to prevent overlapping animations
            let swipeStartTime = 0;
            let lastSwipeTime = 0;
            const SWIPE_COOLDOWN = 500; // Time in ms to wait before allowing another swipe
            const swipeContainer = document.querySelector('.swipe-tabs-container');
            
            if (window.innerWidth <= 768) {
                swipeContainer.addEventListener('touchstart', function(e) {
                    // Only start a new swipe if we're not currently animating
                    if (!isAnimating) {
                        startX = e.touches[0].clientX;
                        currentX = startX;
                        isSwiping = true;
                        swipeStartTime = Date.now();
                    }
                });

                swipeContainer.addEventListener('touchmove', function(e) {
                    // Only track movement if we're actively swiping
                    if (isSwiping) {
                        currentX = e.touches[0].clientX;
                        
                        // Prevent default to avoid page scrolling while swiping tabs
                        if (Math.abs(startX - currentX) > 10) {
                            //e.preventDefault();
                        }
                    }
                }, { passive: false });

                swipeContainer.addEventListener('touchend', function(e) {
                    if (!isSwiping) return;
                    
                    const now = Date.now();
                    // Check if we're still in the cooldown period
                    if (now - lastSwipeTime < SWIPE_COOLDOWN) {
                        // Reset swiping state without taking action
                        isSwiping = false;
                        return;
                    }
                    
                    const diffX = startX - currentX;
                    const threshold = 80; // Increased threshold for less sensitivity
                    const tabWidth = this.scrollWidth / 3; // We have 3 tabs
                    
                    // Calculate current tab more precisely
                    const currentPosition = this.scrollLeft;
                    const currentTab = Math.round(currentPosition / tabWidth);
                    
                    // Only process swipe if it meets threshold and timing requirements
                    if (Math.abs(diffX) > threshold) {
                        isAnimating = true;
                        lastSwipeTime = now;
                        
                        // Limit movement to only one tab at a time
                        if (diffX > 0 && currentTab < 2) {
                            // Swipe left - go to next tab
                            this.scrollTo({ 
                                left: (currentTab + 1) * tabWidth, 
                                behavior: 'smooth' 
                            });
                        } else if (diffX < 0 && currentTab > 0) {
                            // Swipe right - go to previous tab
                            this.scrollTo({ 
                                left: (currentTab - 1) * tabWidth, 
                                behavior: 'smooth' 
                            });
                        } else {
                            // Stay on current tab
                            this.scrollTo({ 
                                left: currentTab * tabWidth, 
                                behavior: 'smooth' 
                            });
                        }
                        
                        // Reset animation flag after transition completes
                        setTimeout(() => {
                            isAnimating = false;
                        }, 300);
                    } else {
                        // If the swipe was too small, snap back to current tab
                        this.scrollTo({ 
                            left: currentTab * tabWidth, 
                            behavior: 'smooth' 
                        });
                    }
                    
                    // Reset swiping state
                    isSwiping = false;
                }, { passive: false });

                // Ensure tabs snap correctly when scrolling ends
                swipeContainer.addEventListener('scroll', function() {
                    if (!isAnimating && !isSwiping) {
                        clearTimeout(swipeContainer.scrollTimeout);
                        swipeContainer.scrollTimeout = setTimeout(() => {
                            const tabWidth = this.scrollWidth / 3;
                            const currentPosition = this.scrollLeft;
                            const targetTab = Math.round(currentPosition / tabWidth);
                            
                            // Only snap if we're not too close to the target already
                            if (Math.abs(currentPosition - (targetTab * tabWidth)) > 10) {
                                this.scrollTo({
                                    left: targetTab * tabWidth,
                                    behavior: 'smooth'
                                });
                            }
                        }, 150);
                    }
                });
            }

            // Global functions for onclick handlers
            window.openEditModal = openEditModal;
            window.deleteExpense = deleteExpense;
        })
    </script>
</body>
</html>
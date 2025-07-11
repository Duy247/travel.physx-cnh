<?php
// public/PackingList.php

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Group Inventory Tracker</title>
    <link rel="stylesheet" href="css/style.css?v=<?= $cache_bust ?>">
    <link rel="stylesheet" href="css/packing.css?v=<?= $cache_bust ?>">
    <link rel="icon" type="image/x-icon" href="favicon.ico?v=<?php echo $cache_bust; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/cache-buster.js?v=<?= $cache_bust ?>"></script>
</head>
<body class="no-select mobile-optimized">
    <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i></a>
    
    <div class="packing-container">
        <div class="packing-header fade-in">
            <h1>Theo Dõi Đồ Nhóm</h1>
            <h2>Quản lý những gì cả nhóm cần mang</h2>
        </div>
        
        <!-- Add Item Button -->
        <button id="add-item-btn" class="btn btn-primary add-item-button">
            <i class="fas fa-plus"></i> Thêm Đồ
        </button>
        
        <!-- Login Button -->
        <button id="login-button" class="login-button">
            <i class="fas fa-user"></i>
        </button>
        
        <!-- User Info Display -->
        <div id="user-info" class="user-info">
            <span id="current-user-name"></span>
        </div>
        
        <!-- Member Selection Modal -->
        <?php
        // Read members from data/member.json
        $members = [];
        $memberFile = __DIR__ . '/data/member.json';
        if (file_exists($memberFile)) {
            $json = file_get_contents($memberFile);
            $members = json_decode($json, true);
        }
        ?>
        <div id="member-select-modal" class="member-select-modal">
            <h3>Chọn Thành Viên</h3>
            <select id="member" class="member-select">
                <option value="">Báo Danh</option>
                <?php if (!empty($members) && is_array($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button id="select-member-btn" class="btn btn-primary" style="width: 100%;">Chọn</button>
        </div>
        
        <!-- Add Item Modal -->
        <div id="add-item-modal" class="member-select-modal">
            <h3>Thêm Đồ Mới</h3>
            <div class="form-group">
                <input type="text" id="new-item-name" class="form-control" placeholder="Tên Đồ">
            </div>
            <button id="save-item-btn" class="btn btn-primary" style="width: 100%;">Lưu</button>
        </div>
        
        <div class="search-container fade-in">
            <input type="text" id="search-input" class="search-input" placeholder="Tìm đồ...">
        </div>
        
        <div class="items-container fade-in">
            <div id="inventory-list"></div>
        </div>
        
    </div>

    <script>
        // Cache DOM elements
        const memberSelect = document.getElementById('member');
        const searchInput = document.getElementById('search-input');
        const inventoryList = document.getElementById('inventory-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const addItemModal = document.getElementById('add-item-modal');
        const newItemNameInput = document.getElementById('new-item-name');
        const saveItemBtn = document.getElementById('save-item-btn');
        const loginButton = document.getElementById('login-button');
        const memberSelectModal = document.getElementById('member-select-modal');
        const selectMemberBtn = document.getElementById('select-member-btn');
        const userInfo = document.getElementById('user-info');
        const currentUserName = document.getElementById('current-user-name');
        
        // Current selected member
        let selectedMember = '';
        
        // API endpoints
        const API_URL = 'api/packing.php';

        // Functions
        async function loadInventory() {
            try {
                const response = await fetch(`${API_URL}?action=get_group_items`);
                const data = await response.json();
                
                if (data && data.items) {
                    displayInventory(sortInventoryAlphabetically(data.items));
                } else {
                    inventoryList.innerHTML = '<p style="color: #a1a1aa; text-align: center;">Không có đồ nào.</p>';
                }
            } catch (error) {
                console.error('Error loading inventory:', error);
                inventoryList.innerHTML = '<p style="color: #ef4444; text-align: center;">Lỗi khi tải dữ liệu.</p>';
            }
        }
        
        function sortInventoryAlphabetically(items) {
            return [...items].sort((a, b) => {
                const nameA = a.name.toLowerCase();
                const nameB = b.name.toLowerCase();
                return nameA.localeCompare(nameB);
            });
        }
        
        function displayInventory(items) {
            inventoryList.innerHTML = '';
            const searchTerm = searchInput.value.toLowerCase();
            
            const filteredItems = items.filter(item => {
                const nameMatch = !searchTerm || item.name.toLowerCase().includes(searchTerm);
                // When a member is selected, show all items regardless of who's carrying them
                // This allows multiple members to carry the same item
                const memberMatch = true; // Always show items regardless of carriers
                
                return nameMatch && memberMatch;
            });
            
            if (filteredItems.length === 0) {
                inventoryList.innerHTML = '<p style="color: #a1a1aa; text-align: center;">Không tìm thấy đồ nào.</p>';
                return;
            }
            
            filteredItems.forEach((item, index) => {
                const delay = index * 50; // Staggered animation
                const itemCard = document.createElement('div');
                itemCard.classList.add('item-card');
                itemCard.style.animationDelay = `${delay}ms`;
                
                const carriers = item.carriers ? item.carriers.join(', ') : '';
                const isCarrier = selectedMember && item.carriers && item.carriers.includes(selectedMember);
                
                itemCard.innerHTML = `
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-carriers">${carriers ? 'Mang bởi: ' + carriers : 'Chưa có ai mang'}</div>
                    </div>
                    <div class="item-actions">
                        <button class="btn btn-primary btn-small assign-btn" data-id="${item.id}" ${isCarrier ? 'disabled' : ''}>
                            ${isCarrier ? 'Đã Nhận' : 'Nhận mang'}
                        </button>
                        <button class="btn btn-secondary btn-small remove-btn" data-id="${item.id}" ${!isCarrier ? 'disabled' : ''}>Bỏ</button>
                        <button class="btn btn-danger btn-small delete-btn" data-id="${item.id}">Xóa</button>
                    </div>
                `;
                
                itemCard.classList.add('fade-in');
                inventoryList.appendChild(itemCard);
                
                // Add event handlers
                const assignBtn = itemCard.querySelector('.assign-btn');
                const removeBtn = itemCard.querySelector('.remove-btn');
                const deleteBtn = itemCard.querySelector('.delete-btn');
                
                assignBtn.addEventListener('click', () => assignCarrier(item.id));
                removeBtn.addEventListener('click', () => removeCarrier(item.id));
                deleteBtn.addEventListener('click', () => deleteItem(item.id));
            });
        }
        
        async function assignCarrier(itemId) {
            if (!selectedMember) {
                alert('Vui lòng chọn thành viên trước');
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=update_group_item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: itemId,
                        member: selectedMember,
                        action: 'add_carrier'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadInventory();
                } else {
                    console.error('Error assigning carrier:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function removeCarrier(itemId) {
            if (!selectedMember) {
                alert('Vui lòng chọn thành viên trước');
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=update_group_item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: itemId,
                        member: selectedMember,
                        action: 'remove_carrier'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadInventory();
                } else {
                    console.error('Error removing carrier:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function deleteItem(itemId) {
            if (selectedMember !== 'Duy') {
                alert('Chỉ Duy mới được xóa đồ');
                return;
            }
            
            if (confirm('Bạn có chắc muốn xóa đồ này?')) {
                try {
                    const response = await fetch(`${API_URL}?action=update_group_item`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: itemId,
                            member: selectedMember,
                            action: 'delete'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        loadInventory();
                    } else {
                        console.error('Error deleting item:', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
        
        async function addNewItem() {
            const itemName = newItemNameInput.value.trim();
            
            if (!itemName) {
                alert('Vui lòng nhập tên đồ');
                return;
            }
            
            try {
                // Create request body - if selectedMember exists, include it as carrier
                const requestBody = {
                    name: itemName
                };
                
                // Only add carrier if a member is selected
                if (selectedMember) {
                    requestBody.carrier = selectedMember;
                }
                
                const response = await fetch(`${API_URL}?action=add_group_item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestBody)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    newItemNameInput.value = '';
                    addItemModal.style.display = 'none';
                    loadInventory();
                } else {
                    console.error('Error adding item:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        // Event listeners
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadInventory, 1000);
        });
        
        addItemBtn.addEventListener('click', function() {
            // Allow adding items without selecting a member
            addItemModal.style.display = 'block';
            newItemNameInput.focus();
        });
        
        saveItemBtn.addEventListener('click', function() {
            addNewItem();
            addItemModal.style.display = 'none';
        });
        
        // Enhanced login button handling to ensure clicks on the icon also trigger the action
        function handleLoginButtonClick(event) {
            if (selectedMember) {
                // If already logged in, log out
                selectedMember = '';
                loginButton.classList.remove('active');
                loginButton.innerHTML = '<i class="fas fa-user"></i>';
                userInfo.style.display = 'none';
            } else {
                // Show the member selection modal
                memberSelectModal.style.display = 'block';
            }
        }
        
        // Add click event listener to the button
        loginButton.addEventListener('click', handleLoginButtonClick);
        
        selectMemberBtn.addEventListener('click', function() {
            const newSelectedMember = memberSelect.value;
            
            if (!newSelectedMember) {
                alert('Vui lòng chọn một thành viên');
                return;
            }
            
            selectedMember = newSelectedMember;
            memberSelectModal.style.display = 'none';
            
            // Update the login button and user info
            loginButton.classList.add('active');
            loginButton.innerHTML = '<i class="fas fa-check"></i>';
            currentUserName.textContent = selectedMember;
            userInfo.style.display = 'block';
            
            loadInventory();
        });
        
        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (!memberSelectModal.contains(event.target) && 
                event.target !== loginButton && 
                memberSelectModal.style.display === 'block') {
                memberSelectModal.style.display = 'none';
            }
            
            if (!addItemModal.contains(event.target) && 
                event.target !== addItemBtn && 
                addItemModal.style.display === 'block') {
                addItemModal.style.display = 'none';
            }
        });
        
        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            memberSelectModal.style.display = 'none';
            addItemModal.style.display = 'none';
            userInfo.style.display = 'none';
            loadInventory();
            
            // Ensure the login button is always clickable by applying correct styles
            loginButton.style.touchAction = 'manipulation'; // Improves touch response
            
            // Prevent event propagation from the modal contents to document
            document.querySelectorAll('.member-select-modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Enable Enter key for the item input
            newItemNameInput.addEventListener('keyup', function(event) {
                if (event.key === "Enter") {
                    addNewItem();
                    addItemModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

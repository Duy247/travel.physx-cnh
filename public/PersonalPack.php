<?php
// public/PersonalPack.php

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
    <title>Personal Pack Tracker</title>
    <link rel="stylesheet" href="css/style.css?v=<?= $cache_bust ?>">
    <link rel="stylesheet" href="css/packing.css?v=<?= $cache_bust ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/cache-buster.js?v=<?= $cache_bust ?>"></script>
</head>
<body class="no-select mobile-optimized">
    <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i></a>
    
    <div class="packing-container">
        <div class="packing-header fade-in">
            <h1>Theo Dõi Đồ Cá Nhân</h1>
            <h2>Quản lý đồ cá nhân của bạn</h2>
        </div>
        
        <!-- Add Item Button -->
        <button id="add-item-btn" class="btn btn-primary add-item-button">
            <i class="fas fa-plus"></i> Thêm Đồ Cá Nhân
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
        
        <!-- Password Authentication Modal -->
        <div id="password-container" class="password-container modal-container fade-in" style="display: none;">
            <div class="password-modal">
                <h3>Nhập Mật Khẩu</h3>
                <div class="form-group">
                    <input type="password" id="member-password" class="form-control" placeholder="Mật khẩu">
                    <button id="password-submit-btn" class="btn btn-primary">Xác Nhận</button>
                </div>
                <p id="password-error" style="color: #ef4444; display: none;">Mật khẩu không đúng. Vui lòng thử lại.</p>
            </div>
        </div>
        
        <!-- Add Item Modal -->
        <div id="add-item-modal" class="member-select-modal">
            <h3>Thêm Đồ Cá Nhân</h3>
            <div class="form-group">
                <input type="text" id="new-item-name" class="form-control" placeholder="Tên Đồ">
            </div>
            <button id="save-item-btn" class="btn btn-primary" style="width: 100%;">Lưu</button>
        </div>
        
        <div id="content-container" style="display: none;">
            <div class="search-container fade-in">
                <input type="text" id="search-input" class="search-input" placeholder="Tìm đồ...">
            </div>
            
            <div class="items-container fade-in">
                <div id="personal-items-list"></div>
            </div>
        </div>
        
    </div>

    <script>
        // Cache DOM elements
        const memberSelect = document.getElementById('member');
        const searchInput = document.getElementById('search-input');
        const personalItemsList = document.getElementById('personal-items-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const addItemModal = document.getElementById('add-item-modal');
        const newItemNameInput = document.getElementById('new-item-name');
        const saveItemBtn = document.getElementById('save-item-btn');
        const passwordContainer = document.getElementById('password-container');
        const contentContainer = document.getElementById('content-container');
        const memberPassword = document.getElementById('member-password');
        const passwordSubmitBtn = document.getElementById('password-submit-btn');
        const passwordError = document.getElementById('password-error');
        const loginButton = document.getElementById('login-button');
        const memberSelectModal = document.getElementById('member-select-modal');
        const selectMemberBtn = document.getElementById('select-member-btn');
        const userInfo = document.getElementById('user-info');
        const currentUserName = document.getElementById('current-user-name');
        
        // Member passwords - loaded from JSON file
        let memberPasswords = {};
        
        // Current authenticated member
        let authenticatedMember = '';
        
        // API endpoints
        const API_URL = 'api/packing.php';
        
        // Load member passwords from data/password.json
        async function loadMemberPasswords() {
            try {
                const response = await fetch('data/password.json');
                memberPasswords = await response.json();
            } catch (error) {
                console.error('Error loading passwords:', error);
            }
        }
        
        // Password validation
        function validatePassword(member, password) {
            return memberPasswords[member] === password;
        }
        
        // Functions
        async function loadPersonalItems() {
            const selectedMember = authenticatedMember;
            
            if (!selectedMember) {
                personalItemsList.innerHTML = '<p style="color: #a1a1aa; text-align: center;">Vui lòng chọn một thành viên</p>';
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=get_personal_items&member=${selectedMember}`);
                const data = await response.json();
                
                if (data && data.personal_items && data.personal_items[selectedMember]) {
                    displayPersonalItems(data.personal_items[selectedMember]);
                } else {
                    personalItemsList.innerHTML = '<p style="color: #a1a1aa; text-align: center;">Không có đồ nào.</p>';
                }
            } catch (error) {
                console.error('Error loading personal items:', error);
                personalItemsList.innerHTML = '<p style="color: #ef4444; text-align: center;">Lỗi khi tải dữ liệu.</p>';
            }
        }
        
        function displayPersonalItems(items) {
            personalItemsList.innerHTML = '';
            const searchTerm = searchInput.value.toLowerCase();
            
            const filteredItems = items.filter(item => 
                !searchTerm || item.name.toLowerCase().includes(searchTerm)
            );
            
            if (filteredItems.length === 0) {
                personalItemsList.innerHTML = '<p style="color: #a1a1aa; text-align: center;">Không tìm thấy đồ nào.</p>';
                return;
            }
            
            filteredItems.forEach((item, index) => {
                const delay = index * 50; // Staggered animation
                const itemCard = document.createElement('div');
                itemCard.classList.add('item-card');
                itemCard.style.animationDelay = `${delay}ms`;
                
                itemCard.innerHTML = `
                    <div class="item-details">
                        <label class="checkbox-container">
                            <input type="checkbox" class="custom-checkbox" data-id="${item.id}" ${item.packed ? 'checked' : ''}>
                            <span class="item-name" style="${item.packed ? 'text-decoration: line-through; opacity: 0.7;' : ''}">${item.name}</span>
                        </label>
                    </div>
                    <div class="item-actions">
                        <button class="btn btn-danger btn-small delete-btn" data-id="${item.id}">Xóa</button>
                    </div>
                `;
                
                itemCard.classList.add('fade-in');
                personalItemsList.appendChild(itemCard);
                
                // Add event handlers
                const checkbox = itemCard.querySelector('.custom-checkbox');
                const deleteBtn = itemCard.querySelector('.delete-btn');
                
                checkbox.addEventListener('change', () => toggleItemPacked(item.id));
                deleteBtn.addEventListener('click', () => deletePersonalItem(item.id));
            });
        }
        
        async function toggleItemPacked(itemId) {
            if (!authenticatedMember) {
                alert('Bạn cần đăng nhập trước');
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=update_personal_item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: itemId,
                        member: authenticatedMember,
                        action: 'toggle_packed'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadPersonalItems();
                } else {
                    console.error('Error toggling item packed status:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function deletePersonalItem(itemId) {
            if (!authenticatedMember) {
                alert('Bạn cần đăng nhập trước');
                return;
            }
            
            if (confirm('Bạn có chắc muốn xóa đồ này?')) {
                try {
                    const response = await fetch(`${API_URL}?action=update_personal_item`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: itemId,
                            member: authenticatedMember,
                            action: 'delete'
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        loadPersonalItems();
                    } else {
                        console.error('Error deleting item:', data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
        
        async function addPersonalItem() {
            const itemName = newItemNameInput.value.trim();
            
            if (!itemName) {
                alert('Vui lòng nhập tên đồ');
                return;
            }
            
            if (!authenticatedMember) {
                alert('Bạn cần đăng nhập trước');
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?action=add_personal_item`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: itemName,
                        member: authenticatedMember
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    newItemNameInput.value = '';
                    addItemModal.style.display = 'none';
                    loadPersonalItems();
                } else {
                    console.error('Error adding item:', data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
        
        async function authenticateMember() {
            const selectedMember = memberSelect.value;
            const password = memberPassword.value.trim();
            
            if (!selectedMember) {
                alert('Vui lòng chọn thành viên');
                return;
            }
            
            if (!password) {
                alert('Vui lòng nhập mật khẩu');
                return;
            }
            
            // Client-side password validation (for demonstration only)
            if (!validatePassword(selectedMember, password)) {
                passwordError.style.display = 'block';
                return;
            }
            
            passwordError.style.display = 'none';
            authenticatedMember = selectedMember;
            passwordContainer.style.display = 'none';
            contentContainer.style.display = 'block';
            
            // Update the login button and user info
            loginButton.classList.add('active');
            loginButton.innerHTML = '<i class="fas fa-check"></i>';
            currentUserName.textContent = authenticatedMember;
            userInfo.style.display = 'block';
            
            loadPersonalItems();
        }
        
        // Event listeners
        selectMemberBtn.addEventListener('click', function() {
            const selectedMember = memberSelect.value;
            
            if (!selectedMember) {
                alert('Vui lòng chọn một thành viên');
                return;
            }
            
            memberSelectModal.style.display = 'none';
            passwordContainer.style.display = 'flex'; // Changed to flex to center the modal
            memberPassword.focus(); // Focus on the password input
        });
        
        // Enhanced login button handling to ensure clicks on the icon also trigger the action
        function handleLoginButtonClick(event) {
            if (authenticatedMember) {
                // If already logged in, log out
                authenticatedMember = '';
                loginButton.classList.remove('active');
                loginButton.innerHTML = '<i class="fas fa-user"></i>';
                contentContainer.style.display = 'none';
                userInfo.style.display = 'none';
                personalItemsList.innerHTML = '';
            } else {
                // Show the member selection modal
                memberSelectModal.style.display = 'block';
            }
        }
        
        // Add click event listener to the button
        loginButton.addEventListener('click', handleLoginButtonClick);
        
        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            // Close member select modal
            if (!memberSelectModal.contains(event.target) && 
                event.target !== loginButton && 
                memberSelectModal.style.display === 'block') {
                memberSelectModal.style.display = 'none';
            }
            
            // Close password modal when clicking outside the password-modal div
            if (passwordContainer.style.display === 'flex' && 
                !event.target.closest('.password-modal') &&
                !memberSelectModal.contains(event.target)) {
                // Don't close when interacting with the member select modal
                passwordContainer.style.display = 'none';
            }
            
            // Close add item modal when clicking outside
            if (addItemModal.style.display === 'block' && 
                !addItemModal.contains(event.target) && 
                event.target !== addItemBtn) {
                addItemModal.style.display = 'none';
            }
        });
        
        searchInput.addEventListener('input', loadPersonalItems);
        
        addItemBtn.addEventListener('click', function() {
            if (!authenticatedMember) {
                alert('Bạn cần đăng nhập trước khi thêm đồ');
                return;
            }
            addItemModal.style.display = 'block';
            newItemNameInput.focus();
        });
        
        saveItemBtn.addEventListener('click', addPersonalItem);
        passwordSubmitBtn.addEventListener('click', authenticateMember);
        
        // Enter key press handler for password input
        memberPassword.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                authenticateMember();
            }
        });
        
        // Initial setup
        document.addEventListener('DOMContentLoaded', async function() {
            // Load passwords from JSON file first
            await loadMemberPasswords();
            
            contentContainer.style.display = 'none';
            passwordContainer.style.display = 'none';
            memberSelectModal.style.display = 'none';
            addItemModal.style.display = 'none';
            userInfo.style.display = 'none';
            
            // Ensure the login button is always clickable by applying correct styles
            loginButton.style.touchAction = 'manipulation'; // Improves touch response
            
            // Prevent event propagation from the modal contents to document
            document.querySelectorAll('.password-modal, .member-select-modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            });
            
            // Additional check for icon click (though this should be unnecessary with pointer-events: none in CSS)
            const iconInLoginButton = loginButton.querySelector('i');
            if (iconInLoginButton) {
                iconInLoginButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    handleLoginButtonClick(e);
                });
            }
            
            // Enable Enter key for the new item input
            newItemNameInput.addEventListener('keyup', function(event) {
                if (event.key === "Enter") {
                    addPersonalItem();
                }
            });
        });
    </script>
</body>
</html>
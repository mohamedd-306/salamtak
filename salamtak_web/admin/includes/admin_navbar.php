<?php
// Admin Navbar - Reusable component for all admin pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* Admin Page Navbar */
    .admin-page {
        margin: 0;
        padding: 0;
    }
    
    .simple-admin-nav {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
        background: rgba(15, 29, 63, 0.95);
        backdrop-filter: blur(20px);
        padding: 16px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .simple-nav-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* Brand Section */
    .nav-brand-link {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        text-decoration: none;
        font-size: 24px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 1000;
        position: relative;
    }
    
    .nav-brand-link:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    .brand-logo {
        width: 70px;
        height: 70px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    
    .brand-logo img {
        pointer-events: none;
    }
    
    /* Navigation Links */
    .landing-nav-links {
        display: flex;
        gap: 32px;
        align-items: center;
    }
    
    .landing-nav-link {
        color: white;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        transition: var(--transition);
    }
    
    .landing-nav-link:hover {
        color: #FBBF24;
    }
    
    .landing-nav-link.active {
        color: #FBBF24;
        position: relative;
    }
    
    .landing-nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        right: 0;
        height: 2px;
        background: #FBBF24;
    }
    
    /* Profile Dropdown Styles */
    .profile-dropdown {
        position: relative;
    }
    
    .profile-button {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 12px 6px 6px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        color: white;
    }
    
    .profile-button:hover {
        background: rgba(255, 255, 255, 0.25);
    }
    
    .profile-avatar {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .profile-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .profile-name {
        font-size: 13px;
        font-weight: 600;
        line-height: 1;
        margin-bottom: 2px;
        color: white;
    }
    
    .dropdown-arrow {
        margin-left: 4px;
        transition: transform 0.3s;
    }
    
    .profile-dropdown.active .dropdown-arrow {
        transform: rotate(180deg);
    }
    
    .dropdown-menu {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        min-width: 220px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s;
        z-index: 1000;
    }
    
    .profile-dropdown.active .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #2d3748;
        text-decoration: none;
        transition: all 0.3s;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .dropdown-item:first-child {
        border-radius: 12px 12px 0 0;
    }
    
    .dropdown-item:last-child {
        border-bottom: none;
        border-radius: 0 0 12px 12px;
    }
    
    .dropdown-item:hover {
        background: #f7fafc;
    }
    
    .dropdown-item svg {
        width: 18px;
        height: 18px;
        color: #4a5568;
    }
    
    .dropdown-item.logout {
        color: #ef4444;
    }
    
    .dropdown-item.logout svg {
        color: #ef4444;
    }
    
    .dropdown-item.logout:hover {
        background: #fee;
    }
    
    .language-switcher {
        display: flex;
        gap: 8px;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .lang-btn {
        flex: 1;
        padding: 6px 12px;
        border: 2px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        color: #4a5568;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
    }
    
    .lang-btn:hover {
        border-color: #0f1d3f;
    }
    
    .lang-btn.active {
        background: #0f1d3f;
        color: white;
        border-color: #0f1d3f;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .landing-nav-links {
            gap: 20px;
        }
        
        .landing-nav-link {
            font-size: 14px;
        }
    }
    
    @media (max-width: 768px) {
        .simple-nav-container {
            padding: 16px 24px;
        }
        
        .landing-nav-links {
            display: none;
        }
        
        .brand-logo {
            width: 70px;
            height: 70px;
        }
    }
</style>

<!-- Admin Navbar -->
<nav class="simple-admin-nav">
    <div class="simple-nav-container">
        <!-- Logo & Title -->
        <a href="<?= isModerator() ? 'dashboard.php' : 'products.php' ?>" class="nav-brand-link">
            <div class="brand-logo" style="overflow: hidden; background: none; box-shadow: none;">
                <img src="../assets/logof.png" alt="<?= t('app_name') ?>" style="width: 150%; height: 150%; object-fit: contain;">
            </div>
            <span><?= t('app_name') ?></span>
        </a>
        
        <!-- Admin Navigation Links -->
        <div class="landing-nav-links">
            <!-- Reports Dashboard - Only for Moderators -->
            <?php if (isModerator()): ?>
            <a href="dashboard.php" class="landing-nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                </svg>
                <?= t('reports') ?>
            </a>
            <?php endif; ?>
            
            <!-- Products & Orders - Only for Product Managers -->
            <?php if (isProductManager()): ?>
            <a href="products.php" class="landing-nav-link <?= $current_page === 'products.php' ? 'active' : '' ?>">
                <?= t('orders') ?>
            </a>
            <a href="inventory.php" class="landing-nav-link <?= $current_page === 'inventory.php' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                </svg>
                <?= t('inventory') ?>
            </a>
            <a href="add_product.php" class="landing-nav-link <?= $current_page === 'add_product.php' ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Product
            </a>
            <?php endif; ?>
            
            <!-- Profile Dropdown -->
            <div class="profile-dropdown" id="profileDropdown">
                <div class="profile-button" onclick="toggleDropdown()">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($_SESSION['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>
                        </div>
                    </div>
                    <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                
                <div class="dropdown-menu">
                    <a href="account.php" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        My Account
                    </a>
                    
                    <div class="language-switcher">
                        <a href="?lang=en" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
                        <a href="?lang=ar" class="lang-btn <?= $lang === 'ar' ? 'active' : '' ?>">AR</a>
                    </div>
                    
                    <a href="../logout.php" class="dropdown-item logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Profile dropdown toggle
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown && !dropdown.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
    
    // Ensure logo link is clickable (fallback)
    document.addEventListener('DOMContentLoaded', function() {
        const logoLink = document.querySelector('.nav-brand-link');
        if (logoLink) {
            logoLink.addEventListener('click', function(e) {
                // Allow default link behavior
                if (e.target.tagName !== 'A') {
                    <?php if (isModerator()): ?>
                    window.location.href = 'dashboard.php';
                    <?php else: ?>
                    window.location.href = 'products.php';
                    <?php endif; ?>
                }
            });
        }
    });
</script>

<style>
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
</style>

<nav class="landing-nav">
    <div class="landing-nav-content">
        <a href="<?= isLoggedIn() && !isAdmin() ? '../home.php' : '../home.php' ?>" class="landing-brand">
            <div class="landing-brand-logo" style="overflow: hidden; background: none;">
                <img src="../assets/logof.png" alt="<?= t('app_name') ?>" style="width: 150%; height: 150%; object-fit: contain;">
            </div>
            <?= t('app_name') ?>
        </a>
        
        <div class="landing-nav-links">
            <a href="../home.php" class="landing-nav-link">
                <?= t('home') ?>
            </a>
            <a href="../about.php" class="landing-nav-link"><?= t('about') ?></a>
            <a href="../features.php" class="landing-nav-link"><?= t('features') ?></a>
            <a href="../contact.php" class="landing-nav-link"><?= t('contact') ?></a>
            <a href="<?= isLoggedIn() && !isAdmin() ? 'products.php' : '../products_public.php' ?>" class="landing-nav-link">
                <?= t('products') ?>
            </a>
            
            <?php if (isLoggedIn() && !isAdmin()): ?>
                <!-- Profile Dropdown -->
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-button" onclick="toggleDropdown()">
                        <div class="profile-avatar">
                            <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="profile-info">
                            <div class="profile-name">
                                <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                            </div>
                        </div>
                        <svg class="dropdown-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    
                    <div class="dropdown-menu">
                        <a href="dashboard.php" class="dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"/>
                                <rect x="14" y="3" width="7" height="7"/>
                                <rect x="14" y="14" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <?= t('dashboard') ?>
                        </a>
                        
                        <a href="account.php" class="dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <?= t('account') ?>
                        </a>
                        
                        <div class="language-switcher">
                            <a href="?lang=en" class="lang-btn <?= getCurrentLanguage() === 'en' ? 'active' : '' ?>">EN</a>
                            <a href="?lang=ar" class="lang-btn <?= getCurrentLanguage() === 'ar' ? 'active' : '' ?>">AR</a>
                        </div>
                        
                        <a href="../logout.php" class="dropdown-item logout">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <?= t('logout') ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="../login.php" class="landing-nav-link"><?= t('login') ?></a>
                
                <!-- Language Switcher for guests -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="?lang=en" style="color: <?= getCurrentLanguage() === 'en' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">EN</a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <a href="?lang=ar" style="color: <?= getCurrentLanguage() === 'ar' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">AR</a>
                </div>
                
                <a href="../signup.php" style="background: white; color: #0f1d3f; padding: 10px 24px; border-radius: 40px; font-weight: 700; text-decoration: none; transition: var(--transition);"><?= t('sign_up') ?></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
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
</script>

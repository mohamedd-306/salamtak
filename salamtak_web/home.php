<?php
require_once 'config.php';

// Handle language change first (before any output)
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('home.php');
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - Your Safety App</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Landing Page Specific Styles */
        .landing-page {
            margin: 0;
            padding: 0;
        }
        
        .landing-nav {
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
        
        .landing-nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .landing-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 700;
        }
        
        .landing-brand-logo {
            width: 70px;
            height: 70px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .landing-nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
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
        
        .hero-section {
            height: 100vh;
            display: flex;
            align-items: flex-end;
            color: white;
            padding: 0 80px 120px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .hero-video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }
        
        .hero-video-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(rgba(0,0,0,0), rgba(0,0,0,0));
            pointer-events: none;
        }
        
        .hero-content {
            max-width: 700px;
            position: relative;
            z-index: 1;
        }
        
        .hero-content h1 {
            font-size: 80px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: -2px;
            line-height: 1.1;
        }
        
        .hero-highlight {
            color: #FBBF24;
            font-weight: 600;
            font-size: 28px;
            margin: 20px 0;
        }
        
        .hero-content p {
            font-size: 20px;
            margin-bottom: 40px;
            line-height: 1.6;
            opacity: 0.95;
        }
        
        .store-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .store-btn {
            background: white;
            color: #0f1d3f;
            padding: 16px 32px;
            border-radius: 40px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .store-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .landing-footer {
            background: #0f1d3f;
            color: white;
            padding: 60px 80px 30px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 60px;
            margin-bottom: 40px;
        }
        
        .footer-section h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        
        .footer-section p {
            color: #ccc;
            line-height: 1.8;
        }
        
        .footer-links {
            display: flex;
            gap: 16px;
            margin-top: 16px;
        }
        
        .footer-link-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .footer-link-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 24px;
            text-align: center;
            color: #bbb;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 0 30px 80px;
            }
            
            .hero-content h1 {
                font-size: 48px;
            }
            
            .hero-highlight {
                font-size: 20px;
            }
            
            .hero-content p {
                font-size: 16px;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .landing-nav-links {
                display: none;
            }
            
            /* Disable video on mobile for performance */
            .hero-video-background video {
                display: none;
            }
            
            .hero-video-background {
                background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            }
        }
    </style>
</head>
<body class="landing-page">
    <!-- Navigation -->
    <nav class="landing-nav">
        <div class="landing-nav-content">
            <a href="home.php" class="landing-brand">
                <div class="landing-brand-logo" style="overflow: hidden; background: none;">
                    <img src="assets/logof.png" alt="<?= t('app_name') ?>" style="width: 150%; height: 150%; object-fit: contain;">
                </div>
                <?= t('app_name') ?>
            </a>
            
            <div class="landing-nav-links">
                <a href="home.php" class="landing-nav-link">
                    Home
                </a>
                <a href="about.php" class="landing-nav-link">About</a>
                <a href="features.php" class="landing-nav-link">Features</a>
                <a href="contact.php" class="landing-nav-link">Contact</a>
                <a href="<?= isLoggedIn() && !isAdmin() ? 'user/products.php' : 'products_public.php' ?>" class="landing-nav-link">
                    Products
                </a>
                
                <?php if (isLoggedIn()): ?>
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
                            <a href="<?= isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php' ?>" class="dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"/>
                                    <rect x="14" y="3" width="7" height="7"/>
                                    <rect x="14" y="14" width="7" height="7"/>
                                    <rect x="3" y="14" width="7" height="7"/>
                                </svg>
                                Dashboard
                            </a>
                            
                            <a href="<?= isAdmin() ? 'admin/account.php' : 'user/account.php' ?>" class="dropdown-item">
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
                            
                            <a href="logout.php" class="dropdown-item logout">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="landing-nav-link">Login</a>
                    
                    <!-- Language Switcher -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="?lang=en" style="color: <?= $lang === 'en' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">EN</a>
                        <span style="color: rgba(255,255,255,0.5);">|</span>
                        <a href="?lang=ar" style="color: <?= $lang === 'ar' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">AR</a>
                    </div>
                    
                    <a href="signup.php" style="background: white; color: #0f1d3f; padding: 10px 24px; border-radius: 40px; font-weight: 700; text-decoration: none; transition: var(--transition);">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Video Background -->
        <div class="hero-video-background">
            <video autoplay muted loop playsinline>
                <source src="assets/videos/background.mp4" type="video/mp4">
            </video>
        </div>
        
        <div class="hero-content">
            <h1><?= t('app_name') ?></h1>
            
            <div class="hero-highlight">
                <?= t('your_safety_route_voice') ?>
            </div>
            
            <p>
                <?= t('report_track_stay_informed') ?>
            </p>
            
            <div class="store-buttons">
                <a href="https://www.apple.com/app-store/" class="store-btn" target="_blank">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <?= t('app_store') ?>
                </a>
                
                <a href="https://play.google.com/" class="store-btn" target="_blank">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <?= t('google_play') ?>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="landing-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?= t('app_name') ?></h3>
                <p>
                    Helping citizens report road issues, improve traffic safety,
                    and create smarter cities across Egypt.
                </p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <div class="footer-links">
                    <a href="https://www.apple.com/app-store/" target="_blank" class="footer-link-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                    </a>
                    <a href="https://play.google.com/" target="_blank" class="footer-link-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="footer-links">
                    <a href="https://www.facebook.com/share/17XLiyy9kg/?mibextid=wwXIfr" target="_blank" class="footer-link-icon" onmouseover="this.style.background='#1877F2'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/salamtak2025?igsh=dXJqeG9wczd3bjg%3D&utm_source=qr" target="_blank" class="footer-link-icon" onmouseover="this.style.background='linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M7.8,2H16.2C19.4,2 22,4.6 22,7.8V16.2A5.8,5.8 0 0,1 16.2,22H7.8C4.6,22 2,19.4 2,16.2V7.8A5.8,5.8 0 0,1 7.8,2M7.6,4A3.6,3.6 0 0,0 4,7.6V16.4C4,18.39 5.61,20 7.6,20H16.4A3.6,3.6 0 0,0 20,16.4V7.6C20,5.61 18.39,4 16.4,4H7.6M17.25,5.5A1.25,1.25 0 0,1 18.5,6.75A1.25,1.25 0 0,1 17.25,8A1.25,1.25 0 0,1 16,6.75A1.25,1.25 0 0,1 17.25,5.5M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9Z"/>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@salamtak2025?_r=1&_t=ZS-96FVtju7nI9" target="_blank" class="footer-link-icon" onmouseover="this.style.background='#000000'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                            <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <p>
                    Cairo, Egypt<br>
                    support@salamtak.com<br>
                    +20 100 000 0000
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            © 2026 <?= t('app_name') ?>. All Rights Reserved.
        </div>
    </footer>
    
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
</body>
</html>

<nav class="landing-nav" style="position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; background: linear-gradient(135deg, rgba(15, 29, 63, 0.95) 0%, rgba(26, 45, 90, 0.95) 100%); backdrop-filter: blur(20px); padding: 16px 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
        <a href="home.php" style="display: flex; align-items: center; gap: 12px; color: white; text-decoration: none; font-size: 24px; font-weight: 700;">
            <div style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img src="assets/logof.png" alt="<?= t('app_name') ?>" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <?= t('app_name') ?>
        </a>
        
        <div style="display: flex; gap: 32px; align-items: center;">
            <a href="about.php" style="color: white; text-decoration: none; font-weight: 600; font-size: 15px; transition: var(--transition);"><?= t('about') ?></a>
            <a href="features.php" style="color: white; text-decoration: none; font-weight: 600; font-size: 15px; transition: var(--transition);"><?= t('features') ?></a>
            <a href="contact.php" style="color: white; text-decoration: none; font-weight: 600; font-size: 15px; transition: var(--transition);"><?= t('contact') ?></a>
            <a href="products_public.php" style="color: white; text-decoration: none; font-weight: 600; font-size: 15px; transition: var(--transition);">
                Products
            </a>
            <?php if (isLoggedIn()): ?>
                <!-- Language Switcher -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="?lang=en" style="color: <?= getCurrentLanguage() === 'en' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">EN</a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <a href="?lang=ar" style="color: <?= getCurrentLanguage() === 'ar' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">AR</a>
                </div>
                
                <a href="<?= isAdmin() ? 'admin/dashboard.php' : 'user/dashboard.php' ?>" style="background: white; color: #0f1d3f; padding: 10px 24px; border-radius: 40px; font-weight: 700; text-decoration: none; transition: var(--transition);">
                    <?= t('dashboard') ?>
                </a>
                <a href="<?= isAdmin() ? 'admin/account.php' : 'user/account.php' ?>" style="display: flex; align-items: center; gap: 10px; padding: 6px 12px 6px 6px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.2); cursor: pointer; transition: var(--transition); text-decoration: none; color: white;">
                    <div style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                        <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-start;">
                        <div style="font-size: 13px; font-weight: 600; line-height: 1; margin-bottom: 2px; color: white;">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?>
                        </div>
                    </div>
                </a>
                <a href="logout.php" class="landing-nav-link"><img src="uploads/logout.png" width="30" height="30"></a>
            <?php else: ?>
                <a href="login.php" style="color: white; text-decoration: none; font-weight: 600; font-size: 15px; transition: var(--transition);"><?= t('login') ?></a>
                
                <!-- Language Switcher -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="?lang=en" style="color: <?= getCurrentLanguage() === 'en' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">EN</a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <a href="?lang=ar" style="color: <?= getCurrentLanguage() === 'ar' ? '#fff' : 'rgba(255,255,255,0.7)' ?>; text-decoration: none; font-weight: 600; font-size: 14px; transition: var(--transition);">AR</a>
                </div>
                
                <a href="signup.php" style="background: white; color: #0f1d3f; padding: 10px 24px; border-radius: 40px; font-weight: 700; text-decoration: none; transition: var(--transition);"><?= t('sign_up') ?></a>
                <a href="logout.php" class="landing-nav-link"><img src="uploads/logout.png" width="30" height="30"></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

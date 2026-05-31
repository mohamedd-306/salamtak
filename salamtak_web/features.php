<?php
require_once 'config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: features.php");
    exit();
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

$features = [
    ['icon' => 'car', 'title' => t('winch_title'), 'desc' => t('winch_description')],
    ['icon' => 'alert-triangle', 'title' => t('potholes_title'), 'desc' => t('potholes_description')],
    ['icon' => 'zap', 'title' => t('light_pole_title'), 'desc' => t('light_pole_description')],
    ['icon' => 'droplet', 'title' => t('broken_pipe_title'), 'desc' => t('broken_pipe_description')],
    ['icon' => 'file-text', 'title' => t('report_title'), 'desc' => t('report_description')],
    ['icon' => 'alert-circle', 'title' => t('broken_glass_title'), 'desc' => t('broken_glass_description')],
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - Features</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/public_header.php'; ?>
    
    <div class="container" style="padding-top: 120px; text-align: center;">
        <section class="section">
            <div style="background: var(--warning); color: var(--text-primary); padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 700; display: inline-block; margin-bottom: 20px;">
                <?= t('core_features_title') ?>
            </div>
            
            <h1 style="font-size: 48px; font-weight: 800; color: var(--text-primary); margin-bottom: 20px;">
                <?= t('built_for_egyptian_roads_title') ?>
            </h1>
            
            <p style="color: var(--text-secondary); font-size: 18px; max-width: 650px; margin: 0 auto 60px;">
                <?= t('app_name') ?> <?= t('combines_cutting_edge') ?>
            </p>
            
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); text-align: left;">
                <?php foreach ($features as $feature): ?>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning) 0%, var(--warning-light) 100%);">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <?php if ($feature['icon'] === 'car'): ?>
                                    <path d="M5 17h14v2H5v-2zm0-4h14v2H5v-2zm0-4h14v2H5V9zm0-4h14v2H5V5z"/>
                                    <path d="M18 8l-4-4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <circle cx="8.5" cy="16.5" r="1.5"/>
                                    <circle cx="15.5" cy="16.5" r="1.5"/>
                                    <path d="M3 12h18M3 8h18"/>
                                <?php elseif ($feature['icon'] === 'alert-triangle'): ?>
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                <?php elseif ($feature['icon'] === 'zap'): ?>
                                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                <?php elseif ($feature['icon'] === 'droplet'): ?>
                                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                <?php elseif ($feature['icon'] === 'file-text'): ?>
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                <?php else: ?>
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                <?php endif; ?>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;"><?= $feature['title'] ?></h3>
                            <p style="font-size: 14px; color: var(--text-secondary);"><?= $feature['desc'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    
    <?php include 'includes/public_footer.php'; ?>
    
    <!-- Quick Report Button (for logged-in users) -->
    <?php if (isLoggedIn() && !isAdmin()): ?>
        <a href="user/services.php" style="position: fixed; bottom: 30px; right: 30px; width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4); z-index: 999; transition: var(--transition); text-decoration: none;" onmouseover="this.style.transform='scale(1.1) rotate(5deg)'" onmouseout="this.style.transform='scale(1)'">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </a>
    <?php endif; ?>
</body>
</html>

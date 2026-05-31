<?php
require_once 'config.php';

// Handle language change first (before any output)
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('about.php');
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - About</title>
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
    
    <div class="container" style="padding-top: 120px;">
        <section class="section">
            <div class="badge-problem" style="background: var(--warning); color: var(--text-primary); padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 700; display: inline-block; margin-bottom: 20px;">
                <?= t('the_problem') ?>
            </div>
            
            <h1 style="font-size: 48px; font-weight: 800; color: var(--text-primary); line-height: 1.2; margin-bottom: 20px;">
                <?= t('every_day_drivers_face') ?>
            </h1>
            
            <p style="color: var(--text-secondary); font-size: 18px; max-width: 600px; line-height: 1.6;">
                <?= t('from_potholes_accidents') ?>
            </p>
        </section>
        
        <section class="section">
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">1M+</div>
                        <div class="stat-label"><?= t('road_incidents_yearly') ?></div>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                            <?= t('thousands_hazards_affect') ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card stat-grey">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">45min</div>
                        <div class="stat-label"><?= t('average_delay') ?></div>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                            <?= t('unexpected_closures_cause') ?>
                        </p>
                    </div>
                </div>
                
                <div class="stat-card stat-danger">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">78%</div>
                        <div class="stat-label"><?= t('lack_awareness') ?></div>
                        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px;">
                            <?= t('drivers_discover_late') ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="section">
            <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-radius: var(--radius-2xl); padding: 60px; box-shadow: var(--shadow-xl);">
                <h2 style="font-size: 36px; font-weight: 800; margin-bottom: 20px;"><?= t('app_name') ?> <?= t('changes_everything') ?></h2>
                
                <p style="font-size: 18px; line-height: 1.7; max-width: 700px; opacity: 0.95;">
                    <?= t('our_mobile_app_empowers') ?>
                </p>
                
                <a href="features.php" class="btn btn-primary" style="background: white; color: var(--primary); margin-top: 30px; display: inline-flex; align-items: center; gap: 10px;">
                    <?= t('explore_features') ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
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

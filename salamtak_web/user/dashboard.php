<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: dashboard.php");
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get user statistics from Firestore
$all_reports = queryFirestoreCollection('reports', 'uid', $user_id);

$total = count($all_reports);
$pending = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'pending'; }));
$in_progress = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'in_progress'; }));
$resolved = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'resolved'; }));

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Set page title for header
$page_title = t('home');
$page_subtitle = htmlspecialchars($user_name);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('home') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
        body.dashboard-page {
            background: white !important;
        }
        .video-background {
            display: none !important;
        }

        /* Navbar Styles */
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
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
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
            transition: all 0.3s;
        }
        
        .landing-nav-link:hover {
            color: #FBBF24;
        }
    </style>
</head>
<body class="dashboard-page">
    
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <!-- 1. REPORT A PROBLEM (TOP) -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title"><?= t('report_problem') ?></h2>
            </div>
            
            <a href="services.php" class="quick-action-card">
                <div class="action-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div class="action-content">
                    <div class="action-title"><?= t('report_problem') ?></div>
                    <div class="action-subtitle">Select problem type</div>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </a>
        </section>
        
        <!-- 2. STATUS (MIDDLE) -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title"><?= t('status') ?></h2>
            </div>
            
            <div class="status-legend">
                <div class="legend-item">
                    <div class="legend-dot legend-warning"></div>
                    <span><?= t('pending') ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot legend-grey"></div>
                    <span><?= t('in_progress') ?></span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot legend-success"></div>
                    <span><?= t('resolved') ?></span>
                </div>
            </div>
        </section>
        
        <!-- 3. DASHBOARD OF REPORTS (BOTTOM) -->
        <section class="section">
            <div class="dashboard-header">
                <div>
                    <!-- User name removed -->
                </div>
            </div>
        </section>
        
        <section class="section">
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label"><?= t('total') ?></div>
                    </div>
                </div>
                
                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $pending ?></div>
                        <div class="stat-label"><?= t('pending') ?></div>
                    </div>
                </div>
                
                <div class="stat-card stat-grey">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $in_progress ?></div>
                        <div class="stat-label"><?= t('in_progress') ?></div>
                    </div>
                </div>
                
                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $resolved ?></div>
                        <div class="stat-label"><?= t('resolved') ?></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

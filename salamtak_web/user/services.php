<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: services.php");
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Set page title for header
$page_title = t('report_problem');
$page_subtitle = 'Select problem type';

$problem_types = [
    [
        'type' => 'Pothole',
        'icon' => 'warning',
        'color' => 'warning',
        'description' => 'Road damage or holes'
    ],
    [
        'type' => 'Broken Pipe',
        'icon' => 'water',
        'color' => 'primary',
        'description' => 'Water leaks or broken pipes'
    ],
    [
        'type' => 'Other',
        'icon' => 'alert',
        'color' => 'grey',
        'description' => 'Other infrastructure issues'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('report_problem') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2.0">
    <style>
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
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <section class="section">
            <div class="problem-types">
            <?php foreach ($problem_types as $problem): ?>
                <a href="report.php?type=<?= urlencode($problem['type']) ?>" class="problem-card problem-<?= $problem['color'] ?>">
                    <div class="problem-icon">
                        <?php if ($problem['icon'] === 'warning'): ?>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                <line x1="12" y1="9" x2="12" y2="13"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                        <?php elseif ($problem['icon'] === 'water'): ?>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                        <?php else: ?>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="problem-content">
                        <h3><?= t(strtolower(str_replace(' ', '_', $problem['type']))) ?></h3>
                        <p><?= $problem['description'] ?></p>
                    </div>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            <?php endforeach; ?>
            </div>
        </section>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

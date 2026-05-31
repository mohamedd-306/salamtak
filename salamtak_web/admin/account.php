<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: account.php");
    exit();
}

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Get admin info from session
$admin_name = $_SESSION['name'] ?? 'Administrator';
$admin_work_id = $_SESSION['work_id'] ?? 'N/A';
$admin_email = 'admin@salamtak.com'; // Default email
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('account') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            min-height: 100vh;
            padding-top: 90px;
        }
        
        .account-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 32px 24px;
        }
        
        .account-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .account-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 32px;
            border-bottom: 2px solid var(--border-light);
        }
        
        .admin-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
            font-weight: 800;
            color: white;
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }
        
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            border-radius: 12px;
            color: white;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        
        .account-name {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        
        .account-role {
            font-size: 16px;
            color: var(--text-secondary);
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .info-item {
            background: var(--surface);
            border: 2px solid var(--border-light);
            border-radius: 16px;
            padding: 20px;
            transition: var(--transition);
        }
        
        .info-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .readonly-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid var(--border-light);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid;
        }
        
        .btn-language {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .btn-language:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-logout {
            background: white;
            color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-logout:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);
        }
        
        .back-btn {
            position: fixed;
            top: 24px;
            left: 24px;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            text-decoration: none;
            color: white;
        }
        
        .back-btn:hover {
            transform: translateX(-4px);
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        
        .language-toggle {
            display: flex;
            gap: 12px;
            padding: 20px;
            background: var(--surface);
            border: 2px solid var(--border-light);
            border-radius: 16px;
            margin-bottom: 24px;
        }
        
        .lang-option {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid var(--border);
            background: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .lang-option:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .lang-option.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        @media (max-width: 768px) {
            .account-card {
                padding: 28px 20px;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .account-name {
                font-size: 24px;
            }
            
            .admin-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="dashboard.php" class="back-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
    </a>
    
    <div class="account-container">
        <div class="account-card">
            <!-- Header -->
            <div class="account-header">
                <div class="admin-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <?= strtoupper(t('admin')) ?>
                </div>
                <div class="admin-avatar">
                    <?= strtoupper(substr($admin_name, 0, 1)) ?>
                </div>
                <h1 class="account-name"><?= htmlspecialchars($admin_name) ?></h1>
                <p class="account-role"><?= t('admin_panel') ?></p>
            </div>
            
            <!-- Language Switcher -->
            <div class="language-toggle">
                <a href="?lang=en" class="lang-option <?= $lang === 'en' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <?= t('english') ?>
                </a>
                <a href="?lang=ar" class="lang-option <?= $lang === 'ar' ? 'active' : '' ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <?= t('arabic') ?>
                </a>
            </div>
            
            <!-- Profile Information (Read-Only) -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <?= t('full_name') ?>
                    </div>
                    <div class="info-value">
                        <?= htmlspecialchars($admin_name) ?>
                        <span class="readonly-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <?= t('view') ?> Only
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <?= t('work_id') ?>
                    </div>
                    <div class="info-value">
                        <?= htmlspecialchars($admin_work_id) ?>
                        <span class="readonly-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <?= t('view') ?> Only
                        </span>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <?= t('email') ?>
                    </div>
                    <div class="info-value">
                        <?= htmlspecialchars($admin_email) ?>
                        <span class="readonly-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <?= t('view') ?> Only
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="dashboard.php" class="action-btn btn-language">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <?= t('dashboard') ?>
                </a>
                
                <a href="../logout.php" class="action-btn btn-logout" onclick="return confirm('<?= t('sign_out_confirm') ?>')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <?= t('logout') ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

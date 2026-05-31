<?php
require_once '../config.php';

// Handle language change first (before any output)
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    // Redirect to same page without lang parameter to avoid reprocessing
    $redirect_url = 'dashboard.php';
    if (isset($_GET['filter'])) {
        $redirect_url .= '?filter=' . urlencode($_GET['filter']);
    }
    redirect($redirect_url);
}

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $report_id = $_POST['report_id'];
    $new_status = $_POST['status'];
    
    updateFirestoreDocument('reports', $report_id, ['status' => $new_status]);
    $success = t('status_updated');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Get all reports from Firestore
$all_reports = queryFirestoreCollection('reports');

// Calculate statistics
$total = count($all_reports);
$pending = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'pending'; }));
$in_progress = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'in_progress'; }));
$resolved = count(array_filter($all_reports, function($r) { return ($r['status'] ?? '') === 'resolved'; }));

// Filter reports
if ($filter === 'all') {
    $reports = $all_reports;
} else {
    $reports = array_filter($all_reports, function($report) use ($filter) {
        return ($report['status'] ?? '') === $filter;
    });
}

// Sort by createdAt descending
usort($reports, function($a, $b) {
    $timeA = isset($a['createdAt']) ? strtotime($a['createdAt']) : 0;
    $timeB = isset($b['createdAt']) ? strtotime($b['createdAt']) : 0;
    return $timeB - $timeA;
});

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

function getStatusClass($status) {
    switch ($status) {
        case 'pending': return 'status-warning';
        case 'in_progress': return 'status-purple';
        case 'resolved': return 'status-success';
        default: return 'status-secondary';
    }
}

function formatDate($date) {
    if (empty($date)) return 'N/A';
    $timestamp = strtotime($date);
    if ($timestamp === false) return 'N/A';
    return date('M d, Y • h:i A', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('admin') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin Page Navbar - Match Home Page */
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
        .nav-brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 24px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .nav-brand-section:hover {
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
        
        /* Admin page background */
        body.admin-page {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            min-height: 100vh;
        }
        
        .admin-content-wrapper {
            padding-top: 70px;
            min-height: 100vh;
            overflow-y: auto;
            margin-top: 0;
        }
        
        /* Scrollable Control Panel */
        .control-panel-fixed {
            position: relative;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            backdrop-filter: blur(30px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: -70px;
            padding-top: 70px;
        }
        
        .control-panel-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px 12px 32px;
        }
        
        .control-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .panel-title-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .admin-shield-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .panel-title {
            font-size: 22px;
            font-weight: 900;
            color: white;
            margin: 0;
            letter-spacing: -1px;
            text-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
        }
        
        .refresh-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .refresh-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }
        
        .refresh-btn svg {
            flex-shrink: 0;
        }
        
        .stats-grid-panel {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .stat-card-panel {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card-panel::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .stat-card-panel:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }
        
        .stat-icon-panel {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }
        
        .stat-total .stat-icon-panel {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
        }
        
        .stat-pending .stat-icon-panel {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }
        
        .stat-progress .stat-icon-panel {
            background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%);
            box-shadow: 0 4px 16px rgba(209, 213, 219, 0.4);
        }
        
        .stat-progress .stat-icon-panel svg {
            color: #6B7280;
        }
        
        .stat-resolved .stat-icon-panel {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
        }
        
        .stat-icon-panel svg {
            color: white;
        }
        
        .stat-content-panel {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        .stat-value-panel {
            font-size: 30px;
            font-weight: 900;
            color: white;
            line-height: 1;
            margin-bottom: 4px;
            letter-spacing: -1.5px;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
        }
        
        .stat-label-panel {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        /* Adjust tabs position */
        .admin-tabs {
            position: relative;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            background: #ffffff;
            backdrop-filter: blur(30px);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            padding: 12px 32px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            text-decoration: none;
            color: #6b7280;
            font-size: 14px;
            font-weight: 700;
            border-radius: 12px;
            white-space: nowrap;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: relative;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
        }
        
        .tab svg {
            flex-shrink: 0;
        }
        
        .tab:hover {
            color: #374151;
            background: #e5e7eb;
            transform: translateY(-2px);
            border-color: #d1d5db;
        }
        
        /* Hover colors for each tab type */
        .tab.tab-pending:hover {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border-color: #f59e0b;
        }
        
        .tab.tab-progress:hover {
            background: rgba(229, 231, 235, 0.3);
            color: #6B7280;
            border-color: #9CA3AF;
        }
        
        .tab.tab-resolved:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-color: #10b981;
        }
        
        .tab.tab-all:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border-color: #3b82f6;
        }
        
        .tab.active {
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
            border-color: var(--primary);
        }
        
        /* Specific colors for each tab */
        .tab.tab-pending.active {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.5);
            border-color: #F59E0B;
        }
        
        .tab.tab-progress.active {
            background: linear-gradient(135deg, #E5E7EB 0%, #D1D5DB 100%);
            box-shadow: 0 4px 16px rgba(209, 213, 219, 0.4);
            border-color: #9CA3AF;
            color: #374151;
        }
        
        .tab.tab-resolved.active {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
            border-color: #10B981;
        }
        
        .tab.tab-all.active {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
            border-color: #3B82F6;
        }
        
        /* Content spacing for scrollable elements */
        .container {
            margin-top: 0;
            padding-bottom: 40px;
            overflow-y: visible;
        }
        
        body.admin-page {
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .landing-nav-links {
                gap: 20px;
            }
            
            .landing-nav-link {
                font-size: 14px;
            }
            
            .stats-grid-panel {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .stat-card-panel {
                padding: 16px;
            }
            
            .stat-icon-panel {
                width: 48px;
                height: 48px;
            }
            
            .stat-icon-panel svg {
                width: 20px;
                height: 20px;
            }
            
            .stat-value-panel {
                font-size: 28px;
            }
            
            .stat-label-panel {
                font-size: 11px;
            }
            
            .admin-tabs {
                padding: 12px 20px;
            }
            
            .tab {
                padding: 10px 18px;
                font-size: 12px;
            }
            
            .container {
                margin-top: 0;
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
            
            .control-panel-container {
                padding: 20px;
            }
            
            .panel-title {
                font-size: 20px;
            }
            
            .refresh-btn span {
                display: none;
            }
            
            .refresh-btn {
                padding: 10px;
                width: 40px;
                height: 40px;
                justify-content: center;
            }
            
            .stats-grid-panel {
                gap: 10px;
            }
            
            .stat-card-panel {
                padding: 14px;
                gap: 12px;
            }
            
            .stat-icon-panel {
                width: 44px;
                height: 44px;
            }
            
            .stat-icon-panel svg {
                width: 18px;
                height: 18px;
            }
            
            .stat-value-panel {
                font-size: 24px;
            }
            
            .stat-label-panel {
                font-size: 10px;
            }
            
            .admin-tabs {
                padding: 10px 16px;
                gap: 6px;
            }
            
            .tab {
                padding: 10px 16px;
                font-size: 11px;
                gap: 6px;
            }
            
            .tab svg {
                width: 14px;
                height: 14px;
            }
            
            .container {
                margin-top: 0;
            }
        }
    </style>
</head>
<body class="admin-page dashboard-page">
    <!-- Simple Modern Navbar -->
    <nav class="simple-admin-nav">
        <div class="simple-nav-container">
            <!-- Logo & Title -->
            <a href="dashboard.php" style="text-decoration: none;">
                <div class="nav-brand-section">
                    <div class="brand-logo" style="overflow: hidden; background: none; box-shadow: none;">
                        <img src="../assets/logof.png" alt="<?= t('app_name') ?>" style="width: 150%; height: 150%; object-fit: contain;">
                    </div>
                    <?= t('app_name') ?>
                </div>
            </a>
            
            <!-- Admin Navigation Links -->
            <div class="landing-nav-links">
                <a href="dashboard.php" class="landing-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Dashboard
                </a>
                <a href="products.php" class="landing-nav-link">
                    Orders
                </a>
                <a href="inventory.php" class="landing-nav-link" style="display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    Inventory
                </a>
                <a href="add_product.php" class="landing-nav-link" style="display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add Product
                </a>
                
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
    
    <div class="admin-content-wrapper">
    
    <!-- Fixed Control Panel with Stats -->
    <div class="control-panel-fixed">
        <div class="control-panel-container">
            <div class="control-panel-header">
                <div class="panel-title-section">
                    <div class="admin-shield-badge">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span>ADMIN</span>
                    </div>
                    <h2 class="panel-title"><?= t('control_panel') ?></h2>
                </div>
                <div style="display: flex; gap: 12px;">
                    <button onclick="location.reload()" class="refresh-btn" title="Refresh Dashboard">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
            
            <div class="stats-grid-panel">
                <div class="stat-card-panel stat-total">
                    <div class="stat-icon-panel">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M3 3h18v18H3zM21 9H3M21 15H3M12 3v18"/>
                        </svg>
                    </div>
                    <div class="stat-content-panel">
                        <div class="stat-value-panel"><?= $total ?></div>
                        <div class="stat-label-panel"><?= t('total') ?> Reports</div>
                    </div>
                </div>
                
                <div class="stat-card-panel stat-pending">
                    <div class="stat-icon-panel">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="stat-content-panel">
                        <div class="stat-value-panel"><?= $pending ?></div>
                        <div class="stat-label-panel"><?= t('pending') ?></div>
                    </div>
                </div>
                
                <div class="stat-card-panel stat-progress">
                    <div class="stat-icon-panel">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <div class="stat-content-panel">
                        <div class="stat-value-panel"><?= $in_progress ?></div>
                        <div class="stat-label-panel"><?= t('in_progress') ?></div>
                    </div>
                </div>
                
                <div class="stat-card-panel stat-resolved">
                    <div class="stat-icon-panel">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="stat-content-panel">
                        <div class="stat-value-panel"><?= $resolved ?></div>
                        <div class="stat-label-panel"><?= t('resolved') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="admin-tabs">
        <a href="?filter=all" class="tab tab-all <?= $filter === 'all' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M3 3h18v18H3zM21 9H3M21 15H3M12 3v18"/>
            </svg>
            <?= t('all') ?>
        </a>
        <a href="?filter=pending" class="tab tab-pending <?= $filter === 'pending' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
            <?= t('pending') ?>
        </a>
        <a href="?filter=in_progress" class="tab tab-progress <?= $filter === 'in_progress' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <?= t('in_progress') ?>
        </a>
        <a href="?filter=resolved" class="tab tab-resolved <?= $filter === 'resolved' ? 'active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <?= t('resolved') ?>
        </a>
    </div>
    
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM21 9H3M21 15H3M12 3v18"/>
                </svg>
                <h3><?= t('no_reports') ?></h3>
            </div>
        <?php else: ?>
            <div class="admin-reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="admin-report-card">
                        <div class="report-card-header <?= getStatusClass($report['status'] ?? 'pending') ?>">
                            <h3><?= htmlspecialchars($report['type'] ?? 'N/A') ?></h3>
                            <span class="status-badge"><?= t($report['status'] ?? 'pending') ?></span>
                        </div>
                        
                        <div class="report-card-body">
                            <?php if (!empty($report['imagePath'])): ?>
                                <div class="report-image">
                                    <?php
                                    // Handle different image path formats
                                    $imageSrc = $report['imagePath'];
                                    // If it's a relative path (not base64 or full URL), add ../ for admin folder
                                    if (!str_starts_with($imageSrc, 'data:image') && 
                                        !str_starts_with($imageSrc, 'http://') && 
                                        !str_starts_with($imageSrc, 'https://')) {
                                        $imageSrc = '../' . $imageSrc;
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($imageSrc) ?>" 
                                         alt="Report image"
                                         onerror="this.style.display='none'; this.parentElement.style.display='none';">
                                </div>
                            <?php endif; ?>
                            
                            <p class="report-description"><?= htmlspecialchars($report['description'] ?? 'No description provided') ?></p>
                            
                            <div class="report-meta">
                                <div class="meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span><?= formatDate($report['createdAt'] ?? '') ?></span>
                                </div>
                                
                                <div class="meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span><?= t('user') ?>: <?= htmlspecialchars($report['nationalId'] ?? $report['uid'] ?? 'Unknown') ?></span>
                                </div>
                                
                                <?php if (!empty($report['location'])): ?>
                                    <div class="meta-item">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span><?= htmlspecialchars($report['location']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="status-update-section">
                                <label><?= t('update_status') ?></label>
                                <form method="POST" class="status-update-form">
                                    <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    
                                    <div class="status-buttons">
                                        <button type="submit" name="status" value="pending" 
                                                class="status-btn status-btn-warning <?= ($report['status'] ?? 'pending') === 'pending' ? 'active' : '' ?>">
                                            <?= t('pending') ?>
                                        </button>
                                        <button type="submit" name="status" value="in_progress" 
                                                class="status-btn status-btn-purple <?= ($report['status'] ?? '') === 'in_progress' ? 'active' : '' ?>">
                                            <?= t('in_progress') ?>
                                        </button>
                                        <button type="submit" name="status" value="resolved" 
                                                class="status-btn status-btn-success <?= ($report['status'] ?? '') === 'resolved' ? 'active' : '' ?>">
                                            <?= t('resolved') ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    </div> <!-- Close admin-content-wrapper -->
    
    <script>
        function confirmSignOut() {
            if (confirm('<?= t('sign_out_confirm') ?>')) {
                window.location.href = '../logout.php';
            }
        }
        
        // Video background handler
        const video = document.getElementById('bgVideo');
        const videoContainer = document.querySelector('.video-background');
        
        if (video) {
            // Check if video can be loaded
            video.addEventListener('loadeddata', function() {
                video.classList.add('loaded');
            });
            
            // Fallback if video fails to load
            video.addEventListener('error', function() {
                videoContainer.classList.add('no-video');
                video.style.display = 'none';
            });
            
            // Pause video on mobile to save battery
            if (window.innerWidth < 768) {
                video.pause();
                video.style.display = 'none';
                videoContainer.classList.add('no-video');
            }
        }
        
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
    </script>
</body>
</html>

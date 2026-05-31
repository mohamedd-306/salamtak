<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: history.php");
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Get user reports from Firestore
$reports = queryFirestoreCollection('reports', 'uid', $user_id);

// Sort by created_at descending (if createdAt timestamp exists)
usort($reports, function($a, $b) {
    $timeA = isset($a['createdAt']) ? strtotime($a['createdAt']) : 0;
    $timeB = isset($b['createdAt']) ? strtotime($b['createdAt']) : 0;
    return $timeB - $timeA;
});

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Set page title for header
$page_title = t('my_reports');
$page_subtitle = count($reports) . ' ' . t('total');

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
    <title><?= t('app_name') ?> - <?= t('my_reports') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <h3><?= t('no_reports') ?></h3>
                <p>Your submitted reports will appear here</p>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report-card">
                        <?php if ($report['imagePath']): ?>
                            <div class="report-image">
                                <img src="../<?= htmlspecialchars($report['imagePath']) ?>" alt="Report image">
                            </div>
                        <?php endif; ?>
                        
                        <div class="report-content">
                            <div class="report-header">
                                <h3><?= htmlspecialchars($report['type']) ?></h3>
                                <span class="status-badge <?= getStatusClass($report['status']) ?>">
                                    <?= t($report['status']) ?>
                                </span>
                            </div>
                            
                            <p class="report-description"><?= htmlspecialchars($report['description']) ?></p>
                            
                            <div class="report-meta">
                                <div class="meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                    <span><?= formatDate($report['createdAt'] ?? '') ?></span>
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

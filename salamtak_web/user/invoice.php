<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$order_id = $_GET['order_id'] ?? '';
if (empty($order_id)) {
    redirect('products.php');
}

// Get order
$order = getFirestoreDocument('orders', $order_id);
if (!$order || $order['userId'] !== $_SESSION['user_id']) {
    redirect('products.php');
}

// Ensure items is an array
if (!isset($order['items']) || !is_array($order['items'])) {
    $order['items'] = [];
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('invoice') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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

        body {
            background: #f8f9fa;
            padding-top: 120px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px 100px;
        }
        
        .success-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        
        .success-icon svg {
            width: 50px;
            height: 50px;
            color: #10b981;
        }
        
        .success-title {
            font-size: 28px;
            font-weight: 800;
            color: #10b981;
            margin-bottom: 8px;
        }
        
        .order-id {
            font-size: 14px;
            color: #718096;
        }
        
        .invoice-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .invoice-logo img {
            width: 60px;
            height: 60px;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            font-size: 32px;
            font-weight: 800;
            color: #0f1d3f;
            margin: 0 0 4px 0;
        }
        
        .invoice-date {
            font-size: 12px;
            color: #718096;
        }
        
        .invoice-section {
            margin-bottom: 32px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
        }
        
        .info-row {
            display: flex;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .info-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 120px;
        }
        
        .info-value {
            color: #2d3748;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        
        .items-table th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .items-table td {
            padding: 12px;
            font-size: 14px;
            color: #2d3748;
            border-bottom: 1px solid #f7fafc;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .text-right {
            text-align: right;
        }
        
        .invoice-total {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-label {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .total-amount {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .invoice-actions {
            display: flex;
            gap: 16px;
            margin-top: 32px;
        }
        
        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 29, 63, 0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #0f1d3f;
            border: 2px solid #0f1d3f;
        }
        
        .btn-secondary:hover {
            background: #f7fafc;
        }
        
        @media print {
            body {
                padding-top: 0;
            }
            
            .landing-nav,
            .invoice-actions,
            nav {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="invoice-container">
        <div class="success-header">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h1 class="success-title">✓ <?= t('order_placed_successfully') ?></h1>
            <p class="order-id"><?= t('order_id') ?>: #<?= strtoupper(substr($order_id, 0, 8)) ?></p>
        </div>
        
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="invoice-logo">
                    <img src="../assets/logof.png" alt="<?= t('app_name') ?>">
                </div>
                <div class="invoice-title">
                    <h2><?= strtoupper(t('invoice')) ?></h2>
                    <p class="invoice-date"><?= date('d/m/Y H:i', strtotime($order['createdAt'])) ?></p>
                </div>
            </div>
            
            <div class="invoice-section">
                <h3 class="section-title">👤 <?= t('customer_information') ?></h3>
                <div class="info-row">
                    <span class="info-label"><?= t('name') ?>:</span>
                    <span class="info-value"><?= htmlspecialchars($order['userName']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= t('national_id_label') ?>:</span>
                    <span class="info-value"><?= htmlspecialchars($order['nationalId']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= t('phone') ?>:</span>
                    <span class="info-value"><?= htmlspecialchars($order['phoneNumber']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?= t('address') ?>:</span>
                    <span class="info-value"><?= htmlspecialchars($order['deliveryAddress']) ?></span>
                </div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="info-row">
                        <span class="info-label"><?= t('notes') ?>:</span>
                        <span class="info-value"><?= htmlspecialchars($order['notes']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="invoice-section">
                <h3 class="section-title">📦 <?= t('order_items') ?></h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th><?= t('product') ?></th>
                            <th class="text-right"><?= t('qty') ?></th>
                            <th class="text-right"><?= t('price') ?></th>
                            <th class="text-right"><?= t('total') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['productName']) ?></td>
                                <td class="text-right">x<?= $item['quantity'] ?></td>
                                <td class="text-right">EGP <?= number_format($item['price'], 2) ?></td>
                                <td class="text-right"><strong>EGP <?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="invoice-total">
                    <span class="total-label"><?= t('total_amount') ?></span>
                    <span class="total-amount">EGP <?= number_format($order['totalAmount'], 2) ?></span>
                </div>
            </div>
            
            <div class="invoice-actions">
                <button onclick="window.print()" class="btn btn-secondary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <?= t('print_invoice') ?>
                </button>
                <a href="products.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?= t('continue_shopping') ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

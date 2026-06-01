<?php
require_once '../config.php';

// Check if user is logged in and is a product manager
if (!isLoggedIn() || !isProductManager()) {
    // Redirect moderators to dashboard (reports)
    if (isModerator()) {
        redirect('dashboard.php');
    }
    // Redirect non-admins to login
    redirect('../login.php');
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: products.php");
    exit();
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

$message = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $updated = updateFirestoreDocument('orders', $order_id, [
        'status' => $new_status,
        'updatedAt' => date('Y-m-d H:i:s')
    ]);
    
    if ($updated) {
        $message = t('order_status_updated');
    } else {
        $message = t('failed_update_order_status');
    }
}

// Get all orders
$all_orders = queryFirestoreCollection('orders');

// Sort orders by date (newest first)
usort($all_orders, function($a, $b) {
    return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
});

function getStatusColor($status) {
    switch ($status) {
        case 'pending': return '#f59e0b';
        case 'processing': return '#3b82f6';
        case 'shipped': return '#8b5cf6';
        case 'delivered': return '#10b981';
        case 'cancelled': return '#ef4444';
        default: return '#6b7280';
    }
}

function getStatusBg($status) {
    switch ($status) {
        case 'pending': return '#fef3c7';
        case 'processing': return '#dbeafe';
        case 'shipped': return '#ede9fe';
        case 'delivered': return '#d1fae5';
        case 'cancelled': return '#fee2e2';
        default: return '#f3f4f6';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('app_name') ?> - <?= t('orders_management') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 110px 40px 40px;
            min-height: 100vh;
        }
        
        /* Container */
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 32px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            margin: 0;
        }
        
        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(15, 29, 63, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 29, 63, 0.4);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }
        
        /* Success Message */
        .message {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        }
        
        /* Section */
        .section {
            background: white;
            padding: 32px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 32px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-badge {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }
        
        /* Orders Grid */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .order-card {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            border-color: #0f1d3f;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .order-id {
            font-size: 14px;
            font-weight: 700;
            color: #0f1d3f;
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .order-customer {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .customer-info h4 {
            font-size: 14px;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 2px 0;
        }
        
        .customer-info p {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }
        
        .order-delivery-info {
            background: #f7fafc;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 13px;
        }
        
        .delivery-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            align-items: flex-start;
        }
        
        .delivery-row:last-child {
            margin-bottom: 0;
        }
        
        .delivery-icon {
            color: #0f1d3f;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .delivery-label {
            font-weight: 700;
            color: #4a5568;
            min-width: 60px;
        }
        
        .delivery-value {
            color: #2d3748;
            flex: 1;
            word-break: break-word;
        }
        
        .order-items {
            background: white;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-size: 13px;
            color: #4a5568;
        }
        
        .item-qty {
            font-size: 13px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            margin-bottom: 12px;
        }
        
        .total-label {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
        }
        
        .total-amount {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .order-actions {
            display: flex;
            gap: 8px;
        }
        
        .status-select {
            flex: 1;
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .update-btn {
            padding: 8px 16px;
            background: #0f1d3f;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .update-btn:hover {
            background: #1a2d5a;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state svg {
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            body { padding: 90px 20px 20px; }
            .orders-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; gap: 16px; }
            .header-actions { width: 100%; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title"><?= t('orders_management') ?></h1>
                <p class="page-subtitle"><?= t('view_manage_customer_orders') ?></p>
            </div>
            <div class="header-actions">
                <a href="inventory.php" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    <?= t('manage_inventory') ?>
                </a>
                <a href="add_product.php" class="btn btn-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <?= t('add_new_product') ?>
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Orders Section -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?= t('recent_orders') ?>
                    <span class="section-badge"><?= count($all_orders) ?></span>
                </h2>
            </div>
            
            <?php if (empty($all_orders)): ?>
                <div class="empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <h3><?= t('no_orders_yet') ?></h3>
                    <p><?= t('customer_orders_appear_here') ?></p>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($all_orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id"><?= t('order') ?> #<?= substr($order['id'], 0, 8) ?></span>
                                <span class="order-status" style="background: <?= getStatusBg($order['status'] ?? 'pending') ?>; color: <?= getStatusColor($order['status'] ?? 'pending') ?>;">
                                    <?= t($order['status'] ?? 'pending') ?>
                                </span>
                            </div>
                            
                            <div class="order-customer">
                                <div class="customer-avatar">
                                    <?= strtoupper(substr($order['userName'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div class="customer-info">
                                    <h4><?= htmlspecialchars($order['userName'] ?? t('customer')) ?></h4>
                                    <p><?= date('M d, Y • h:i A', strtotime($order['createdAt'] ?? 'now')) ?></p>
                                </div>
                            </div>
                            
                            <!-- Delivery Information -->
                            <div class="order-delivery-info">
                                <div class="delivery-row">
                                    <svg class="delivery-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span class="delivery-label"><?= t('address') ?>:</span>
                                    <span class="delivery-value"><?= htmlspecialchars($order['deliveryAddress'] ?? t('not_provided')) ?></span>
                                </div>
                                <div class="delivery-row">
                                    <svg class="delivery-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span class="delivery-label"><?= t('phone') ?>:</span>
                                    <span class="delivery-value"><?= htmlspecialchars($order['phoneNumber'] ?? t('not_provided')) ?></span>
                                </div>
                                <?php if (!empty($order['notes'])): ?>
                                <div class="delivery-row">
                                    <svg class="delivery-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                    <span class="delivery-label"><?= t('notes') ?>:</span>
                                    <span class="delivery-value"><?= htmlspecialchars($order['notes']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-items">
                                <?php 
                                $items = $order['items'] ?? [];
                                foreach ($items as $item): 
                                ?>
                                    <div class="order-item">
                                        <span class="item-name"><?= htmlspecialchars($item['productName'] ?? 'Product') ?></span>
                                        <span class="item-qty">x<?= $item['quantity'] ?? 0 ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="order-total">
                                <span class="total-label"><?= t('total_amount') ?></span>
                                <span class="total-amount">EGP <?= number_format($order['totalAmount'] ?? 0, 2) ?></span>
                            </div>
                            
                            <form method="POST" class="order-actions">
                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?= ($order['status'] ?? '') === 'pending' ? 'selected' : '' ?>><?= t('pending') ?></option>
                                    <option value="processing" <?= ($order['status'] ?? '') === 'processing' ? 'selected' : '' ?>><?= t('processing') ?></option>
                                    <option value="shipped" <?= ($order['status'] ?? '') === 'shipped' ? 'selected' : '' ?>><?= t('shipped') ?></option>
                                    <option value="delivered" <?= ($order['status'] ?? '') === 'delivered' ? 'selected' : '' ?>><?= t('delivered') ?></option>
                                    <option value="cancelled" <?= ($order['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= t('cancelled') ?></option>
                                </select>
                                <button type="submit" name="update_order_status" class="update-btn"><?= t('update') ?></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

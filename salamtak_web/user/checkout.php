<?php
require_once '../config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Get cart
$cart = getFirestoreDocument('carts', $user_id);
$cart_items = [];
$total = 0;

if ($cart && isset($cart['items']) && !empty($cart['items'])) {
    foreach ($cart['items'] as $product_id => $quantity) {
        $product = getFirestoreDocument('products', $product_id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $total += $product['subtotal'];
            $cart_items[] = $product;
        }
    }
}

// If cart is empty, redirect to products
if (empty($cart_items)) {
    redirect('products.php');
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    if (empty($address) || strlen($address) < 10) {
        $errors[] = 'Please enter a valid delivery address (at least 10 characters)';
    }
    
    if (empty($phone) || !preg_match('/^01\d{9}$/', $phone)) {
        $errors[] = 'Please enter a valid Egyptian phone number (must start with 01 and be 11 digits, e.g., 01012345678)';
    }
    
    if (empty($errors)) {
        // Create order items array
        $order_items = [];
        foreach ($cart_items as $item) {
            $order_items[] = [
                'productId' => $item['id'],
                'productName' => $item['name'],
                'price' => (float)$item['price'],
                'quantity' => (int)$item['quantity']
            ];
        }
        
        // Prepare order data
        $order_data = [
            'userId' => $user_id,
            'nationalId' => $_SESSION['national_id'] ?? '',
            'userName' => $_SESSION['name'] ?? '',
            'totalAmount' => (float)$total,
            'status' => 'pending',
            'createdAt' => date('Y-m-d H:i:s'),
            'deliveryAddress' => $address,
            'phoneNumber' => $phone,
            'notes' => $notes
        ];
        
        // Convert items to Firestore format manually
        $fields = [];
        foreach ($order_data as $key => $value) {
            $fields[$key] = convertToFirestoreValue($value);
        }
        
        // Add items as arrayValue
        $itemsArray = [];
        foreach ($order_items as $item) {
            $itemFields = [];
            foreach ($item as $k => $v) {
                $itemFields[$k] = convertToFirestoreValue($v);
            }
            $itemsArray[] = ['mapValue' => ['fields' => $itemFields]];
        }
        $fields['items'] = ['arrayValue' => ['values' => $itemsArray]];
        
        // Save to Firestore using direct request
        $response = firestoreRequest('POST', '/orders', ['fields' => $fields]);
        
        if ($response && isset($response['name'])) {
            $order_id = basename($response['name']);
            
            // Decrease product stock for each ordered item
            foreach ($cart_items as $item) {
                $product = getFirestoreDocument('products', $item['id']);
                if ($product) {
                    $current_stock = $product['stock'] ?? 0;
                    $ordered_quantity = $item['quantity'];
                    $new_stock = max(0, $current_stock - $ordered_quantity); // Ensure stock doesn't go negative
                    
                    // Update product stock
                    updateFirestoreDocument('products', $item['id'], [
                        'stock' => $new_stock,
                        'updatedAt' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // Clear cart using multiple approaches to ensure it works
            
            // Approach 1: Delete the entire cart document
            $deleteUrl = FIRESTORE_URL . "/carts/{$user_id}";
            $ch = curl_init($deleteUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            $deleteResult = curl_exec($ch);
            $deleteCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Approach 2: If delete didn't work, try updating with empty items
            if ($deleteCode < 200 || $deleteCode >= 300) {
                $emptyCartData = [
                    'fields' => [
                        'userId' => ['stringValue' => $user_id],
                        'items' => ['mapValue' => ['fields' => []]],
                        'updatedAt' => ['stringValue' => date('Y-m-d H:i:s')],
                        'createdAt' => ['stringValue' => date('Y-m-d H:i:s')]
                    ]
                ];
                
                $updateUrl = FIRESTORE_URL . "/carts/{$user_id}";
                $ch = curl_init($updateUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emptyCartData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                $updateResult = curl_exec($ch);
                $updateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                error_log("Cart clear - Delete: $deleteCode, Update: $updateCode");
            }
            
            // Redirect to invoice
            redirect('invoice.php?order_id=' . $order_id);
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - Checkout</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow-x: hidden;
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

        /* Checkout Container */
        .checkout-wrapper {
            padding-top: 140px;
            padding-bottom: 80px;
            min-height: 100vh;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Progress Steps */
        .checkout-progress {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 60px;
            gap: 40px;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .step-circle.active {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
        }
        
        .step-circle.completed {
            background: #10b981;
            color: white;
        }
        
        .step-label {
            font-weight: 600;
            color: #4a5568;
        }
        
        .step-label.active {
            color: #0f1d3f;
        }
        
        .progress-line {
            width: 80px;
            height: 3px;
            background: #e2e8f0;
        }
        
        .progress-line.completed {
            background: #10b981;
        }
        
        /* Checkout Grid */
        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
        }
        
        /* Form Section */
        .checkout-form {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            animation: slideInLeft 0.6s ease-out;
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .form-header {
            margin-bottom: 32px;
        }
        
        .form-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .form-header p {
            color: #718096;
            font-size: 15px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .required {
            color: #ef4444;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .form-group textarea {
            padding: 14px 16px;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0f1d3f;
            box-shadow: 0 0 0 3px rgba(15, 29, 63, 0.1);
        }
        
        /* Order Summary */
        .order-summary {
            background: white;
            padding: 32px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 140px;
            animation: slideInRight 0.6s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .summary-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f7fafc;
        }
        
        .summary-header h2 {
            font-size: 22px;
            font-weight: 800;
            color: #2d3748;
        }
        
        .summary-badge {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        
        .order-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #f7fafc;
        }
        
        .item-image {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 700;
            color: #2d3748;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .item-quantity {
            font-size: 13px;
            color: #718096;
        }
        
        .item-price {
            font-weight: 700;
            color: #0f1d3f;
            font-size: 15px;
        }
        
        .summary-totals {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid #f7fafc;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .total-row.grand-total {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 2px solid #e2e8f0;
            font-size: 18px;
            font-weight: 800;
        }
        
        .total-label {
            color: #4a5568;
        }
        
        .total-value {
            font-weight: 700;
            color: #2d3748;
        }
        
        .grand-total .total-value {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 24px;
        }
        
        /* Place Order Button */
        .place-order-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 29, 63, 0.4);
        }
        
        .place-order-btn:active {
            transform: translateY(0);
        }
        
        /* Security Badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 8px;
            font-size: 13px;
            color: #10b981;
            font-weight: 600;
        }
        
        /* Error Message */
        .error-message {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #c33;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            border-left: 4px solid #ef4444;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
            
            .checkout-progress {
                gap: 20px;
            }
            
            .progress-line {
                width: 40px;
            }
            
            .step-label {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="checkout-wrapper">
        <div class="checkout-container">
            <!-- Progress Steps -->
            <div class="checkout-progress">
                <div class="progress-step">
                    <div class="step-circle completed">✓</div>
                    <span class="step-label"><?= t('cart') ?></span>
                </div>
                <div class="progress-line completed"></div>
                <div class="progress-step">
                    <div class="step-circle active">2</div>
                    <span class="step-label active"><?= t('checkout') ?></span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-circle">3</div>
                    <span class="step-label"><?= t('complete') ?></span>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <strong>⚠️ <?= t('fix_errors') ?></strong>
                    <?php foreach ($errors as $error): ?>
                        <div style="margin-top: 8px;">• <?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="checkout-grid">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <div class="form-header">
                        <h2>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <?= t('delivery_information') ?>
                        </h2>
                        <p><?= t('where_deliver_equipment') ?></p>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <?= t('delivery_address') ?> <span class="required">*</span>
                        </label>
                        <textarea name="address" required minlength="10" placeholder="<?= t('delivery_address_placeholder') ?>"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <?= t('phone_number') ?> <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <input type="tel" name="phone" required pattern="01\d{9}" maxlength="11" placeholder="01012345678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" title="Phone number must start with 01 and be 11 digits">
                        </div>
                        <small style="color: #6b7280; display: block; margin-top: 6px; font-size: 13px;">
                            📱 <?= t('phone_format_note') ?>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            <?= t('order_notes_optional') ?>
                        </label>
                        <textarea name="notes" placeholder="<?= t('order_notes_placeholder') ?>"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-header">
                        <h2><?= t('order_summary') ?></h2>
                        <span class="summary-badge"><?= count($cart_items) ?> <?= t('items') ?></span>
                    </div>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <?php 
                        // Get product image
                        $productName = strtolower($item['name']);
                        $imageUrl = '';
                        
                        if (strpos($productName, 'vest') !== false) {
                            $imageUrl = '../assets/products/vest.jpeg';
                        } elseif (strpos($productName, 'ear') !== false || strpos($productName, 'muff') !== false) {
                            $imageUrl = '../assets/products/earmuffs.jpeg';
                        } elseif (strpos($productName, 'jacket') !== false) {
                            $imageUrl = '../assets/products/jacket.jpeg';
                        } elseif (strpos($productName, 'hard hat') !== false) {
                            $imageUrl = '../assets/products/hardhat.jpeg';
                        } elseif (strpos($productName, 'helmet') !== false) {
                            $imageUrl = '../assets/products/helmet.jpeg';
                        } elseif (strpos($productName, 'boots') !== false) {
                            $imageUrl = '../assets/products/boots.jpeg';
                        } else {
                            $imageUrl = '../assets/products/placeholder.svg';
                        }
                        ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='https://via.placeholder.com/70x70/0f1d3f/ffffff?text=Product'">
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-quantity"><?= t('quantity') ?>: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-price">
                                EGP <?= number_format($item['subtotal'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-totals">
                        <div class="total-row">
                            <span class="total-label"><?= t('subtotal') ?></span>
                            <span class="total-value">EGP <?= number_format($total, 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span class="total-label"><?= t('shipping') ?></span>
                            <span class="total-value" style="color: #10b981;"><?= t('free') ?></span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="total-label"><?= t('total') ?></span>
                            <span class="total-value">EGP <?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="place-order-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <?= t('place_order') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: cart.php");
    exit();
}

if (!isLoggedIn() || isAdmin()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    $cart = getFirestoreDocument('carts', $user_id);
    if ($cart) {
        $items = $cart['items'] ?? [];
        
        if ($quantity > 0) {
            $items[$product_id] = $quantity;
        } else {
            unset($items[$product_id]);
        }
        
        updateFirestoreDocument('carts', $user_id, [
            'items' => $items,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }
    
    header("Location: cart.php");
    exit();
}

// Handle remove item
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    
    $cart = getFirestoreDocument('carts', $user_id);
    if ($cart) {
        $items = $cart['items'] ?? [];
        unset($items[$product_id]);
        
        updateFirestoreDocument('carts', $user_id, [
            'items' => $items,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }
    
    header("Location: cart.php");
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - Shopping Cart</title>
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

        .cart-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
        }
        .cart-item {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            margin-bottom: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius);
            background: var(--background);
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }
        .cart-item-price {
            font-size: 16px;
            color: var(--primary);
            font-weight: 600;
        }
        .cart-item-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 2px solid var(--border);
            background: white;
            border-radius: var(--radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: var(--transition);
        }
        .quantity-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .quantity-display {
            font-size: 18px;
            font-weight: 700;
            min-width: 40px;
            text-align: center;
        }
        .remove-btn {
            padding: 8px 16px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }
        .remove-btn:hover {
            background: #cc0000;
        }
        .cart-summary {
            background: var(--surface);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 100px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        .summary-row:last-child {
            border-bottom: none;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding-top: 120px; padding-bottom: 100px;">
        <div class="cart-header">
            <h1 style="margin: 0 0 4px 0; font-size: 28px;"><?= t('shopping_cart') ?></h1>
            <p style="margin: 0; opacity: 0.9;"><?= count($cart_items) ?> <?= t('items_in_cart') ?></p>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px;">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <h2><?= t('your_cart_empty') ?></h2>
                <p><?= t('add_products_get_started') ?></p>
                <a href="products.php" class="btn btn-primary" style="display: inline-block; margin-top: 20px;">
                    <?= t('browse_products') ?>
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
                <?php
                // Use same local image logic as products page
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
                } elseif (strpos($productName, 'helmet') !== false && strpos($productName, 'carbon') !== false) {
                    $imageUrl = '../assets/products/helmet.jpeg';
                } elseif (strpos($productName, 'boots') !== false) {
                    $imageUrl = '../assets/products/boots.jpeg';
                } else {
                    $imageUrl = '../assets/products/placeholder.svg';
                }
                ?>
                <div class="cart-item">
                    <img src="<?= htmlspecialchars($imageUrl) ?>?v=<?= time() ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>" 
                         class="cart-item-image"
                         onerror="this.src='https://via.placeholder.com/100x100/1976d2/ffffff?text=Product'">
                    
                    <div class="cart-item-info">
                        <h3 class="cart-item-name"><?= htmlspecialchars($item['name']) ?></h3>
                        <div class="cart-item-price">EGP <?= number_format($item['price'], 2) ?> <?= t('each') ?></div>
                    </div>
                    
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] - 1 ?>">
                                <button type="submit" name="update_quantity" class="quantity-btn">-</button>
                            </form>
                            
                            <span class="quantity-display"><?= $item['quantity'] ?></span>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                <button type="submit" name="update_quantity" class="quantity-btn">+</button>
                            </form>
                        </div>
                        
                        <div style="font-size: 18px; font-weight: 700; min-width: 80px; text-align: right;">
                            EGP <?= number_format($item['subtotal'], 2) ?>
                        </div>
                        
                        <a href="?remove=<?= htmlspecialchars($item['id']) ?>" 
                           class="remove-btn"
                           onclick="return confirm('<?= t('remove_item_confirm') ?>')">
                            <?= t('remove') ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span><?= t('subtotal') ?>:</span>
                    <span>EGP <?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span><?= t('shipping') ?>:</span>
                    <span><?= t('free') ?></span>
                </div>
                <div class="summary-row">
                    <span><?= t('total') ?>:</span>
                    <span>EGP <?= number_format($total, 2) ?></span>
                </div>
                
                <a href="checkout.php" class="btn btn-primary btn-block" style="margin-top: 20px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    <?= t('proceed_to_checkout') ?>
                </a>
                
                <a href="products.php" class="btn btn-block" style="margin-top: 12px; background: var(--background); color: var(--text-primary); text-align: center; display: block;">
                    <?= t('continue_shopping') ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

<?php
require_once '../config.php';

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: products.php");
    exit();
}

// Allow viewing products without login, but check for cart operations
$isLoggedIn = isLoggedIn();
$isUserLoggedIn = $isLoggedIn && !isAdmin();

$user_id = $isUserLoggedIn ? $_SESSION['user_id'] : null;
$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Handle add to cart - REQUIRES LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Check if user is logged in
    if (!$isUserLoggedIn) {
        // Redirect to login with return URL
        $_SESSION['redirect_after_login'] = 'user/products.php';
        redirect('../login.php?message=login_required');
    }
    
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Get existing cart or create new
    $cart = getFirestoreDocument('carts', $user_id);
    
    if (!$cart) {
        // Create new cart
        $cart_data = [
            'userId' => $user_id,
            'items' => [],
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        
        // Add first item
        $cart_data['items'][$product_id] = $quantity;
        
        // Create cart with user_id as document ID
        createFirestoreDocumentWithId('carts', $user_id, $cart_data);
    } else {
        // Update existing cart
        $items = $cart['items'] ?? [];
        
        if (isset($items[$product_id])) {
            $items[$product_id] += $quantity;
        } else {
            $items[$product_id] = $quantity;
        }
        
        updateFirestoreDocument('carts', $user_id, [
            'items' => $items,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }
    
    header("Location: products.php?added=1");
    exit();
}

// Get all products
$products = queryFirestoreCollection('products');

// Get user's cart count (only if logged in)
$cart_count = 0;
if ($isUserLoggedIn) {
    $cart = getFirestoreDocument('carts', $user_id);
    if ($cart && isset($cart['items'])) {
        foreach ($cart['items'] as $qty) {
            $cart_count += $qty;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= t('app_name') ?> - Products</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time() ?>">
    <style>
        .products-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-badge {
            position: relative;
            display: inline-block;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 100px;
        }
        .product-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 2px solid var(--border);
            overflow: hidden;
            transition: var(--transition);
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
        }
        .product-image-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1976d2;
            font-size: 48px;
        }
        .product-info {
            padding: 16px;
        }
        .product-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }
        .product-description {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0 0 12px 0;
            line-height: 1.5;
        }
        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 16px 0;
        }
        .product-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            text-align: center;
            font-weight: 600;
        }
        .add-to-cart-btn {
            flex: 1;
            padding: 10px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .add-to-cart-btn:hover {
            background: var(--primary-dark);
        }
        .success-message {
            background: #4caf50;
            color: white;
            padding: 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container" style="padding-top: 120px; padding-bottom: 100px;">
        <?php if (isset($_GET['added'])): ?>
            <div class="success-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Product added to cart successfully!
            </div>
        <?php endif; ?>
        
        <div class="products-header">
            <div>
                <h1 style="margin: 0 0 4px 0; font-size: 28px;">Products</h1>
                <p style="margin: 0; opacity: 0.9;">Browse and shop our products</p>
            </div>
            <?php if ($isUserLoggedIn): ?>
                <a href="cart.php" class="cart-badge">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="../login.php" style="background: white; color: var(--primary); padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Login to Shop
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 16px;">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <h2>No Products Available</h2>
                <p>Check back later for new products</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php 
                        // Use local images from assets/products/ (.jpeg extension)
                        $productName = strtolower($product['name']);
                        $imageUrl = '';
                        $description = $product['description'];
                        
                        if (strpos($productName, 'vest') !== false) {
                            $imageUrl = '../assets/products/vest.jpeg';
                            $description = 'High-visibility safety vest with reflective strips. Multiple pockets for tools. Meets ANSI/ISEA standards.';
                        } elseif (strpos($productName, 'ear') !== false || strpos($productName, 'muff') !== false) {
                            $imageUrl = '../assets/products/earmuffs.jpeg';
                            $description = 'Professional noise cancelling ear muffs with 34dB noise reduction. Padded headband for comfort. Adjustable fit.';
                        } elseif (strpos($productName, 'jacket') !== false) {
                            $imageUrl = '../assets/products/jacket.jpeg';
                            $description = 'High-visibility safety jacket with reflective strips. Waterproof and windproof. Multiple pockets with zipper closure.';
                        } elseif (strpos($productName, 'hard hat') !== false) {
                            $imageUrl = '../assets/products/hardhat.jpeg';
                            $description = 'ANSI Z89.1 certified safety hard hat. Adjustable ratchet suspension. Ventilation slots for comfort. Impact resistant.';
                        } elseif (strpos($productName, 'helmet') !== false && strpos($productName, 'carbon') !== false) {
                            $imageUrl = '../assets/products/helmet.jpeg';
                            $description = 'Professional carbon fiber safety helmet. Advanced impact protection. Clear anti-fog visor. Lightweight design.';
                        } elseif (strpos($productName, 'boots') !== false) {
                            $imageUrl = '../assets/products/boots.jpeg';
                            $description = 'Steel toe safety boots with slip-resistant sole. Oil and chemical resistant. Orange padded collar. Breathable mesh.';
                        } else {
                            $imageUrl = '../assets/products/placeholder.svg';
                        }
                        ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>?v=<?= time() ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/400x400/1976d2/ffffff?text=<?= urlencode($product['name']) ?>'">
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars($description) ?></p>
                            <div class="product-price">EGP <?= number_format($product['price'], 2) ?></div>
                            
                            <?php if ($isUserLoggedIn): ?>
                                <form method="POST" class="product-actions">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="99" class="quantity-input">
                                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"/>
                                            <circle cx="20" cy="21" r="1"/>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        </svg>
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="product-actions">
                                    <a href="../login.php?redirect=user/products.php" class="add-to-cart-btn" style="text-decoration: none;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                            <polyline points="10 17 15 12 10 7"/>
                                            <line x1="15" y1="12" x2="3" y2="12"/>
                                        </svg>
                                        Login to Add
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

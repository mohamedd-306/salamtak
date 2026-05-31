<?php
require_once 'config.php';

// Handle language change
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    redirect('products_public.php');
}

// NO LOGIN REQUIRED - Public access
$isUserLoggedIn = isLoggedIn() && !isAdmin();
$user_id = $isUserLoggedIn ? $_SESSION['user_id'] : null;

// Get all products
$products = queryFirestoreCollection('products');

// Get cart count (only if logged in)
$cart_count = 0;
if ($isUserLoggedIn && $user_id) {
    $cart = getFirestoreDocument('carts', $user_id);
    if ($cart && isset($cart['items'])) {
        foreach ($cart['items'] as $qty) {
            $cart_count += $qty;
        }
    }
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('app_name') ?> - <?= t('products') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8f9fa;
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

        /* Hero Section */
        .products-hero {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            padding: 140px 20px 80px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .products-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .products-hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .products-hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
            text-shadow: 0 2px 20px rgba(0,0,0,0.2);
        }
        
        .products-hero p {
            font-size: 20px;
            opacity: 0.95;
            margin-bottom: 32px;
        }
        
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 20px 32px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 150px;
        }
        
        .stat-item:nth-child(2) {
            background: #d3d3d3 !important;
            backdrop-filter: none !important;
            border: 1px solid #a9a9a9 !important;
        }
        
        .stat-item:nth-child(2) .stat-number,
        .stat-item:nth-child(2) .stat-label {
            color: #2d3748 !important;
            opacity: 1 !important;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 800;
            display: block;
            line-height: 1;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
            display: block;
            line-height: 1;
        }

        /* Cart Floating Button */
        .cart-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(15, 29, 63, 0.4);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            text-decoration: none;
        }
        
        .cart-float:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 32px rgba(15, 29, 63, 0.6);
        }
        
        .cart-float .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            border: 3px solid white;
        }

        /* Container */
        .products-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 20px 100px;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .filter-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .products-count {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .filter-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-tag {
            padding: 8px 16px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-tag:hover, .filter-tag.active {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border-color: transparent;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(15, 29, 63, 0.15);
        }
        
        .product-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 240px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 16px;
            transition: transform 0.6s;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.1);
        }
        
        .product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-category {
            font-size: 11px;
            font-weight: 700;
            color: #0f1d3f;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .product-description {
            font-size: 13px;
            color: #718096;
            line-height: 1.5;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 2px solid #f7fafc;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 14px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            background: #f7fafc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #4a5568;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .quantity-btn:hover {
            background: #e2e8f0;
        }
        
        .quantity-input {
            width: 45px;
            height: 32px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #2d3748;
        }
        
        .add-to-cart-btn {
            flex: 1;
            padding: 12px 20px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 29, 63, 0.4);
        }
        
        .login-btn {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            margin-top: 16px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 29, 63, 0.4);
        }

        /* Guest Notice */
        .guest-notice {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            padding: 20px 24px;
            border-radius: 20px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 4px 20px rgba(253, 203, 110, 0.3);
        }
        
        .guest-notice-icon {
            font-size: 32px;
        }
        
        .guest-notice-text {
            flex: 1;
        }
        
        .guest-notice-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .guest-notice-desc {
            font-size: 14px;
            color: #4a5568;
        }
        
        .guest-notice-btn {
            padding: 10px 24px;
            background: white;
            color: #2d3748;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .guest-notice-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Success Toast */
        .success-toast {
            position: fixed;
            top: 100px;
            right: 30px;
            background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 184, 148, 0.4);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1000;
            animation: slideIn 0.4s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .products-hero h1 {
                font-size: 32px;
            }
            
            .products-hero p {
                font-size: 16px;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .cart-float {
                bottom: 20px;
                right: 20px;
                width: 56px;
                height: 56px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/public_header.php'; ?>

    <!-- Hero Section -->
    <div class="products-hero">
        <div class="products-hero-content">
            <h1><?= t('safety_products_store') ?></h1>
            <p><?= t('professional_safety_equipment') ?></p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= count($products) ?>+</span>
                    <span class="stat-label"><?= t('products') ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label"><?= t('support_24_7') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Cart Button (for logged-in users) -->
    <?php if ($isUserLoggedIn && $cart_count > 0): ?>
        <a href="user/cart.php" class="cart-float">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <span class="cart-badge"><?= $cart_count ?></span>
        </a>
    <?php endif; ?>

    <!-- Success Toast -->
    <?php if (isset($_GET['added'])): ?>
        <div class="success-toast">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <strong>Success!</strong> Product added to cart
            </div>
        </div>
    <?php endif; ?>

    <!-- Products Container -->
    <div class="products-container">
        <!-- Guest Notice -->
        <?php if (!$isUserLoggedIn): ?>
            <div class="guest-notice">
                <span class="guest-notice-icon">👋</span>
                <div class="guest-notice-text">
                    <div class="guest-notice-title"><?= t('welcome_guest') ?></div>
                    <div class="guest-notice-desc"><?= t('browse_products_freely') ?></div>
                </div>
                <a href="login.php" class="guest-notice-btn"><?= t('login_now') ?></a>
            </div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-left">
                <div class="products-count"><?= count($products) ?> <?= t('products_available') ?></div>
            </div>
            <div class="filter-tags">
                <div class="filter-tag active"><?= t('all_products') ?></div>
                <div class="filter-tag"><?= t('safety_wear') ?></div>
                <div class="filter-tag"><?= t('head_protection') ?></div>
                <div class="filter-tag"><?= t('footwear') ?></div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <?php 
                $productName = strtolower($product['name']);
                $imageUrl = '';
                $description = '';
                $category = t('safety_equipment');
                
                if (strpos($productName, 'vest') !== false) {
                    $imageUrl = 'assets/products/vest.jpeg';
                    $description = t('vest_description');
                    $category = t('safety_wear');
                } elseif (strpos($productName, 'ear') !== false || strpos($productName, 'muff') !== false) {
                    $imageUrl = 'assets/products/earmuffs.jpeg';
                    $description = t('earmuffs_description');
                    $category = t('hearing_protection');
                } elseif (strpos($productName, 'jacket') !== false) {
                    $imageUrl = 'assets/products/jacket.jpeg';
                    $description = t('jacket_description');
                    $category = t('safety_wear');
                } elseif (strpos($productName, 'hard hat') !== false) {
                    $imageUrl = 'assets/products/hardhat.jpeg';
                    $description = t('hardhat_description');
                    $category = t('head_protection');
                } elseif (strpos($productName, 'helmet') !== false) {
                    $imageUrl = 'assets/products/helmet.jpeg';
                    $description = t('helmet_description');
                    $category = t('head_protection');
                } elseif (strpos($productName, 'boots') !== false) {
                    $imageUrl = 'assets/products/boots.jpeg';
                    $description = t('boots_description');
                    $category = t('footwear');
                } else {
                    $imageUrl = 'assets/products/placeholder.svg';
                    $description = $product['description'] ?? t('default_product_description');
                }
                ?>
                <div class="product-card">
                    <a href="user/product_details.php?id=<?= htmlspecialchars($product['id']) ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-image-wrapper">
                            <img src="<?= htmlspecialchars($imageUrl) ?>?v=<?= time() ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/400x400/0f1d3f/ffffff?text=<?= urlencode($product['name']) ?>'">>
                        </div>
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars($category) ?></div>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-description"><?= htmlspecialchars($description) ?></p>
                            
                            <div class="product-footer">
                                <div class="product-price">EGP <?= number_format($product['price'], 2) ?></div>
                            </div>
                        </div>
                    </a>
                    <div style="padding: 0 24px 24px;">
                        <?php if ($isUserLoggedIn): ?>
                            <form method="POST" action="user/products.php">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                <div class="product-actions">
                                    <div class="quantity-selector">
                                        <button type="button" class="quantity-btn" onclick="decreaseQty(this)">−</button>
                                        <input type="number" name="quantity" value="1" min="1" max="99" class="quantity-input" readonly>
                                        <button type="button" class="quantity-btn" onclick="increaseQty(this)">+</button>
                                    </div>
                                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="9" cy="21" r="1"/>
                                            <circle cx="20" cy="21" r="1"/>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                        </svg>
                                        <?= t('add_to_cart') ?>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <a href="login.php?redirect=products_public.php" class="login-btn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                    <polyline points="10 17 15 12 10 7"/>
                                    <line x1="15" y1="12" x2="3" y2="12"/>
                                </svg>
                                <?= t('login_to_purchase') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function increaseQty(btn) {
            const input = btn.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            if (currentValue < 99) {
                input.value = currentValue + 1;
            }
        }
        
        function decreaseQty(btn) {
            const input = btn.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }
        
        // Auto-hide success toast
        setTimeout(() => {
            const toast = document.querySelector('.success-toast');
            if (toast) {
                toast.style.animation = 'slideIn 0.4s ease-out reverse';
                setTimeout(() => toast.remove(), 400);
            }
        }, 3000);
        
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterTags = document.querySelectorAll('.filter-tag');
            const productCards = document.querySelectorAll('.product-card');
            const productsCount = document.querySelector('.products-count');
            
            filterTags.forEach(tag => {
                tag.addEventListener('click', function() {
                    // Remove active class from all tags
                    filterTags.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tag
                    this.classList.add('active');
                    
                    const filterText = this.textContent.trim();
                    let visibleCount = 0;
                    
                    productCards.forEach(card => {
                        const category = card.querySelector('.product-category').textContent.trim();
                        
                        // Check if filter matches "All Products" in any language
                        const isAllProducts = this.classList.contains('active') && filterTags[0] === this;
                        
                        if (isAllProducts) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else if (category === filterText) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });
                    
                    // Update products count with proper translation
                    const productsLabel = '<?= t('products_available') ?>';
                    productsCount.textContent = visibleCount + ' ' + productsLabel;
                });
            });
        });
    </script>
    
    <?php include 'includes/public_footer.php'; ?>
</body>
</html>

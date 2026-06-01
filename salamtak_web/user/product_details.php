<?php
require_once '../config.php';

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$isUserLoggedIn = $isLoggedIn && !isAdmin();
$user_id = $isUserLoggedIn ? $_SESSION['user_id'] : null;

// Get product ID
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    redirect('products.php');
}

// Get product details
$product = getFirestoreDocument('products', $product_id);
if (!$product) {
    redirect('products.php');
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$isUserLoggedIn) {
        redirect('../login.php?message=login_required');
    }
    
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $review_data = [
            'productId' => $product_id,
            'userId' => $user_id,
            'userName' => $_SESSION['name'] ?? 'Anonymous',
            'rating' => $rating,
            'comment' => $comment,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        
        addFirestoreDocument('reviews', $review_data);
        header("Location: product_details.php?id=$product_id&review_added=1");
        exit();
    }
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$isUserLoggedIn) {
        redirect('../login.php?message=login_required');
    }
    
    $quantity = (int)$_POST['quantity'];
    
    // Get existing cart or create new
    $cart = getFirestoreDocument('carts', $user_id);
    
    if (!$cart) {
        $cart_data = [
            'userId' => $user_id,
            'items' => [$product_id => $quantity],
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        createFirestoreDocumentWithId('carts', $user_id, $cart_data);
    } else {
        $items = $cart['items'] ?? [];
        $items[$product_id] = ($items[$product_id] ?? 0) + $quantity;
        updateFirestoreDocument('carts', $user_id, [
            'items' => $items,
            'updatedAt' => date('Y-m-d H:i:s')
        ]);
    }
    
    header("Location: products.php?added=1");
    exit();
}

// Get all reviews for this product
$all_reviews = queryFirestoreCollection('reviews');
$product_reviews = array_filter($all_reviews, function($review) use ($product_id) {
    return ($review['productId'] ?? '') === $product_id;
});

// Sort reviews by date (newest first)
usort($product_reviews, function($a, $b) {
    return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
});

// Calculate average rating
$total_rating = 0;
$review_count = count($product_reviews);
foreach ($product_reviews as $review) {
    $total_rating += $review['rating'] ?? 0;
}
$average_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

// Product details - use helper function for image
$imageUrl = getProductImageUrl($product);
$description = $product['description'] ?? 'Professional safety equipment';
$category = $product['category'] ?? 'Safety Equipment';
$fullDescription = $product['description'] ?? 'Professional safety equipment for workplace protection. High-quality construction and materials ensure maximum protection and durability.';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - <?= t('app_name') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            padding-top: 110px;
            overflow-x: hidden;
        }

        /* Navbar Styles - Same as products page */
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

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #0f1d3f;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 24px;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: #1a2d5a;
            transform: translateX(-4px);
        }

        .product-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .product-image-section {
            position: relative;
        }

        .product-main-image {
            width: 100%;
            height: 500px;
            object-fit: contain;
            border-radius: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 40px;
            display: block;
        }
        
        /* Base64 image support */
        .product-main-image[src^="data:image"] {
            object-fit: contain;
            background: #ffffff;
        }

        .product-badge-detail {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .product-info-section {
            display: flex;
            flex-direction: column;
        }

        .product-category-detail {
            font-size: 14px;
            font-weight: 700;
            color: #0f1d3f;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        .product-name-detail {
            font-size: 36px;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .stars {
            display: flex;
            gap: 4px;
        }

        .star {
            color: #fbbf24;
            font-size: 20px;
        }

        .star.empty {
            color: #e5e7eb;
        }

        .rating-text {
            font-size: 16px;
            color: #6b7280;
            font-weight: 600;
        }

        .product-price-detail {
            font-size: 42px;
            font-weight: 800;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 24px;
        }

        .product-description-detail {
            font-size: 16px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 2px solid #f7fafc;
        }

        .product-actions-detail {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .quantity-selector-detail {
            display: flex;
            align-items: center;
            background: #f7fafc;
            border-radius: 12px;
            overflow: hidden;
        }

        .quantity-btn-detail {
            width: 48px;
            height: 48px;
            border: none;
            background: transparent;
            color: #4a5568;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quantity-btn-detail:hover {
            background: #e2e8f0;
        }

        .quantity-input-detail {
            width: 70px;
            height: 48px;
            border: none;
            background: transparent;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }

        .add-to-cart-btn-detail {
            flex: 1;
            padding: 16px 32px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .add-to-cart-btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 29, 63, 0.4);
        }

        /* Reviews Section */
        .reviews-section {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #f7fafc;
        }

        .reviews-title {
            font-size: 28px;
            font-weight: 800;
            color: #2d3748;
        }

        .write-review-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .write-review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 29, 63, 0.4);
        }

        .review-form {
            background: #f7fafc;
            padding: 32px;
            border-radius: 16px;
            margin-bottom: 32px;
            display: none;
        }

        .review-form.active {
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .star-rating {
            display: flex;
            gap: 8px;
            font-size: 32px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            color: #e5e7eb;
            transition: all 0.3s;
        }

        .star-rating label:hover,
        .star-rating label.active {
            color: #fbbf24;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #0f1d3f;
        }

        .form-actions {
            display: flex;
            gap: 12px;
        }

        .btn-submit {
            padding: 12px 24px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-cancel {
            padding: 12px 24px;
            background: #e5e7eb;
            color: #4a5568;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .review-card {
            padding: 24px;
            background: #f7fafc;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .reviewer-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .reviewer-details h4 {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .review-date {
            font-size: 13px;
            color: #9ca3af;
        }

        .review-stars {
            display: flex;
            gap: 4px;
        }

        .review-comment {
            font-size: 15px;
            line-height: 1.7;
            color: #4a5568;
        }

        .no-reviews {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .no-reviews svg {
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        @media (max-width: 968px) {
            body {
                padding-top: 90px;
            }

            .landing-nav {
                padding: 12px 20px;
            }

            .landing-brand-logo {
                width: 60px;
                height: 60px;
            }

            .landing-nav-links {
                gap: 20px;
            }

            .landing-nav-link {
                font-size: 14px;
            }

            .product-detail-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .product-main-image {
                height: 400px;
            }

            .product-name-detail {
                font-size: 28px;
            }

            .product-price-detail {
                font-size: 32px;
            }
        }

        @media (max-width: 768px) {
            .landing-nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <a href="products.php" class="back-link">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            <?= t('back_to_products') ?>
        </a>

        <?php if (isset($_GET['added'])): ?>
            <div class="success-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?= t('product_added_success') ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['review_added'])): ?>
            <div class="success-message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?= t('thank_you_review') ?>
            </div>
        <?php endif; ?>

        <div class="product-detail-grid">
            <div class="product-image-section">
                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="product-main-image"
                     loading="lazy"
                     onerror="this.src='../assets/products/placeholder.svg'">
            </div>

            <div class="product-info-section">
                <div class="product-category-detail"><?= htmlspecialchars($category) ?></div>
                <h1 class="product-name-detail"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= floor($average_rating) ? '' : 'empty' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text"><?= $average_rating ?> (<?= $review_count ?> <?= t('reviews_label') ?>)</span>
                </div>

                <div class="product-price-detail">EGP <?= number_format($product['price'], 2) ?></div>

                <p class="product-description-detail"><?= htmlspecialchars($fullDescription) ?></p>

                <form method="POST">
                    <div class="product-actions-detail">
                        <div class="quantity-selector-detail">
                            <button type="button" class="quantity-btn-detail" onclick="decreaseQty()">−</button>
                            <input type="number" name="quantity" id="quantityInput" value="1" min="1" max="99" class="quantity-input-detail" readonly>
                            <button type="button" class="quantity-btn-detail" onclick="increaseQty()">+</button>
                        </div>
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn-detail">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/>
                                <circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            <?= t('add_to_cart') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="reviews-header">
                <h2 class="reviews-title"><?= t('customer_reviews') ?> (<?= $review_count ?>)</h2>
                <?php if ($isUserLoggedIn): ?>
                    <button class="write-review-btn" onclick="toggleReviewForm()"><?= t('write_review') ?></button>
                <?php else: ?>
                    <a href="../login.php?message=login_required" class="write-review-btn" style="text-decoration: none;"><?= t('login_to_review') ?></a>
                <?php endif; ?>
            </div>

            <?php if ($isUserLoggedIn): ?>
                <form method="POST" class="review-form" id="reviewForm">
                    <div class="form-group">
                        <label><?= t('your_rating') ?></label>
                        <div class="star-rating" id="starRating">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5" data-rating="5">★</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4" data-rating="4">★</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3" data-rating="3">★</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2" data-rating="2">★</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1" data-rating="1">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= t('your_review') ?></label>
                        <textarea name="comment" placeholder="<?= t('share_experience') ?>" required></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="submit_review" class="btn-submit"><?= t('submit_review') ?></button>
                        <button type="button" class="btn-cancel" onclick="toggleReviewForm()"><?= t('cancel') ?></button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="reviews-list">
                <?php if (empty($product_reviews)): ?>
                    <div class="no-reviews">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <h3><?= t('no_reviews_yet') ?></h3>
                        <p><?= t('be_first_review') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($product_reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?= strtoupper(substr($review['userName'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="reviewer-details">
                                        <h4><?= htmlspecialchars($review['userName'] ?? 'Anonymous') ?></h4>
                                        <span class="review-date"><?= date('F j, Y', strtotime($review['createdAt'] ?? 'now')) ?></span>
                                    </div>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= ($review['rating'] ?? 0) ? '' : 'empty' ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($review['comment'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function increaseQty() {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value);
            if (currentValue < 99) {
                input.value = currentValue + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        function toggleReviewForm() {
            const form = document.getElementById('reviewForm');
            form.classList.toggle('active');
        }

        // Star rating interaction
        const starRating = document.getElementById('starRating');
        if (starRating) {
            const labels = starRating.querySelectorAll('label');
            
            labels.forEach(label => {
                label.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    labels.forEach(l => {
                        if (l.dataset.rating <= rating) {
                            l.classList.add('active');
                        } else {
                            l.classList.remove('active');
                        }
                    });
                });
            });
        }

        // Auto-hide success messages
        setTimeout(() => {
            const messages = document.querySelectorAll('.success-message');
            messages.forEach(msg => {
                msg.style.transition = 'opacity 0.5s';
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            });
        }, 3000);
    </script>

    <?php include '../includes/public_footer.php'; ?>
    
    <?php include 'includes/nav.php'; ?>
</body>
</html>

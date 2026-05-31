<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: inventory.php");
    exit();
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

$message = '';

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $product_id = $_POST['product_id'];
    $new_stock = (int)$_POST['stock'];
    
    $updated = updateFirestoreDocument('products', $product_id, ['stock' => $new_stock, 'updatedAt' => date('Y-m-d H:i:s')]);
    
    if ($updated) {
        $message = t('stock_updated_successfully');
    } else {
        $message = 'Failed to update stock!';
    }
}

// Handle price update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_price'])) {
    $product_id = $_POST['product_id'];
    $new_price = floatval($_POST['price']);
    
    $updated = updateFirestoreDocument('products', $product_id, ['price' => $new_price, 'updatedAt' => date('Y-m-d H:i:s')]);
    
    if ($updated) {
        $message = t('price_updated_successfully');
    } else {
        $message = 'Failed to update price!';
    }
}

// Handle delete product
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    $url = FIRESTORE_URL . "/products/{$product_id}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    
    header("Location: inventory.php?deleted=1");
    exit();
}

// Get all products
$products = queryFirestoreCollection('products');

// Get all reviews
$all_reviews = queryFirestoreCollection('reviews');

// Group reviews by product ID
$reviews_by_product = [];
foreach ($all_reviews as $review) {
    $productId = $review['productId'] ?? '';
    if (!isset($reviews_by_product[$productId])) {
        $reviews_by_product[$productId] = [];
    }
    $reviews_by_product[$productId][] = $review;
}

// Calculate average ratings
$product_ratings = [];
foreach ($reviews_by_product as $productId => $reviews) {
    $total = 0;
    $count = count($reviews);
    foreach ($reviews as $review) {
        $total += $review['rating'] ?? 0;
    }
    $product_ratings[$productId] = [
        'average' => $count > 0 ? round($total / $count, 1) : 0,
        'count' => $count
    ];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('app_name') ?> - <?= t('product_inventory') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 50%, #0f1d3f 100%);
            padding: 110px 40px 40px;
            min-height: 100vh;
        }
        
        /* Back Arrow Button */
        .back-arrow {
            position: fixed;
            top: 90px;
            left: 40px;
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 100;
            text-decoration: none;
        }
        
        .back-arrow:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-4px);
        }
        
        .back-arrow svg {
            color: #FBBF24;
            width: 24px;
            height: 24px;
        }
        
        /* Container */
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 42px;
            font-weight: 900;
            color: white;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 32px 0;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
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
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            overflow-x: auto;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 3px solid #FBBF24;
        }
        
        .section-title {
            font-size: 28px;
            font-weight: 800;
            color: #0f1d3f;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-badge {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 700;
        }
        
        /* Products Table */
        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 16px;
            table-layout: fixed;
        }
        
        .products-table thead th {
            background: transparent;
            padding: 12px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .products-table thead th:nth-child(1) { width: 100px; } /* Image */
        .products-table thead th:nth-child(2) { width: 180px; } /* Product Name */
        .products-table thead th:nth-child(3) { width: 220px; } /* Price */
        .products-table thead th:nth-child(4) { width: 220px; } /* Stock */
        .products-table thead th:nth-child(5) { width: 130px; } /* Category */
        .products-table thead th:nth-child(6) { width: 200px; } /* Reviews */
        .products-table thead th:nth-child(7) { width: 130px; } /* Actions */
        
        .products-table tbody tr {
            background: #f7fafc;
            transition: all 0.3s;
        }
        
        .products-table tbody tr:hover {
            background: white;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .products-table tbody td {
            padding: 24px 20px;
            border-top: 2px solid white;
            border-bottom: 2px solid white;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
        }
        
        .products-table tbody td:first-child {
            border-left: 2px solid white;
            border-radius: 12px 0 0 12px;
            padding-left: 24px;
        }
        
        .products-table tbody td:last-child {
            border-right: 2px solid white;
            border-radius: 0 12px 12px 0;
            padding-right: 24px;
        }
        
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 12px;
            background: white;
            padding: 10px;
            border: 2px solid #e5e7eb;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f1d3f;
            line-height: 1.4;
        }
        
        .stock-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: nowrap;
        }
        
        .stock-input {
            width: 80px;
            padding: 10px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .stock-input:focus {
            outline: none;
            border-color: #0f1d3f;
            box-shadow: 0 0 0 3px rgba(15, 29, 63, 0.1);
        }
        
        .stock-btn {
            padding: 10px 16px;
            background: #0f1d3f;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            min-width: 70px;
        }
        
        .stock-btn:hover {
            background: #1a2d5a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 29, 63, 0.3);
        }
        
        .stock-badge {
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 10px;
            display: inline-block;
        }
        
        .stock-low { background: #fee2e2; color: #dc2626; }
        .stock-medium { background: #fef3c7; color: #d97706; }
        .stock-high { background: #d1fae5; color: #059669; }
        
        .delete-btn {
            padding: 10px 16px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            min-width: 70px;
        }
        
        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .rating-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        
        .view-reviews-btn {
            padding: 8px 14px;
            background: #0f1d3f;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            margin-top: 10px;
            white-space: nowrap;
        }
        
        .view-reviews-btn:hover {
            background: #1a2d5a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 29, 63, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6b7280;
        }
        
        .empty-state svg {
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 24px;
            color: #0f1d3f;
            margin-bottom: 12px;
        }
        
        @media (max-width: 768px) {
            body { padding: 90px 20px 20px; }
            .back-arrow {
                top: 80px;
                left: 20px;
                width: 48px;
                height: 48px;
            }
            .section {
                padding: 24px;
                overflow-x: auto;
            }
            .page-title {
                font-size: 32px;
            }
            .products-table {
                min-width: 1000px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <!-- Back Arrow -->
    <a href="products.php" class="back-arrow" title="<?= t('back_to_products') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
    </a>
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title"><?= t('product_inventory') ?></h1>
            <p class="page-subtitle"><?= t('manage_stock_prices_reviews') ?></p>
            <div class="header-actions">
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
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="message">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <?= t('product_deleted_successfully') ?>
            </div>
        <?php endif; ?>
        
        <!-- Products Section -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    <?= t('all_products') ?>
                </h2>
                <span class="section-badge"><?= count($products) ?> <?= t('items') ?></span>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                    <h3><?= t('no_products_yet') ?></h3>
                    <p><?= t('add_first_product') ?></p>
                    <a href="add_product.php" class="btn btn-success" style="margin-top: 24px;"><?= t('add_product') ?></a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; margin: 0 -40px; padding: 0 40px; -webkit-overflow-scrolling: touch;">
                    <table class="products-table" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th><?= t('image') ?></th>
                            <th><?= t('product_name') ?></th>
                            <th><?= t('price') ?></th>
                            <th><?= t('stock') ?></th>
                            <th><?= t('category') ?></th>
                            <th><?= t('reviews') ?></th>
                            <th><?= t('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $stock = $product['stock'] ?? 0;
                            $stockClass = $stock < 20 ? 'stock-low' : ($stock < 50 ? 'stock-medium' : 'stock-high');
                            
                            // Use helper function to get image URL
                            $imageUrl = getProductImageUrl($product);
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Product" class="product-img" onerror="this.src='../assets/products/placeholder.svg'">
                                </td>
                                <td>
                                    <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                                </td>
                                <td>
                                    <form method="POST" class="stock-form">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                        <input type="number" name="price" value="<?= number_format($product['price'], 2, '.', '') ?>" min="0" step="0.01" class="stock-input" style="width: 120px;">
                                        <button type="submit" name="update_price" class="stock-btn"><?= t('update') ?></button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" class="stock-form">
                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                                        <input type="number" name="stock" value="<?= $stock ?>" min="0" class="stock-input">
                                        <button type="submit" name="update_stock" class="stock-btn"><?= t('update') ?></button>
                                    </form>
                                    <?php if ($stock < 20): ?>
                                        <span class="stock-badge stock-low"><?= t('low_stock') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['category'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $productId = $product['id'];
                                    $rating = $product_ratings[$productId] ?? ['average' => 0, 'count' => 0];
                                    ?>
                                    <div style="display: flex; flex-direction: column; gap: 8px;">
                                        <?php if ($rating['count'] > 0): ?>
                                            <span class="rating-badge">
                                                ⭐ <?= $rating['average'] ?> (<?= $rating['count'] ?>)
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #9ca3af; font-size: 13px;"><?= t('no_reviews') ?></span>
                                        <?php endif; ?>
                                        <button onclick="alert('Reviews feature - Product: <?= htmlspecialchars($product['name']) ?>')" class="view-reviews-btn">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                            </svg>
                                            <?= t('view_reviews') ?>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <button onclick="if(confirm('<?= t('delete') ?>?')) window.location.href='?delete=<?= htmlspecialchars($product['id']) ?>'" class="delete-btn">
                                        <?= t('delete') ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

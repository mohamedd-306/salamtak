<?php
require_once '../config.php';

/**
 * Compress image data for base64 storage
 * Similar to Flutter app's compression logic
 * Requires PHP GD extension to be enabled
 */
function compressImage($image_data, $mime_type) {
    // Create image resource from string
    $image = imagecreatefromstring($image_data);
    
    if ($image === false) {
        error_log('Failed to create image resource, returning original data');
        return $image_data;
    }
    
    // Get original dimensions
    $width = imagesx($image);
    $height = imagesy($image);
    
    error_log("Original image dimensions: {$width}x{$height}");
    
    // Resize if image is too large (max 1200px)
    $max_dimension = 1200;
    if ($width > $max_dimension || $height > $max_dimension) {
        $ratio = min($max_dimension / $width, $max_dimension / $height);
        $new_width = (int)($width * $ratio);
        $new_height = (int)($height * $ratio);
        
        error_log("Resizing image to: {$new_width}x{$new_height}");
        
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        if (strpos($mime_type, 'png') !== false) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    // Compress and output to buffer
    ob_start();
    
    if (strpos($mime_type, 'png') !== false) {
        // PNG compression (0-9, where 9 is max compression)
        imagepng($image, null, 6);
    } else {
        // JPEG compression (0-100, where 100 is best quality)
        imagejpeg($image, null, 85);
    }
    
    $compressed_data = ob_get_clean();
    imagedestroy($image);
    
    $original_size = strlen($image_data);
    $compressed_size = strlen($compressed_data);
    $ratio = round((1 - $compressed_size / $original_size) * 100, 1);
    
    error_log("Image compression: {$original_size} bytes -> {$compressed_size} bytes ({$ratio}% reduction)");
    
    return $compressed_data;
}

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
    header("Location: add_product.php");
    exit();
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);
    
    if (empty($name) || empty($price)) {
        $error = t('name_price_required');
    } else {
        $imageUrl = '';
        
        // Handle image upload - Convert to base64 for cross-platform compatibility
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];
            
            // Get file extension
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExt, $allowed)) {
                if ($fileSize < 5000000) { // 5MB max
                    // Read the uploaded file
                    $image_data = file_get_contents($fileTmpName);
                    
                    if ($image_data !== false) {
                        // Compress image (requires GD library)
                        $compressed_image = compressImage($image_data, $fileType);
                        
                        // Convert to base64
                        $base64_image = base64_encode($compressed_image);
                        
                        // Determine MIME type
                        $mime_type = $fileType;
                        if (empty($mime_type) || $mime_type === 'application/octet-stream') {
                            // Fallback to JPEG if type is unknown
                            $mime_type = 'image/jpeg';
                        }
                        
                        // Create data URI
                        $imageUrl = 'data:' . $mime_type . ';base64,' . $base64_image;
                        
                        error_log('Product image converted to base64: ' . strlen($imageUrl) . ' characters');
                    } else {
                        error_log('Failed to read uploaded product image file');
                        $error = t('firebase_upload_failed');
                    }
                } else {
                    $error = t('file_size_exceeded_error');
                }
            } else {
                $error = t('invalid_file_type_error');
            }
        }
        
        if (empty($error)) {
            $product_data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'category' => $category,
                'image' => $imageUrl,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s')
            ];
            
            $product_id = addFirestoreDocument('products', $product_data);
            
            if ($product_id) {
                $message = t('product_added_successfully');
                // Redirect to prevent form resubmission
                header("Location: add_product.php?success=1");
                exit();
            } else {
                $error = t('failed_to_add_product');
            }
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $message = t('product_added_successfully');
}

// Get all products
$products = queryFirestoreCollection('products');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('app_name') ?> - <?= t('add_new_product') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Admin Navbar Styles */
        .admin-page {
            margin: 0;
            padding: 0;
        }
        
        .simple-admin-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: 70px;
        }
        
        .simple-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 32px;
            height: 100%;
        }
        
        .nav-brand-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .brand-logo {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4);
        }
        
        .brand-logo svg {
            width: 28px;
            height: 28px;
            color: #0f1d3f;
        }
        
        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .brand-name {
            font-size: 20px;
            font-weight: 900;
            color: white;
            margin: 0;
            letter-spacing: -0.5px;
            line-height: 1;
        }
        
        .brand-badge {
            font-size: 10px;
            font-weight: 700;
            color: #FBBF24;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 1;
        }
        
        .nav-actions-section {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        
        .language-selector {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            transition: var(--transition);
        }
        
        .language-selector:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .lang-icon {
            color: #FBBF24;
            flex-shrink: 0;
        }
        
        .lang-option {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .lang-option:hover {
            color: white;
        }
        
        .lang-option.active {
            color: #FBBF24;
            font-weight: 700;
        }
        
        .lang-divider {
            color: rgba(255, 255, 255, 0.3);
            font-weight: 300;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 20px 8px 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            transition: var(--transition);
        }
        
        .user-section:hover {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .user-avatar-circle {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 16px;
            color: #0f1d3f;
            box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .user-display-name {
            font-size: 13px;
            font-weight: 700;
            color: white;
            line-height: 1;
        }
        
        .user-badge {
            font-size: 10px;
            font-weight: 600;
            color: #FBBF24;
            line-height: 1;
        }
        
        .logout-button {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }
        
        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }
        
        .logout-button:active {
            transform: translateY(0);
        }
        
        .logout-button svg {
            flex-shrink: 0;
        }
        
        body { 
            padding: 110px 40px 40px 40px; 
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 50%, #0f1d3f 100%);
            min-height: 100vh;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            position: relative;
        }
        
        /* Back Arrow Button */
        .back-arrow {
            position: fixed;
            top: 90px;
            left: 40px;
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
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
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 36px;
            font-weight: 900;
            color: white;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }
        
        .page-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
        }
        
        .form-container { 
            background: rgba(255, 255, 255, 0.98);
            padding: 40px; 
            border-radius: 20px; 
            margin-bottom: 30px; 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .form-container h2 {
            font-size: 24px;
            font-weight: 800;
            color: #0f1d3f;
            margin: 0 0 30px 0;
            padding-bottom: 15px;
            border-bottom: 3px solid #FBBF24;
        }
        
        .form-group { 
            margin-bottom: 24px; 
        }
        
        .form-group label { 
            display: block; 
            font-weight: 700; 
            margin-bottom: 10px; 
            color: #0f1d3f;
            font-size: 14px;
        }
        
        .form-group input, 
        .form-group textarea, 
        .form-group select { 
            width: 100%; 
            padding: 14px 16px; 
            border: 2px solid #e5e7eb; 
            border-radius: 12px; 
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FBBF24;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
        }
        
        .form-group textarea { 
            min-height: 120px; 
            resize: vertical; 
            font-family: inherit;
        }
        
        .form-group small {
            color: #6b7280;
            display: block;
            margin-top: 8px;
            font-size: 13px;
        }
        
        /* Image Preview Styles */
        .image-preview {
            border: 2px dashed #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #f9fafb;
            transition: all 0.3s ease;
        }
        
        .image-preview:hover {
            border-color: #FBBF24;
            background: #fffbeb;
        }
        
        #image-preview {
            max-width: 300px;
            max-height: 300px;
            width: auto;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            object-fit: contain;
        }
        
        .preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 40px 20px;
            color: #9ca3af;
        }
        
        .preview-placeholder svg {
            color: #d1d5db;
        }
        
        .preview-placeholder p {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .image-filename {
            margin-top: 12px;
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
            word-break: break-all;
        }
        
        .btn { 
            padding: 14px 32px; 
            border-radius: 12px; 
            border: none; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-success { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
            width: 100%;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }
        
        .btn-success:active {
            transform: translateY(0);
        }
        
        .message { 
            padding: 16px 20px; 
            border-radius: 12px; 
            margin-bottom: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .message.success { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        }
        
        .message.error { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }
        
        .products-list { 
            background: rgba(255, 255, 255, 0.98);
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .products-list h2 {
            font-size: 24px;
            font-weight: 800;
            color: #0f1d3f;
            margin: 0 0 30px 0;
            padding-bottom: 15px;
            border-bottom: 3px solid #FBBF24;
        }
        
        .product-item { 
            padding: 20px; 
            border-bottom: 1px solid #e5e7eb; 
            display: flex; 
            align-items: center;
            gap: 16px;
            transition: all 0.3s ease;
        }
        
        .product-item:hover {
            background: rgba(251, 191, 36, 0.05);
            border-radius: 12px;
        }
        
        .product-item:last-child { 
            border-bottom: none; 
        }
        
        .product-item img { 
            width: 80px; 
            height: 80px; 
            object-fit: contain; 
            border-radius: 12px;
            background: #f9fafb;
            padding: 8px;
            border: 2px solid #e5e7eb;
        }
        
        .product-info { 
            flex: 1; 
        }
        
        .product-info strong {
            font-size: 16px;
            font-weight: 700;
            color: #0f1d3f;
            display: block;
            margin-bottom: 6px;
        }
        
        .product-info span {
            color: #6b7280;
            font-size: 14px;
        }
        
        @media (max-width: 1024px) {
            .user-details {
                display: none;
            }
            
            .logout-button span {
                display: none;
            }
            
            .logout-button {
                padding: 12px;
                width: 44px;
                height: 44px;
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .simple-nav-container {
                padding: 12px 20px;
                gap: 16px;
            }
            
            .brand-info {
                display: none;
            }
            
            .brand-logo {
                width: 48px;
                height: 48px;
            }
            
            .brand-logo svg {
                width: 28px;
                height: 28px;
            }
            
            .language-selector {
                padding: 8px 12px;
                gap: 8px;
            }
            
            .lang-icon {
                display: none;
            }
            
            .lang-option {
                font-size: 12px;
            }
            
            .nav-actions-section {
                gap: 12px;
            }
            
            body {
                padding: 90px 20px 20px 20px;
            }
            
            .back-arrow {
                top: 80px;
                left: 20px;
                width: 44px;
                height: 44px;
            }
            
            .form-container,
            .products-list {
                padding: 24px;
            }
            
            .page-title {
                font-size: 28px;
            }
            
            .product-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-item img {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body class="admin-page">
    <?php include 'includes/admin_navbar.php'; ?>
    
    <!-- Back Arrow -->
    <a href="products.php" class="back-arrow" title="<?= t('back_to_products') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
    </a>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><?= t('add_new_product_title') ?></h1>
            <p class="page-subtitle"><?= t('fill_details_below') ?></p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?= t('product_details') ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><?= t('product_name_required') ?></label>
                    <input type="text" name="name" required placeholder="<?= t('product_name_placeholder') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('description') ?></label>
                    <textarea name="description" placeholder="<?= t('description_placeholder') ?>"></textarea>
                </div>
                
                <div class="form-group">
                    <label><?= t('price_egp_required') ?></label>
                    <input type="number" name="price" step="0.01" required placeholder="<?= t('price_placeholder') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('stock_quantity') ?></label>
                    <input type="number" name="stock" value="100" placeholder="<?= t('stock_placeholder') ?>">
                </div>
                
                <div class="form-group">
                    <label><?= t('category') ?></label>
                    <select name="category">
                        <option value="Head Protection"><?= t('head_protection') ?></option>
                        <option value="Body Protection"><?= t('body_protection') ?></option>
                        <option value="Foot Protection"><?= t('foot_protection') ?></option>
                        <option value="Hand Protection"><?= t('hand_protection') ?></option>
                        <option value="Eye Protection"><?= t('eye_protection') ?></option>
                        <option value="Hearing Protection"><?= t('hearing_protection') ?></option>
                        <option value="Respiratory Protection"><?= t('respiratory_protection') ?></option>
                        <option value="Fall Protection"><?= t('fall_protection') ?></option>
                        <option value="Other"><?= t('other') ?></option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><?= t('upload_product_image') ?></label>
                    <input type="file" 
                           name="product_image" 
                           id="product_image" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                           onchange="previewImage(event)">
                    <small><?= t('max_file_size_5mb') ?></small>
                </div>
                
                <div class="form-group">
                    <label><?= t('image_preview') ?></label>
                    <div id="image-preview-container" class="image-preview">
                        <img id="image-preview" src="" alt="Preview" style="display:none;">
                        <div id="preview-placeholder" class="preview-placeholder">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <p><?= t('no_image_selected') ?></p>
                        </div>
                        <p id="image-filename" class="image-filename"></p>
                    </div>
                </div>
                
                <button type="submit" name="add_product" class="btn btn-success"><?= t('add_product') ?></button>
            </form>
        </div>
        
        <div class="products-list">
            <h2><?= t('current_products') ?> (<?= count($products) ?>)</h2>
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         loading="lazy"
                         onerror="this.onerror=null; this.src='../assets/products/placeholder.svg';">
                    <div class="product-info">
                        <strong><?= htmlspecialchars($product['name']) ?></strong><br>
                        <span style="color: #666;">EGP <?= number_format($product['price'], 2) ?> | Stock: <?= $product['stock'] ?> | <?= htmlspecialchars($product['category'] ?? 'N/A') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('preview-placeholder');
            const filename = document.getElementById('image-filename');
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('<?= t('invalid_file_type') ?>');
                    event.target.value = '';
                    return;
                }
                
                // Validate file size (5MB = 5,000,000 bytes)
                if (file.size > 5000000) {
                    alert('<?= t('file_too_large') ?>');
                    event.target.value = '';
                    return;
                }
                
                // Display preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                    filename.textContent = file.name;
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'flex';
                filename.textContent = '';
            }
        }
    </script>
</body>
</html>

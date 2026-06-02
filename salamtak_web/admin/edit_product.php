<?php
require_once '../config.php';

/**
 * Compress image data for base64 storage
 * Similar to Flutter app's compression logic
 */
function compressImage($image_data, $mime_type) {
    $image = imagecreatefromstring($image_data);
    
    if ($image === false) {
        error_log('Failed to create image resource, returning original data');
        return $image_data;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    error_log("Original image dimensions: {$width}x{$height}");
    
    $max_dimension = 1200;
    if ($width > $max_dimension || $height > $max_dimension) {
        $ratio = min($max_dimension / $width, $max_dimension / $height);
        $new_width = (int)($width * $ratio);
        $new_height = (int)($height * $ratio);
        
        error_log("Resizing image to: {$new_width}x{$new_height}");
        
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        if (strpos($mime_type, 'png') !== false) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    ob_start();
    
    if (strpos($mime_type, 'png') !== false) {
        imagepng($image, null, 6);
    } else {
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
    if (isModerator()) {
        redirect('dashboard.php');
    }
    redirect('../login.php');
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar'])) {
    setLanguage($_GET['lang']);
    header("Location: edit_product.php?id=" . urlencode($_GET['id']));
    exit();
}

$lang = getCurrentLanguage();
$isRTL = $lang === 'ar';

$message = '';
$error = '';

// Get product ID from URL
$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    redirect('inventory.php');
}

// Fetch product details
$product = getFirestoreDocument('products', $product_id);

if (!$product) {
    $_SESSION['error'] = t('product_not_found');
    redirect('inventory.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category = trim($_POST['category']);
    
    if (empty($name) || empty($price)) {
        $error = t('name_price_required');
    } else {
        $update_data = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'category' => $category,
            'updatedAt' => date('Y-m-d H:i:s')
        ];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = t('invalid_image_format');
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error = t('image_too_large');
            } else {
                $image_data = file_get_contents($_FILES['image']['tmp_name']);
                
                try {
                    $compressed_data = compressImage($image_data, $file_type);
                    $base64_image = base64_encode($compressed_data);
                    $mime_normalized = str_replace('image/jpg', 'image/jpeg', $file_type);
                    $data_url = "data:{$mime_normalized};base64,{$base64_image}";
                    
                    $update_data['image'] = $data_url;
                    
                    error_log("Product updated with new image (size: " . strlen($data_url) . " bytes)");
                } catch (Exception $e) {
                    error_log("Image compression failed: " . $e->getMessage());
                    $error = t('image_compression_failed');
                }
            }
        }
        
        if (empty($error)) {
            $updated = updateFirestoreDocument('products', $product_id, $update_data);
            
            if ($updated) {
                $_SESSION['success'] = t('product_updated_successfully');
                redirect('inventory.php');
            } else {
                $error = t('failed_to_update_product');
            }
        }
    }
}

// Categories list
$categories = ['Medical Supplies', 'Emergency Equipment', 'Safety Gear', 'First Aid', 'Other'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $isRTL ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <title><?= t('app_name') ?> - <?= t('edit_product') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 50%, #0f1d3f 100%);
            padding: 110px 40px 40px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 42px;
            font-weight: 900;
            color: white;
            margin: 0 0 12px 0;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0f1d3f;
            box-shadow: 0 0 0 3px rgba(15, 29, 63, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .image-preview-container {
            margin-top: 16px;
        }
        
        .image-preview {
            max-width: 300px;
            max-height: 300px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 10px;
            background: white;
            object-fit: contain;
        }
        
        .current-image {
            display: block;
            margin-bottom: 16px;
        }
        
        .current-image-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }
        
        .message {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .message.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .message.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        
        .btn {
            flex: 1;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0f1d3f 0%, #1a2d5a 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 29, 63, 0.4);
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        @media (max-width: 768px) {
            body { padding: 90px 20px 20px; }
            .form-card { padding: 24px; }
            .page-title { font-size: 32px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><?= t('edit_product') ?></h1>
            <p class="page-subtitle"><?= t('update_product_details') ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="message error">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name"><?= t('product_name') ?> *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><?= t('description') ?></label>
                    <textarea id="description" name="description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price"><?= t('price') ?> (EGP) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?= number_format($product['price'], 2, '.', '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock"><?= t('stock_quantity') ?> *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?= intval($product['stock'] ?? 0) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category"><?= t('category') ?> *</label>
                    <select id="category" name="category" required>
                        <option value=""><?= t('select_category') ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($product['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image"><?= t('product_image') ?></label>
                    
                    <?php if (!empty($product['image'])): ?>
                        <span class="current-image-label"><?= t('current_image') ?>:</span>
                        <div class="current-image">
                            <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="image-preview">
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="image" name="image" accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewNewImage(event)">
                    <div id="newImagePreview" class="image-preview-container" style="display: none;">
                        <span class="current-image-label"><?= t('new_image') ?>:</span>
                        <img id="newImagePreviewImg" class="image-preview">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="inventory.php" class="btn btn-secondary"><?= t('cancel') ?></a>
                    <button type="submit" name="update_product" class="btn btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <?= t('update_product') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function previewNewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('newImagePreviewImg').src = e.target.result;
                    document.getElementById('newImagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('newImagePreview').style.display = 'none';
            }
        }
    </script>
</body>
</html>
